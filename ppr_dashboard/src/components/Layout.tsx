import React, { useState } from 'react';
import { LayoutDashboard, History, ArrowLeft } from 'lucide-react';
import { usePPR } from '../context/PPRContext';

interface LayoutProps {
    children: React.ReactNode;
    activeTab: 'dashboard' | 'audit';
    setActiveTab: (tab: 'dashboard' | 'audit') => void;
}

const Layout: React.FC<LayoutProps> = ({ children, activeTab, setActiveTab }) => {
    const { currentYear, setCurrentYear, availableYears } = usePPR();

    return (
        <div className="min-h-screen bg-slate-100 font-sans text-slate-900">
            {/* Header */}
            <header className="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-40">
                <div className="max-w-7xl mx-auto flex justify-between items-center">
                    <div className="flex items-center gap-4">
                        <button
                            onClick={() => window.location.href = '/utool/'}
                            className="bg-slate-100 hover:bg-slate-200 text-slate-600 p-2 rounded-lg transition-colors"
                            title="Voltar ao Início"
                        >
                            <ArrowLeft size={20} />
                        </button>
                        <div>
                            <h1 className="text-2xl font-bold text-slate-900">Gestão de <span className="text-blue-600">PPR</span></h1>
                            <p className="text-xs text-slate-500">Acompanhamento de metas e resultados.</p>
                        </div>
                    </div>

                    <div className="flex items-center gap-4">
                        <select
                            value={currentYear}
                            onChange={(e) => setCurrentYear(Number(e.target.value))}
                            className="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2"
                        >
                            {/* Generate dynamic range including current year and recent past */}
                            {availableYears.map(year => (
                                <option key={year} value={year}>{year}</option>
                            ))}
                        </select>
                        <div className="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold border border-blue-200">
                            SM
                        </div>
                    </div>
                </div>
            </header>

            {/* Navigation Bar / Breadcrumbs */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div className="flex items-center gap-2 text-sm text-slate-500 mb-4">
                    <span className="font-medium text-slate-800">PPR {currentYear}</span>
                </div>

                <div className="flex space-x-1 bg-white p-1 rounded-lg border border-slate-200 w-fit">
                    <button
                        onClick={() => setActiveTab('dashboard')}
                        className={`flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium transition-all ${activeTab === 'dashboard' ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-slate-600 hover:bg-slate-50'
                            }`}
                    >
                        <LayoutDashboard size={18} />
                        Dashboard
                    </button>

                    <button
                        onClick={() => setActiveTab('audit')}
                        className={`flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium transition-all ${activeTab === 'audit' ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-slate-600 hover:bg-slate-50'
                            }`}
                    >
                        <History size={18} />
                        Auditoria
                    </button>
                </div>
            </div>

            {/* Main Content */}
            <main>
                {children}
            </main>
        </div>
    );
};

export default Layout;
