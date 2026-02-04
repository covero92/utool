import React from 'react';
import { usePPR } from '../context/PPRContext';
import { Trophy, AlertTriangle, TrendingDown, Save } from 'lucide-react';

const ScoreCard = () => {
    const { currentScore, accumulatedLoss, isEligible, hasUnsavedChanges, saveChanges, currentYear } = usePPR();
    const MIN_THRESHOLD = 70;

    // Visual helper
    const getScoreColor = () => {
        if (!isEligible) return 'bg-red-600';
        if (currentScore < 80) return 'bg-yellow-500';
        return 'bg-blue-600';
    };

    return (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            {/* Main Score Board */}
            <div className={`md:col-span-2 rounded-xl p-8 text-white relative overflow-hidden shadow-lg transition-colors duration-500 ${getScoreColor()}`}>
                <div className="relative z-10 flex justify-between items-start">
                    <div>
                        <h2 className="text-blue-100 font-semibold tracking-wide text-sm uppercase mb-1">Pontuação Atual ({currentYear})</h2>
                        <div className="text-7xl font-bold tracking-tighter">
                            {currentScore}
                        </div>
                        <p className="mt-2 text-blue-100 opacity-90 flex items-center gap-2">
                            {isEligible
                                ? `Acima do mínimo (${MIN_THRESHOLD})`
                                : <span className="flex items-center gap-1 font-bold text-white"><AlertTriangle size={16} /> Crítico: Abaixo do mínimo!</span>
                            }
                        </p>
                    </div>
                    <div className="p-4 bg-white/10 rounded-full backdrop-blur-sm">
                        <Trophy size={48} className="text-white" />
                    </div>
                </div>

                {/* Progress Bar Background */}
                <div className="absolute bottom-0 left-0 w-full h-2 bg-black/20">
                    <div
                        className="h-full bg-white/50 transition-all duration-1000"
                        style={{ width: `${currentScore}%` }}
                    />
                </div>
            </div>

            {/* Stats & Actions */}
            <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between">
                <div>
                    <h3 className="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        Status das Metas
                    </h3>

                    <div className="space-y-4">
                        <div className="flex justify-between items-center p-3 bg-red-50 rounded-lg border border-red-100">
                            <div className="flex items-center gap-2 text-red-700">
                                <TrendingDown size={18} />
                                <span className="text-sm font-medium">Perdas Acumuladas</span>
                            </div>
                            <span className="font-bold text-red-700 text-lg">{accumulatedLoss > 0 ? `-${accumulatedLoss}` : '0'}</span>
                        </div>

                        <div className="flex justify-between items-center p-3 bg-slate-50 rounded-lg border border-slate-100">
                            <div className="text-sm text-slate-600">Projeção Final</div>
                            <span className="font-bold text-slate-800 text-lg">{currentScore}</span>
                        </div>
                    </div>
                </div>

                {hasUnsavedChanges && (
                    <button
                        onClick={saveChanges}
                        className="w-full mt-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg flex items-center justify-center gap-2 font-medium transition-colors shadow-md animate-pulse"
                    >
                        <Save size={18} />
                        Salvar Alterações
                    </button>
                )}
            </div>
        </div>
    );
};

export default ScoreCard;
