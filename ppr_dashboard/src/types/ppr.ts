export type Month = 'Jan' | 'Fev' | 'Mar' | 'Abr' | 'Mai' | 'Jun' | 'Jul' | 'Ago' | 'Set' | 'Out' | 'Nov' | 'Dez';

export const MONTHS: Month[] = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

export interface UserInfo {
    name: string;
    role: string;
    capabilities: string[];
}

declare global {
    interface Window {
        USER_INFO: UserInfo;
        PPR_DATA: any;
    }
}

export type GoalStatus = 'pending' | 'met' | 'failed';

export type GoalType = 'percentage' | 'time' | 'numeric' | 'currency';

export interface MonthlyResult {
    month: Month;
    status: GoalStatus;
    actualValue?: string | number; // Changed to support formatted strings like "3m 12s"
    targetValue?: string | number;
    comment?: string;
    lastUpdated?: string;
}

export interface Goal {
    id: string;
    title: string;
    description: string; // The "Rule" or "Meta" description
    type: GoalType;
    weight: number;
    shortName?: string;
    penaltyPoints?: number;
    maxPenalty?: number;
    results: Record<Month, MonthlyResult>;
}

export interface OKRGroup {
    id: string;
    title: string; // "OKR 1 - Interação..."
    goals: Goal[];
}

export interface AuditLogEntry {
    id: string;
    timestamp: string;
    user: string;
    action: string;
    details: string; // "Alterou meta de Fev de 'Batida' para 'Não Batida'"
}

export interface PPRState {
    currentYear: number;
    okrs: OKRGroup[];
    auditLogs: AuditLogEntry[];
}

export interface PPRContextType {
    state: PPRState;
    currentScore: number;
    accumulatedLoss: number;
    isEligible: boolean;
    updateMeasurement: (goalId: string, month: Month, status: GoalStatus, actual?: string | number, target?: string | number, comment?: string) => void;
    saveChanges: () => void;
    hasUnsavedChanges: boolean;
    currentYear: number;
    setCurrentYear: (year: number) => void;
    comparisonData: Record<number, OKRGroup[]>;
    createNewYear: (baseYear: number) => void;
    updateGoalDefinition: (goalId: string, updates: Partial<Goal>) => void;
    deleteGoal: (goalId: string) => void;
    deleteYear: (year: number) => void;
    availableYears: number[];
    canEdit: boolean;
    canView: boolean;
}
