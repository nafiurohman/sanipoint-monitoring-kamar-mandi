import React from 'react';
import { Sun, Moon, Monitor } from 'lucide-react';
import { useTheme } from '../../../contexts/ThemeContext';
import { THEMES, THEME_LABELS } from '../../../utils/constants/themes';

const ThemeToggle = () => {
  const { theme, changeTheme } = useTheme();

  const themes = [
    { value: THEMES.LIGHT, label: THEME_LABELS[THEMES.LIGHT], icon: Sun },
    { value: THEMES.DARK, label: THEME_LABELS[THEMES.DARK], icon: Moon },
    { value: THEMES.AUTO, label: THEME_LABELS[THEMES.AUTO], icon: Monitor }
  ];

  const getCurrentIcon = () => {
    const currentTheme = themes.find(t => t.value === theme);
    return currentTheme ? currentTheme.icon : Sun;
  };

  const CurrentIcon = getCurrentIcon();

  return (
    <div className="relative group">
      <button
        className="flex items-center justify-center w-10 h-10 rounded-lg bg-light-surface hover:bg-gray-100 text-light-text border border-light-border transition-colors duration-200 dark:bg-dark-surface dark:hover:bg-slate-700 dark:text-dark-text dark:border-dark-border"
        onClick={() => {
          const currentIndex = themes.findIndex(t => t.value === theme);
          const nextIndex = (currentIndex + 1) % themes.length;
          changeTheme(themes[nextIndex].value);
        }}
        title={`Current: ${THEME_LABELS[theme]}`}
      >
        <CurrentIcon size={18} />
      </button>
      
      {/* Theme dropdown */}
      <div className="absolute right-0 top-12 hidden group-hover:block w-48 z-50">
        <div className="bg-light-surface border border-light-border rounded-lg shadow-lg p-2 dark:bg-dark-surface dark:border-dark-border">
          {themes.map((themeOption) => {
            const Icon = themeOption.icon;
            return (
              <button
                key={themeOption.value}
                className={`flex items-center w-full px-3 py-2 rounded-md text-sm transition-colors duration-200 ${
                  theme === themeOption.value
                    ? 'bg-light-primary text-white dark:bg-dark-primary'
                    : 'text-light-text hover:bg-gray-100 dark:text-dark-text dark:hover:bg-slate-700'
                }`}
                onClick={() => changeTheme(themeOption.value)}
              >
                <Icon size={16} className="mr-2" />
                {themeOption.label}
              </button>
            );
          })}
        </div>
      </div>
    </div>
  );
};

export default ThemeToggle;