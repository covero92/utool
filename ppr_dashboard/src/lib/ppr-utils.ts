import { OKRGroup, Goal, MonthlyResult, MONTHS } from '../types/ppr';

export const INITIAL_SCORE = 100;
export const MIN_SCORE_THRESHOLD = 70;

export const calculateScore = (okrs: OKRGroup[]) => {
    let loss = 0;

    okrs.forEach(okr => {
        okr.goals.forEach(goal => {
            // Count failures
            let failures = 0;
            MONTHS.forEach(month => {
                if (goal.results[month]?.status === 'failed') {
                    failures++;
                }
            });

            // Calculate penalty for this goal
            const penaltyVal = goal.penaltyPoints ?? 2;
            const maxPenaltyVal = goal.maxPenalty ?? 24;
            const penalty = Math.min(failures * penaltyVal, maxPenaltyVal);
            loss += penalty;
        });
    });

    const currentScore = Math.max(0, INITIAL_SCORE - loss);
    return {
        currentScore,
        accumulatedLoss: loss,
        isEligible: currentScore >= MIN_SCORE_THRESHOLD
    };
};

export const generateMockData = (): OKRGroup[] => {
    // Generate empty results for all months
    const emptyResults = () => {
        const res: any = {};
        MONTHS.forEach(m => res[m] = { month: m, status: 'pending' });
        return res;
    };

    return [
        {
            id: 'okr-1',
            title: 'OKR 1 - Interação e criação de conteúdo para as revendas',
            goals: [
                {
                    id: 'g1',
                    shortName: 'Engajamento',
                    description: 'Realizar ao menos 50 atividades...',
                    penaltyPoints: 2,
                    maxPenalty: 24,
                    ruleDescription: 'O não cumprimento mensal resulta na perda de 2 pontos.',
                    results: emptyResults()
                },
                {
                    id: 'g2',
                    shortName: 'Documentação EAD/Wiki',
                    description: 'Criar ou dar manutenção em pelo menos 8 documentações...',
                    penaltyPoints: 2,
                    maxPenalty: 24,
                    ruleDescription: 'Meta de 8 docs/mês',
                    results: emptyResults()
                }
            ]
        },
        {
            id: 'okr-2',
            title: 'OKR 2 - Satisfação e agilidade',
            goals: [
                {
                    id: 'g3',
                    shortName: 'Tempo médio de espera',
                    description: 'Reduzir o tempo médio de espera (TME)...',
                    penaltyPoints: 2,
                    maxPenalty: 24,
                    ruleDescription: '< 3min/mês no 1º sem, < 2:45 no 2º sem',
                    results: emptyResults()
                },
                {
                    id: 'g4',
                    shortName: 'FCR',
                    description: 'Resolução em primeiro contato...',
                    penaltyPoints: 2,
                    maxPenalty: 24,
                    ruleDescription: '85%+ no 1º sem, 90%+ no 2º sem',
                    results: emptyResults()
                }
            ]
        }
    ];
};
