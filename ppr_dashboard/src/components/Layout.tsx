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
            <header className="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-40 shadow-sm">
                <div className="max-w-7xl mx-auto flex justify-between items-center">
                    <div className="flex items-center gap-8">
                        <div className="flex items-center gap-4">
                            <button
                                onClick={() => window.location.href = '/utool/'}
                                className="bg-slate-100 hover:bg-slate-200 text-slate-600 p-2 rounded-lg transition-colors"
                                title="Voltar ao Início"
                            >
                                <ArrowLeft size={20} />
                            </button>
                            <div>
                                <h1 className="text-xl font-bold text-slate-900 leading-tight">Gestão de <span className="text-blue-600">PPR</span></h1>
                                <p className="text-[10px] text-slate-500 uppercase tracking-wider font-bold">Painel de Controle</p>
                            </div>
                        </div>

                        {/* Navigation Buttons - Moved Here */}
                        <div className="hidden md:flex items-center bg-slate-100/50 p-1 rounded-lg border border-slate-200/50">
                            <button
                                onClick={() => setActiveTab('dashboard')}
                                className={`flex items-center gap-2 px-4 py-1.5 rounded-md text-sm font-medium transition-all ${activeTab === 'dashboard' ? 'bg-white text-blue-700 shadow-sm border border-slate-200/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-200/50'
                                    }`}
                            >
                                <LayoutDashboard size={16} />
                                Dashboard
                            </button>

                            <button
                                onClick={() => setActiveTab('audit')}
                                className={`flex items-center gap-2 px-4 py-1.5 rounded-md text-sm font-medium transition-all ${activeTab === 'audit' ? 'bg-white text-blue-700 shadow-sm border border-slate-200/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-200/50'
                                    }`}
                            >
                                <History size={16} />
                                Auditoria
                            </button>
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
                        <div className="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold shadow-md text-sm">
                            SM
                        </div>
                    </div>
                </div>
            </header>

            {/* Sub-header info only */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div className="flex items-center gap-2 text-sm text-slate-500">
                    <span className="font-medium text-slate-800">Visão Geral</span>
                    <span>/</span>
                    <span>Ciclo {currentYear}</span>
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
