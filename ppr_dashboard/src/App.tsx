import React, { useState } from 'react';
import { PPRProvider } from './context/PPRContext';
import Layout from './components/Layout';
import Dashboard from './components/Dashboard';
import AuditLog from './components/AuditLog';
import './index.css';

const App = () => {
    const [activeTab, setActiveTab] = useState<'dashboard' | 'audit'>('dashboard');

    const renderContent = () => {
        switch (activeTab) {
            case 'dashboard': return <Dashboard />;
            case 'audit': return (
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <AuditLog />
                </div>
            );
            default: return <Dashboard />;
        }
    };

    return (
        <PPRProvider>
            <Layout activeTab={activeTab} setActiveTab={setActiveTab}>
                {renderContent()}
            </Layout>
        </PPRProvider>
    );
};

export default App;
