import React, { useState } from 'react';
import { usePPR } from '../context/PPRContext';
import { ChevronDown, ChevronRight, Info, MessageSquare, FileText } from 'lucide-react';
import { OKRGroup, Month, MONTHS, GoalStatus } from '../types/ppr';
import SmartCell from './SmartCell';
import MeasurementModal from './MeasurementModal';

const GoalsTable = () => {
    const { state, updateMeasurement, currentYear, comparisonData, canEdit } = usePPR();
    const isReadOnly = currentYear < 2026 || !canEdit;

    // Helper to find same goal in previous year
    const findHistoryGoal = (year: number, okrIndex: number, goalIndex: number) => {
        try {
            return comparisonData[year]?.[okrIndex]?.goals[goalIndex];
        } catch (e) {
            return null;
        }
    };

    // Initialize expanded groups state (all expanded by default)
    const [expandedGroups, setExpandedGroups] = useState<string[]>([]);

    React.useEffect(() => {
        if (state.okrs.length > 0 && expandedGroups.length === 0) {
            setExpandedGroups(state.okrs.map(o => o.id));
        }
    }, [state.okrs]);

    const toggleGroup = (id: string) => {
        setExpandedGroups(prev =>
            prev.includes(id) ? prev.filter(g => g !== id) : [...prev, id]
        );
    };

    // Modal State
    const [modalOpen, setModalOpen] = useState(false);
    const [selectedGoalData, setSelectedGoalData] = useState<{
        goalId: string;
        goalTitle: string;
        goalRule: string;
        month: Month;
        status: GoalStatus;
        actual?: string | number;
        target?: string | number;
        comment?: string;
    } | null>(null);

    const handleOpenModal = (goal: any, month: Month) => {
        const result = goal.results[month];
        setSelectedGoalData({
            goalId: goal.id,
            goalTitle: goal.title,
            goalRule: goal.description || '',
            month,
            status: result.status,
            actual: result.actualValue,
            target: result.targetValue,
            comment: result.comment
        });
        setModalOpen(true);
    };

    const handleSaveModal = (status: GoalStatus, actual?: number, target?: number, comment?: string) => {
        if (selectedGoalData) {
            updateMeasurement(
                selectedGoalData.goalId,
                selectedGoalData.month,
                status,
                actual,
                target,
                comment
            );
        }
    };

    return (

        <div className="space-y-8">
            {state.okrs.map((okr, okrIndex) => (
                <div key={okr.id} className="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div
                        className="bg-gradient-to-r from-blue-50 to-white px-6 py-4 border-b border-blue-100 flex justify-between items-center cursor-pointer hover:from-blue-100 transition-all"
                        onClick={() => toggleGroup(okr.id)}
                    >
                        <div className="flex items-center gap-3">
                            <div className="p-1 bg-blue-100 rounded-md text-blue-600">
                                {expandedGroups.includes(okr.id) ? <ChevronDown size={20} /> : <ChevronRight size={20} />}
                            </div>
                            <h3 className="font-bold text-slate-800 text-lg">{okr.title}</h3>
                        </div>
                    </div>

                    {expandedGroups.includes(okr.id) && (
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm text-center">
                                <thead>
                                    <tr className="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500 font-bold tracking-wider">
                                        <th className="px-6 py-4 text-left w-[250px] text-blue-900 bg-blue-50/50">Meta / Indicador</th>
                                        {MONTHS.map(m => (
                                            <th key={m} className="px-1 py-3 text-center min-w-[70px] text-blue-700">{m}</th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {okr.goals.map((goal, goalIndex) => {
                                        const prevYear1 = findHistoryGoal(currentYear - 1, okrIndex, goalIndex);

                                        return (
                                            <React.Fragment key={goal.id}>
                                                {/* Header Row for Goal */}
                                                <tr className="bg-white">
                                                    <td colSpan={13} className="px-6 py-4 text-left border-l-4 border-blue-500 bg-slate-50/30 mt-4">
                                                        <div className="font-bold text-slate-800 text-base flex items-center gap-2">
                                                            {goal.title}
                                                            {goal.penaltyPoints && (
                                                                <span className="text-[10px] bg-slate-100 text-slate-500 border border-slate-200 px-2 py-0.5 rounded-full font-medium" title="Penalidade por descumprimento (Regra)">
                                                                    Regra: -{goal.penaltyPoints} pts
                                                                </span>
                                                            )}
                                                        </div>
                                                    </td>
                                                </tr>

                                                {/* ROW 1: Status */}
                                                <tr className="bg-slate-100">
                                                    <td className="px-6 py-3 font-bold text-left border-r border-slate-300 text-xs uppercase tracking-wide text-slate-800">
                                                        Status Mensal
                                                    </td>
                                                    {MONTHS.map(m => {
                                                        const status = goal.results[m].status;
                                                        return (
                                                            <td key={m} className="text-center border-r border-slate-300 p-2 bg-slate-50">
                                                                {status === 'met' && (
                                                                    <div className="mx-auto w-7 h-7 rounded-md bg-green-200 text-green-900 flex items-center justify-center font-black text-sm shadow-sm border border-green-400">
                                                                        ✓
                                                                    </div>
                                                                )}
                                                                {status === 'failed' && (
                                                                    <div className="mx-auto w-7 h-7 rounded-md bg-red-200 text-red-900 flex items-center justify-center font-black text-sm shadow-sm border border-red-400">
                                                                        ✕
                                                                    </div>
                                                                )}
                                                                {status === 'pending' && <span className="text-slate-400 font-bold text-xl leading-none">-</span>}

                                                                {/* Note Button */}
                                                                {!isReadOnly && (
                                                                    <button
                                                                        onClick={() => handleOpenModal(goal, m)}
                                                                        className={`mt-1 p-1 rounded-full hover:bg-slate-200 transition-colors ${goal.results[m].comment ? 'text-blue-700' : 'text-slate-400 hover:text-slate-600'}`}
                                                                        title={goal.results[m].comment || "Adicionar observação"}
                                                                    >
                                                                        <MessageSquare size={14} fill={goal.results[m].comment ? "currentColor" : "none"} />
                                                                    </button>
                                                                )}
                                                            </td>
                                                        );
                                                    })}
                                                </tr>

                                                {/* ROW 2: Current Year Data */}
                                                <tr className="bg-white">
                                                    <td className="px-6 py-4 font-bold text-left border-r border-slate-300 text-blue-950 bg-blue-100/50 text-sm">
                                                        {currentYear} <span className="text-xs text-blue-800 font-bold uppercase ml-1">(Realizado)</span>
                                                    </td>
                                                    {MONTHS.map(m => (
                                                        <td key={m} className="p-0 border-r border-slate-300 h-14 relative">
                                                            <SmartCell
                                                                value={goal.results[m].actualValue}
                                                                target={goal.results[m].targetValue}
                                                                status={goal.results[m].status}
                                                                type={goal.type}
                                                                isReadOnly={isReadOnly}
                                                                onClick={() => !isReadOnly && handleOpenModal(goal, m)}
                                                            />
                                                        </td>
                                                    ))}
                                                </tr>

                                                {/* ROW 3: History Data */}
                                                <tr className="bg-slate-100 text-sm text-slate-700 border-b-4 border-slate-300">
                                                    <td className="px-6 py-3 italic text-left border-r border-slate-300 font-bold text-slate-700">
                                                        {currentYear - 1} <span className="text-xs text-slate-600 font-bold uppercase ml-1">(Referência)</span>
                                                    </td>
                                                    {MONTHS.map(m => (
                                                        <td key={m} className="text-center border-r border-slate-300 py-3 font-mono font-bold text-slate-700 bg-slate-100/50">
                                                            {prevYear1?.results[m]?.actualValue || '-'}
                                                        </td>
                                                    ))}
                                                </tr>
                                            </React.Fragment>
                                        )
                                    })}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            ))}
            {/* Measurement Modal */}
            {selectedGoalData && (
                <MeasurementModal
                    isOpen={modalOpen}
                    onClose={() => setModalOpen(false)}
                    onSave={handleSaveModal}
                    goalName={selectedGoalData.goalTitle}
                    month={selectedGoalData.month}
                    currentStatus={selectedGoalData.status}
                    initialActual={selectedGoalData.actual as any}
                    initialTarget={selectedGoalData.target as any}
                    initialComment={selectedGoalData.comment}
                    ruleDescription={selectedGoalData.goalRule}
                />
            )}
        </div>
    );
};

export default GoalsTable;
