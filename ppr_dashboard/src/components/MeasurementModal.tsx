import React, { useState, useEffect } from 'react';
import { X, Check, AlertCircle, MessageSquare } from 'lucide-react';
import { GoalStatus, Month } from '../types/ppr';

interface MeasurementModalProps {
    isOpen: boolean;
    onClose: () => void;
    onSave: (status: GoalStatus, actual?: number, target?: number, comment?: string) => void;
    goalName: string;
    month: Month;
    currentStatus: GoalStatus;
    initialActual?: number;
    initialTarget?: number;
    initialComment?: string;
    ruleDescription: string;
}

const MeasurementModal: React.FC<MeasurementModalProps> = ({
    isOpen, onClose, onSave, goalName, month, currentStatus, initialActual, initialTarget, initialComment, ruleDescription
}) => {
    const [actual, setActual] = useState<string>(initialActual?.toString() || '');
    const [target, setTarget] = useState<string>(initialTarget?.toString() || '');
    const [comment, setComment] = useState<string>(initialComment || '');
    const [status, setStatus] = useState<GoalStatus>(currentStatus);

    useEffect(() => {
        if (isOpen) {
            setActual(initialActual?.toString() || '');
            setTarget(initialTarget?.toString() || '');
            setComment(initialComment || '');
            setStatus(currentStatus);
        }
    }, [isOpen, initialActual, initialTarget, currentStatus]);

    // Auto-calculate status based on values if provided
    useEffect(() => {
        if (actual !== '' && target !== '') {
            const numActual = parseFloat(actual);
            const numTarget = parseFloat(target);

            // Default logic: Higher is better (e.g., Sales, Engagement)
            // But for "Time" (TME), lower is better.
            // We need a way to know the goal type or direction.
            // For now, let's assume if it finds 'm' or 's' in the original string it might be time, but here we just have numbers.
            // HACK: Start with simple ">= target" is met. 
            // The user can override. 
            // EXCEPT if 0 is entered and target is > 0, it should be failed.

            // To be safer and avoid overriding user intent too aggressively, 
            // we only update if the status hasn't been manually touched? 
            // But here we are reacting to value changes.

            if (!isNaN(numActual) && !isNaN(numTarget)) {
                // Heuristic: If target is small integer (like 1, 2, 3), it's likely a counter/score where higher is better.
                // If goalName contains "Tempo" or "TME", lower is better.
                const isTimeGoal = goalName.toLowerCase().includes('tempo') || goalName.toLowerCase().includes('tme');

                let isMet = false;
                if (isTimeGoal) {
                    isMet = numActual <= numTarget;
                } else {
                    isMet = numActual >= numTarget;
                }

                setStatus(isMet ? 'met' : 'failed');
            }
        }
    }, [actual, target, goalName]);

    if (!isOpen) return null;

    const handleSave = () => {
        // Try to parse if it looks like a clean number, otherwise keep as string
        const parseValue = (val: string) => {
            if (!val) return undefined;
            const num = parseFloat(val);
            // If it's a valid number and the string is just that number (not "10 errors"), use number
            // Actually, keep logic simple: pass as is? 
            // The context supports string | number.
            // But for checks (>=), we might want numbers.
            // Let's force proper number if !isNaN, else string
            return !isNaN(num) && isFinite(num) && val.trim() === num.toString() ? num : val;
        };

        onSave(status, parseValue(actual) as any, parseValue(target) as any, comment);
        onClose();
    };

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white rounded-lg shadow-xl w-full max-w-2xl p-6">
                <div className="flex justify-between items-center mb-6">
                    <h3 className="text-xl font-bold text-slate-800">
                        {month} - {goalName}
                    </h3>
                    <button onClick={onClose} className="text-slate-400 hover:text-slate-600">
                        <X size={24} />
                    </button>
                </div>

                <div className="mb-6">
                    <label className="block text-sm font-medium text-slate-700 mb-1">Descrição / Observações</label>
                    <textarea
                        value={comment}
                        onChange={(e) => setComment(e.target.value)}
                        className="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm bg-slate-50/50"
                        placeholder="Descreva detalhadamente o resultado, justificativas, links..."
                        rows={8}
                    />
                </div>

                <div className="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">Meta Esperada</label>
                        <input
                            type="text"
                            value={target}
                            onChange={(e) => setTarget(e.target.value)}
                            className="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none font-medium text-lg bg-slate-50 text-slate-500"
                            placeholder="Ex: 100"
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1 text-blue-700">Resultado (Entregue)</label>
                        <input
                            type="text"
                            value={actual}
                            onChange={(e) => setActual(e.target.value)}
                            className="w-full px-4 py-3 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none font-bold text-lg text-blue-700"
                            placeholder="Valor final"
                        />
                    </div>
                </div>

                <div className="mb-6">
                    <label className="block text-sm font-medium text-slate-700 mb-2">Status do Mês</label>
                    <div className="flex gap-2">
                        <button
                            onClick={() => setStatus('met')}
                            className={`flex-1 py-2 px-3 rounded flex items-center justify-center gap-2 border ${status === 'met' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'}`}
                        >
                            <Check size={16} /> Batida
                        </button>
                        <button
                            onClick={() => setStatus('failed')}
                            className={`flex-1 py-2 px-3 rounded flex items-center justify-center gap-2 border ${status === 'failed' ? 'bg-red-100 border-red-500 text-red-700' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'}`}
                        >
                            <X size={16} /> Não Batida
                        </button>
                    </div>
                </div>

                <div className="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button onClick={onClose} className="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded">
                        Cancelar
                    </button>
                    <button onClick={handleSave} className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium">
                        Salvar Resultado
                    </button>
                </div>
            </div>
        </div>
    );
};

export default MeasurementModal;
