import React from 'react';
import ScoreCard from './ScoreCard';
import GoalsTable from './GoalsTable';
import GoalRulesPanel from './GoalRulesPanel';
import { usePPR } from '../context/PPRContext';
import { PlusCircle, Trash2 } from 'lucide-react';

const Dashboard = () => {
    const { createNewYear, deleteYear, currentYear, canEdit } = usePPR();
    const [showRules, setShowRules] = React.useState(false);

    const handleCreateNextYear = () => {
        if (confirm(`Deseja criar o ano de ${currentYear + 1}? Isso iniciará um novo ciclo zerado base das regras atuais.`)) {
            createNewYear(currentYear);
        }
    };

    const handleDeleteYear = () => {
        if (confirm(`Tem certeza que deseja excluir o ano de ${currentYear}? Todas as informações deste ano serão perdidas.`)) {
            deleteYear(currentYear);
        }
    };

    return (
        <div className="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8 relative">
            <div className="grid grid-cols-12 gap-6 items-start">

                {/* Main Content: Score & Table (Full Width) */}
                <div className="col-span-12 space-y-8">
                    <div className="flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-slate-200">
                        <div className="text-slate-500 text-sm">
                            Ciclo atual: <span className="font-bold text-slate-800">{currentYear}</span>
                        </div>
                        <div className="flex gap-2">
                            {canEdit && currentYear > 2026 && (
                                <button
                                    onClick={handleDeleteYear}
                                    className="bg-red-50 text-red-600 hover:bg-red-100 px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors border border-red-100"
                                >
                                    <Trash2 size={18} />
                                    Excluir Ano
                                </button>
                            )}
                            {canEdit && (
                                <button
                                    onClick={handleCreateNextYear}
                                    className="bg-indigo-50 text-indigo-600 hover:bg-indigo-100 px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors"
                                >
                                    <PlusCircle size={18} />
                                    Ano Sequente
                                </button>
                            )}
                        </div>
                    </div>

                    <ScoreCard />

                    <div>
                        <div className="mb-4 flex items-center justify-between">
                            <h2 className="text-xl font-bold text-slate-800">
                                Acompanhamento de Metas
                            </h2>
                            <div className="flex items-center gap-4">
                                <button
                                    onClick={() => setShowRules(true)}
                                    className="text-sm font-medium text-blue-600 hover:text-blue-800 bg-blue-50 px-3 py-1.5 rounded-lg flex items-center gap-2 transition-colors border border-blue-100"
                                >
                                    📜 Ver Regras
                                </button>
                                <div className="text-sm text-slate-500">
                                    Clique nos meses para atualizar
                                </div>
                            </div>
                        </div>

                        <GoalsTable />
                    </div>
                </div>
            </div>

            {/* Rules Modal */}
            {showRules && (
                <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
                    <div className="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto relative animate-in fade-in zoom-in duration-200">
                        <button
                            onClick={() => setShowRules(false)}
                            className="absolute top-4 right-4 p-2 hover:bg-slate-100 rounded-full text-slate-400 hover:text-slate-600 transition-colors"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M18 6 6 18" /><path d="m6 6 18 18" /></svg>
                        </button>

                        <div className="p-1">
                            <GoalRulesPanel />
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default Dashboard;
