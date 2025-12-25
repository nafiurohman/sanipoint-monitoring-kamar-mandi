import React from 'react';
import { useTheme } from '../../../contexts/ThemeContext';

// Simple chart component tanpa Chart.js untuk sementara
const SalesChart = ({ data, type = 'line', title, height = 300 }) => {
  const { theme } = useTheme();

  const isDark = theme === 'dark' || (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);

  return (
    <div className="bg-light-surface dark:bg-dark-surface rounded-lg border border-light-border dark:border-dark-border p-6">
      {title && (
        <h3 className="text-lg font-semibold text-light-text dark:text-dark-text mb-4">
          {title}
        </h3>
      )}
      
      {/* Placeholder chart - akan diganti dengan Chart.js nanti */}
      <div 
        className="flex items-end justify-between space-x-2"
        style={{ height: `${height - 80}px` }}
      >
        {data?.labels?.map((label, index) => (
          <div key={index} className="flex flex-col items-center flex-1">
            <div 
              className="w-full bg-blue-500 rounded-t transition-all duration-300 hover:bg-blue-600"
              style={{ 
                height: `${(data.datasets[0].data[index] / Math.max(...data.datasets[0].data)) * 100}%`,
                minHeight: '20px'
              }}
            ></div>
            <span className="text-xs text-light-textSecondary dark:text-dark-textSecondary mt-2">
              {label}
            </span>
          </div>
        ))}
      </div>
      
      {!data?.labels && (
        <div 
          className="flex items-center justify-center text-light-textSecondary dark:text-dark-textSecondary"
          style={{ height: `${height - 80}px` }}
        >
          Data chart tidak tersedia
        </div>
      )}
    </div>
  );
};

export default SalesChart;