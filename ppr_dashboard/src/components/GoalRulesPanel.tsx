import { usePPR } from '../context/PPRContext';
import { Edit2, Save, X, Trash2 } from 'lucide-react';
import React, { useState } from 'react';

const GoalRulesPanel = () => {
    const { state, updateGoalDefinition, deleteGoal, canEdit } = usePPR();
    const [isEditing, setIsEditing] = useState(false);
    const [editValues, setEditValues] = useState<Record<string, any>>({});

    const handleEditToggle = () => {
        if (!isEditing) {
            // Initialize edit values
            const initialValues: any = {};
            state.okrs.forEach(okr => {
                okr.goals.forEach(goal => {
                    initialValues[goal.id] = {
                        title: goal.title,
                        description: goal.description,
                        penaltyPoints: goal.penaltyPoints
                    };
                });
            });
            setEditValues(initialValues);
        }
        setIsEditing(!isEditing);
    };

    const handleSave = () => {
        // Commit changes
        Object.keys(editValues).forEach(goalId => {
            updateGoalDefinition(goalId, editValues[goalId]);
        });
        setIsEditing(false);
        alert('Regras atualizadas!');
    };

    const handleChange = (goalId: string, field: string, value: any) => {
        setEditValues(prev => ({
            ...prev,
            [goalId]: {
                ...prev[goalId],
                [field]: value
            }
        }));
    };

    return (
        <div className="bg-white p-6 h-full">
            <div className="flex justify-between items-center mb-6">
                <h3 className="text-lg font-bold text-slate-800 flex items-center gap-2">
                    📜 Regras & Metas
                </h3>
                {canEdit && (
                    <button
                        onClick={isEditing ? handleSave : handleEditToggle}
                        className={`flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors mr-12 ${isEditing ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'}`}
                    >
                        {isEditing ? <><Save size={16} /> Salvar</> : <><Edit2 size={16} /> Editar</>}
                    </button>
                )}
            </div>

            <div className="space-y-6">
                {state.okrs.map((okr) => (
                    <div key={okr.id} className="border-b border-slate-100 last:border-0 pb-4 last:pb-0">
                        <div className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                            {okr.title}
                        </div>
                        <div className="space-y-4">
                            {okr.goals.map((goal) => (
                                <div key={goal.id} className="group mb-4 last:mb-0">
                                    {isEditing ? (
                                        <div className="space-y-2 p-3 bg-slate-50 rounded border border-slate-200 relative group/edit">
                                            <input
                                                type="text"
                                                value={editValues[goal.id]?.title || ''}
                                                onChange={(e) => handleChange(goal.id, 'title', e.target.value)}
                                                className="w-full text-sm font-semibold text-slate-700 border-slate-300 rounded focus:ring-blue-500 focus:border-blue-500 pr-8"
                                                placeholder="Nome da Meta"
                                            />
                                            <textarea
                                                value={editValues[goal.id]?.description || ''}
                                                onChange={(e) => handleChange(goal.id, 'description', e.target.value)}
                                                className="w-full text-xs text-slate-500 border-slate-300 rounded focus:ring-blue-500 focus:border-blue-500"
                                                rows={3}
                                                placeholder="Descrição da regra..."
                                            />
                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center gap-2">
                                                    <label className="text-xs text-slate-500">Penalidade:</label>
                                                    <input
                                                        type="number"
                                                        value={editValues[goal.id]?.penaltyPoints || 0}
                                                        onChange={(e) => handleChange(goal.id, 'penaltyPoints', parseInt(e.target.value))}
                                                        className="w-16 text-xs border-slate-300 rounded"
                                                    />
                                                </div>
                                                <button
                                                    onClick={() => {
                                                        if (confirm('Tem certeza que deseja excluir esta meta?')) {
                                                            deleteGoal(goal.id);
                                                            // Also remove from editValues to avoid errors specific to form handling if needed, 
                                                            // but component will re-render and remove this block anyway.
                                                        }
                                                    }}
                                                    className="text-red-400 hover:text-red-600 p-1 hover:bg-red-50 rounded transition-colors"
                                                    title="Excluir Meta"
                                                >
                                                    <Trash2 size={16} />
                                                </button>
                                            </div>
                                        </div>
                                    ) : (
                                        <>
                                            <div className="font-semibold text-slate-700 text-sm mb-1">
                                                {goal.title}
                                            </div>
                                            <div className="text-xs text-slate-500 leading-relaxed bg-slate-50 p-2 rounded">
                                                {goal.description}
                                                {goal.penaltyPoints && (
                                                    <div className="mt-1 text-red-400 font-medium">
                                                        Penalidade: -{goal.penaltyPoints} pts
                                                    </div>
                                                )}
                                            </div>
                                        </>
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
};

export default GoalRulesPanel;
