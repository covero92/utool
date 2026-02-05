import React, { useState, useEffect, useRef } from 'react';
import { Month, GoalStatus, GoalType } from '../types/ppr';

interface SmartCellProps {
    value?: string | number;
    target?: string | number;
    status: GoalStatus;
    type: GoalType;
    isReadOnly: boolean;
}

const SmartCell: React.FC<SmartCellProps & { onClick?: () => void }> = ({ value, target, status, type, isReadOnly, onClick }) => {

    const getStatusColor = () => {
        if (status === 'met') return 'bg-green-100 text-green-800 border-green-200';
        if (status === 'failed') return 'bg-red-100 text-red-800 border-red-200';
        return 'bg-white hover:bg-slate-50 focus-within:bg-blue-50 border-transparent';
    };

    return (
        <div
            className={`w-full h-full flex items-center justify-center p-1 transition-colors border-r border-b cursor-pointer ${getStatusColor()}`}
            onClick={onClick}
        >
            <div className={`w-full h-full flex items-center justify-center text-sm ${value === undefined || value === null || value === '' ? 'text-slate-500' : 'font-bold text-slate-900'}`}>
                {(value !== undefined && value !== null && value !== '') ? (
                    value
                ) : (target !== undefined && target !== null && target !== '') ? (
                    <span className="text-slate-600 italic font-medium text-xs" title={`Meta: ${target}`}>{target}</span>
                ) : (
                    <span className="opacity-100 text-xs text-slate-400 font-bold">-</span>
                )}
            </div>

            {status === 'failed' && (
                <span className="absolute bottom-0 right-0 text-[9px] font-bold text-red-600 bg-red-100 px-1 rounded-tl">-2</span>
            )}
        </div>
    );
};

export default SmartCell;
