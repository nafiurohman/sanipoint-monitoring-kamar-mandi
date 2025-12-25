import React from 'react';
import { useAuth } from '../../../contexts/AuthContext';
import { useTheme } from '../../../contexts/ThemeContext';
import ThemeToggle from '../../ui/ThemeToggle/ThemeToggle';
import { LogOut, User, Settings, Bell } from 'lucide-react';

const Header = ({ onToggleSidebar, sidebarOpen }) => {
  const { user, logout } = useAuth();
  const { theme } = useTheme();

  const handleLogout = async () => {
    if (window.confirm('Apakah Anda yakin ingin logout?')) {
      await logout();
    }
  };

  return (
    <header className="bg-light-surface border-b border-light-border dark:bg-dark-surface dark:border-dark-border transition-colors duration-200">
      <div className="flex items-center justify-between h-16 px-4">
        {/* Left Section */}
        <div className="flex items-center">
          <button
            onClick={onToggleSidebar}
            className="p-2 rounded-lg hover:bg-light-background dark:hover:bg-dark-background transition-colors duration-200"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          
          <div className="ml-4">
            <h1 className="text-lg font-semibold text-light-text dark:text-dark-text">
              BeznPOS
            </h1>
          </div>
        </div>

        {/* Right Section */}
        <div className="flex items-center space-x-4">
          {/* Theme Toggle */}
          <ThemeToggle />

          {/* Notifications */}
          <button className="relative p-2 rounded-lg hover:bg-light-background dark:hover:bg-dark-background transition-colors duration-200">
            <Bell size={20} className="text-light-textSecondary dark:text-dark-textSecondary" />
            <span className="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
          </button>

          {/* User Menu */}
          <div className="relative group">
            <button className="flex items-center space-x-2 p-2 rounded-lg hover:bg-light-background dark:hover:bg-dark-background transition-colors duration-200">
              <div className="w-8 h-8 bg-gradient-to-r from-light-primary to-blue-600 dark:from-dark-primary dark:to-blue-500 rounded-full flex items-center justify-center">
                <User size={16} className="text-white" />
              </div>
              <div className="text-left hidden md:block">
                <p className="text-sm font-medium text-light-text dark:text-dark-text">
                  {user?.nama_lengkap}
                </p>
                <p className="text-xs text-light-textSecondary dark:text-dark-textSecondary capitalize">
                  {user?.role}
                </p>
              </div>
            </button>

            {/* Dropdown Menu */}
            <div className="absolute right-0 top-12 hidden group-hover:block w-48 z-50">
              <div className="bg-light-surface border border-light-border rounded-lg shadow-lg p-2 dark:bg-dark-surface dark:border-dark-border">
                <div className="px-3 py-2 border-b border-light-border dark:border-dark-border mb-2">
                  <p className="text-sm font-medium text-light-text dark:text-dark-text">
                    {user?.nama_lengkap}
                  </p>
                  <p className="text-xs text-light-textSecondary dark:text-dark-textSecondary capitalize">
                    {user?.role}
                  </p>
                </div>
                
                <button className="flex items-center w-full px-3 py-2 rounded-md text-sm text-light-text hover:bg-light-background dark:text-dark-text dark:hover:bg-dark-background transition-colors duration-200">
                  <Settings size={16} className="mr-2" />
                  Settings
                </button>
                
                <button 
                  onClick={handleLogout}
                  className="flex items-center w-full px-3 py-2 rounded-md text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 transition-colors duration-200"
                >
                  <LogOut size={16} className="mr-2" />
                  Logout
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>
  );
};

export default Header;