import React from 'react';
import { usePPR } from '../context/PPRContext';
import { History } from 'lucide-react';

const AuditLog = () => {
    const { state } = usePPR();

    return (
        <div className="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div className="p-6 border-b border-slate-200 flex items-center gap-2">
                <History className="text-slate-500" />
                <h3 className="font-bold text-slate-800 text-lg">Histórico de Alterações</h3>
            </div>

            <div className="overflow-x-auto">
                <table className="w-full text-sm text-left">
                    <thead className="text-xs text-slate-500 uppercase bg-slate-50">
                        <tr>
                            <th className="px-6 py-3">Data/Hora</th>
                            <th className="px-6 py-3">Usuário</th>
                            <th className="px-6 py-3">Ação</th>
                            <th className="px-6 py-3">Detalhes</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {state.auditLogs.length === 0 ? (
                            <tr>
                                <td colSpan={4} className="px-6 py-8 text-center text-slate-400">
                                    Nenhuma alteração registrada ainda.
                                </td>
                            </tr>
                        ) : (
                            state.auditLogs.map((log) => (
                                <tr key={log.id} className="bg-white hover:bg-slate-50">
                                    <td className="px-6 py-4 whitespace-nowrap text-slate-600">
                                        {new Date(log.timestamp).toLocaleString('pt-BR')}
                                    </td>
                                    <td className="px-6 py-4 font-medium text-slate-900">
                                        {log.user}
                                    </td>
                                    <td className="px-6 py-4">
                                        <span className="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                            {log.action}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 text-slate-600">
                                        {log.details}
                                    </td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

export default AuditLog;
