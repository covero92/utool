import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { PPRContextType, PPRState, OKRGroup, Month, GoalStatus, AuditLogEntry, GoalType, Goal } from '../types/ppr';
import { calculateScore, generateMockData } from '../lib/ppr-utils';

const PPRContext = createContext<PPRContextType | undefined>(undefined);

export const PPRProvider = ({ children }: { children: ReactNode }) => {
    const [okrs, setOkrs] = useState<OKRGroup[]>([]);
    const [auditLogs, setAuditLogs] = useState<AuditLogEntry[]>([]);
    const [currentYear, setCurrentYear] = useState(new Date().getFullYear());
    const [availableYears, setAvailableYears] = useState<number[]>([2026]); // Start with base year
    const [hasUnsavedChanges, setHasUnsavedChanges] = useState(false);

    // Permission Logic
    const userInfo = (typeof window !== 'undefined' ? window.USER_INFO : { capabilities: [] }) as any;

    // Admin always has access (system_config or manage_users is simplified admin check, or just check if role contains admin)
    // But rely on capabilities for granular control.
    // System Config = Super Admin. 
    // edit_ppr = Specific permission.
    const caps = (userInfo.capabilities || []) as string[];
    const isAdmin = caps.includes('system_config') || caps.includes('access_admin_panel');
    const canEdit = isAdmin || caps.includes('edit_ppr');
    const canView = isAdmin || canEdit || caps.includes('view_ppr');

    const [comparisonData, setComparisonData] = useState<Record<number, OKRGroup[]>>({});

    // Initial Load & Year Change
    useEffect(() => {
        const loadData = () => {
            // Always try to hydrate from localStorage to override/extend static data
            const stored = localStorage.getItem('PPR_DATA');
            if (stored) {
                try {
                    const parsed = JSON.parse(stored);
                    const currentData = (window as any).PPR_DATA || {};

                    // Merge strategy: LocalStorage wins.
                    (window as any).PPR_DATA = { ...currentData, ...parsed };
                } catch (e) {
                    console.error('Failed to parse localStorage data', e);
                }
            }
            const allData = (window as any).PPR_DATA || {};

            // Always sync available years from data
            const years = Object.keys(allData).map(y => parseInt(y));
            // Ensure 2026 is always there, merge unique
            const distinctYears = Array.from(new Set([...years, 2026])).sort((a, b) => b - a);

            setAvailableYears(prev => {
                if (prev.length === distinctYears.length && prev.every((val, index) => val === distinctYears[index])) {
                    return prev;
                }
                return distinctYears;
            });

            const yearData = allData[currentYear] || [];

            if (yearData.length > 0) {
                setOkrs(yearData);
            } else if (currentYear === 2026) {
                // For 2026 specifically, we might want default data if empty? 
                // Assuming util keeps default.
                setOkrs(generateMockData());
            } else if (currentYear > 2026) {
                // Future years might reference previous or be empty
                setOkrs(generateMockData()); // Just mock for now or empty
            } else {
                setOkrs([]); // Empty for past/missing years
            }
        };

        loadData();
    }, [currentYear]);

    const { currentScore, accumulatedLoss, isEligible } = calculateScore(okrs);

    const updateMeasurement = (goalId: string, month: Month, status: GoalStatus, actual?: string | number, target?: string | number, comment?: string) => {
        let itemChanged = false;
        let goalTitle = '';
        let oldStatus = '';

        setOkrs(prevOkrs => {
            return prevOkrs.map(okr => ({
                ...okr,
                goals: okr.goals.map(goal => {
                    if (goal.id !== goalId) return goal;

                    oldStatus = goal.results[month].status;
                    goalTitle = goal.title;

                    // Helper for loose comparison
                    const normalize = (v: any) => (v === null || v === undefined) ? '' : String(v);

                    const isStatusChanged = oldStatus !== status;
                    const isActualChanged = normalize(goal.results[month].actualValue) !== normalize(actual);
                    const isTargetChanged = normalize(goal.results[month].targetValue) !== normalize(target);
                    const isCommentChanged = normalize(goal.results[month].comment) !== normalize(comment);

                    if (isStatusChanged || isActualChanged || isTargetChanged || isCommentChanged) {
                        itemChanged = true;

                        // Build detailed change message
                        const changes: string[] = [];
                        if (isStatusChanged) changes.push(`Status: ${oldStatus} -> ${status}`);
                        if (isActualChanged) changes.push(`Resultado: ${goal.results[month].actualValue || '-'} -> ${actual || '-'}`);
                        if (isTargetChanged) changes.push(`Meta: ${goal.results[month].targetValue || '-'} -> ${target || '-'}`);
                        if (isCommentChanged) changes.push(`Obs: alterada`);

                        const detailsMsg = `Alterou '${goalTitle.substring(0, 30)}...' em ${month}. ${changes.join('. ')}`;

                        const newLog: AuditLogEntry = {
                            id: crypto.randomUUID(),
                            timestamp: new Date().toISOString(),
                            user: typeof window !== 'undefined' ? window.USER_INFO?.name || 'Usuário' : 'Usuário',
                            action: 'Atualização de Meta',
                            details: detailsMsg
                        };
                        setAuditLogs(prev => [newLog, ...prev]);

                        return {
                            ...goal,
                            results: {
                                ...goal.results,
                                [month]: {
                                    ...goal.results[month],
                                    status,
                                    actualValue: actual,
                                    targetValue: target,
                                    comment: comment !== undefined ? comment : goal.results[month].comment,
                                    lastUpdated: new Date().toISOString()
                                }
                            }
                        };
                    }
                    return goal;
                })
            }));
        });

        // Auto-save logic
        // We know itemChanged is local to the closure, but we did the logic inside setState which is async.
        // But we can just use the same logic to update persistence layer.

        // Simulating immediate save for simplicity in this hybrid actions
        // Real app would wait for state effect or use proper sync.

        const allData = (window as any).PPR_DATA || {};
        let updatedOkrs = [...okrs]; // Note: this uses old state reference, but for immediate user action it might be ok if no concurrency
        // Actually best to re-run map on current 'okrs' state variable which is closure-captured.

        // Re-run the update logic for persistence
        updatedOkrs = updatedOkrs.map(okr => ({
            ...okr,
            goals: okr.goals.map(goal => {
                if (goal.id !== goalId) return goal;
                return {
                    ...goal,
                    results: {
                        ...goal.results,
                        [month]: {
                            ...goal.results[month],
                            status,
                            actualValue: actual,
                            targetValue: target, // Ensure target is passed
                            comment: comment !== undefined ? comment : goal.results[month].comment,
                            lastUpdated: new Date().toISOString()
                        }
                    }
                };
            })
        }));

        const dataToSave = (window as any).PPR_DATA || {};
        dataToSave[currentYear] = updatedOkrs;
        (window as any).PPR_DATA = dataToSave;
        localStorage.setItem('PPR_DATA', JSON.stringify(dataToSave));

        setHasUnsavedChanges(false); // Saved
    };

    const saveChanges = () => {
        const allData = (window as any).PPR_DATA || {};
        allData[currentYear] = okrs;
        (window as any).PPR_DATA = allData;
        localStorage.setItem('PPR_DATA', JSON.stringify(allData));
        setHasUnsavedChanges(false);
    };

    const createNewYear = (baseYear: number) => {
        const nextYear = baseYear + 1;
        const currentData = JSON.parse(JSON.stringify(okrs));
        const allData = (window as any).PPR_DATA || {};
        allData[baseYear] = currentData;

        const newOkrs = okrs.map(okr => ({
            ...okr,
            goals: okr.goals.map(goal => ({
                ...goal,
                description: '',
                results: Object.keys(goal.results).reduce((acc, m) => ({
                    ...acc,
                    [m]: {
                        month: m,
                        status: 'pending',
                        actualValue: '',
                        targetValue: '',
                        comment: '',
                        lastUpdated: undefined
                    }
                }), {} as Record<string, any>)
            }))
        }));

        allData[nextYear] = newOkrs;
        (window as any).PPR_DATA = allData;
        localStorage.setItem('PPR_DATA', JSON.stringify(allData));

        setComparisonData(prev => ({
            ...prev,
            [baseYear]: currentData
        }));

        setOkrs(newOkrs);
        setAvailableYears(prev => [...prev, nextYear].sort((a, b) => b - a));
        setCurrentYear(nextYear);
        setHasUnsavedChanges(true);

        const newLog: AuditLogEntry = {
            id: crypto.randomUUID(),
            timestamp: new Date().toISOString(),
            user: 'Gestor Atual',
            action: 'Criação de Ano',
            details: `Criou novo ciclo para o ano ${nextYear}`
        };
        setAuditLogs(prev => [newLog, ...prev]);
    };

    const updateGoalDefinition = (goalId: string, updates: Partial<Goal>) => {
        setOkrs(prev => prev.map(okr => ({
            ...okr,
            goals: okr.goals.map(goal => {
                if (goal.id === goalId) {
                    return { ...goal, ...updates };
                }
                return goal;
            })
        })));

        const allData = (window as any).PPR_DATA || {};
        const currentOkrs = allData[currentYear] || okrs;
        const updatedOkrs = currentOkrs.map((okr: OKRGroup) => ({
            ...okr,
            goals: okr.goals.map((g: Goal) => {
                if (g.id === goalId) {
                    return { ...g, ...updates };
                }
                return g;
            })
        }));

        allData[currentYear] = updatedOkrs;
        (window as any).PPR_DATA = allData;
        localStorage.setItem('PPR_DATA', JSON.stringify(allData));

        setHasUnsavedChanges(true);
    };

    const deleteGoal = (goalId: string) => {
        let goalTitle = '';
        const newOkrs = okrs.map(okr => ({
            ...okr,
            goals: okr.goals.filter(g => {
                if (g.id === goalId) {
                    goalTitle = g.title;
                    return false;
                }
                return true;
            })
        })).filter(okr => okr.goals.length > 0);

        setOkrs(newOkrs);

        const allData = (window as any).PPR_DATA || {};
        allData[currentYear] = newOkrs;
        (window as any).PPR_DATA = allData;
        localStorage.setItem('PPR_DATA', JSON.stringify(allData));

        const newLog: AuditLogEntry = {
            id: crypto.randomUUID(),
            timestamp: new Date().toISOString(),
            user: 'Gestor Atual',
            action: 'Exclusão de Meta',
            details: `Excluiu a meta '${goalTitle}'`
        };
        setAuditLogs(prev => [newLog, ...prev]);
        setHasUnsavedChanges(true);
    };

    const deleteYear = (targetYear: number) => {
        if (targetYear <= 2026) {
            alert('Não é possível excluir o ano base ou anteriores.');
            return;
        }

        const allData = (window as any).PPR_DATA || {};
        delete allData[targetYear];
        (window as any).PPR_DATA = allData;
        localStorage.setItem('PPR_DATA', JSON.stringify(allData));

        setComparisonData(prev => {
            const newData = { ...prev };
            delete newData[targetYear];
            return newData;
        });

        setAvailableYears(prev => prev.filter(y => y !== targetYear));

        if (currentYear === targetYear) {
            setCurrentYear(targetYear - 1);
        }

        const newLog: AuditLogEntry = {
            id: crypto.randomUUID(),
            timestamp: new Date().toISOString(),
            user: 'Gestor Atual',
            action: 'Exclusão de Ano',
            details: `Excluiu todo o ciclo do ano ${targetYear}`
        };
        setAuditLogs(prev => [newLog, ...prev]);
        setHasUnsavedChanges(true);
    };

    return (
        <PPRContext.Provider value={{
            state: { currentYear, okrs, auditLogs },
            currentScore,
            accumulatedLoss,
            isEligible,
            updateMeasurement,
            saveChanges,
            hasUnsavedChanges,

            currentYear,
            setCurrentYear,
            comparisonData,
            createNewYear,
            updateGoalDefinition,
            deleteGoal,
            deleteYear,
            availableYears,
            canEdit,
            canView
        }}>
            {children}
        </PPRContext.Provider>
    );
};

export const usePPR = () => {
    const context = useContext(PPRContext);
    if (!context) {
        throw new Error('usePPR must be used within a PPRProvider');
    }
    return context;
};
