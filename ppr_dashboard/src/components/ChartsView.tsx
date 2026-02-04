import React from 'react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

const ChartsView = () => {
    // Mock Data for the chart
    const data = [
        { name: 'Jan', ano2025: 100, ano2026: 100 },
        { name: 'Fev', ano2025: 98, ano2026: null },
        { name: 'Mar', ano2025: 96, ano2026: null },
        { name: 'Abr', ano2025: 96, ano2026: null },
        { name: 'Mai', ano2025: 94, ano2026: null },
        { name: 'Jun', ano2025: 90, ano2026: null },
        { name: 'Jul', ano2025: 88, ano2026: null }, // Future
        { name: 'Ago', ano2025: 88, ano2026: null },
        { name: 'Set', ano2025: 86, ano2026: null },
        { name: 'Out', ano2025: 86, ano2026: null },
        { name: 'Nov', ano2025: 84, ano2026: null },
        { name: 'Dez', ano2025: 84, ano2026: null },
    ];

    return (
        <div className="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <h3 className="font-bold text-slate-800 text-lg mb-6">Comparativo Global: 2025 vs 2026</h3>
            <div className="h-96 w-full">
                <ResponsiveContainer width="100%" height="100%">
                    <LineChart
                        data={data}
                        margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
                    >
                        <CartesianGrid strokeDasharray="3 3" stroke="#f1f5f9" />
                        <XAxis dataKey="name" stroke="#64748b" fontSize={12} />
                        <YAxis stroke="#64748b" fontSize={12} domain={[0, 100]} />
                        <Tooltip />
                        <Legend />
                        <Line type="monotone" dataKey="ano2025" stroke="#94a3b8" name="2025 (Final: 84)" strokeWidth={2} dot={false} />
                        <Line type="monotone" dataKey="ano2026" stroke="#2563eb" name="2026 (Atual)" strokeWidth={3} activeDot={{ r: 8 }} />
                    </LineChart>
                </ResponsiveContainer>
            </div>
            <p className="text-sm text-slate-500 mt-4 text-center">
                * A linha azul representa o ano corrente. Pontuação mínima para elegibilidade: 70 pontos.
            </p>
        </div>
    );
};

export default ChartsView;
