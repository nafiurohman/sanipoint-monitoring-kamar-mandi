import React from 'react';
import { NavLink, useLocation } from 'react-router-dom';
import { useAuth } from '../../../contexts/AuthContext';
import { 
  LayoutDashboard, 
  Users, 
  Package, 
  ShoppingCart, 
  BarChart3, 
  FileText,
  Settings,
  Store
} from 'lucide-react';
import { ROLES } from '../../../utils/constants/roles';

const Sidebar = ({ isOpen }) => {
  const { user } = useAuth();
  const location = useLocation();

  const navigationItems = {
    [ROLES.PEMILIK]: [
      { path: '/owner', label: 'Dashboard', icon: LayoutDashboard },
      { path: '/owner/users', label: 'Manajemen User', icon: Users },
      { path: '/owner/products', label: 'Produk', icon: Package },
      { path: '/owner/transactions', label: 'Transaksi', icon: ShoppingCart },
      { path: '/owner/analytics', label: 'Analytics', icon: BarChart3 },
      { path: '/owner/reports', label: 'Laporan', icon: FileText },
      { path: '/owner/settings', label: 'Settings', icon: Settings }
    ],
    [ROLES.ADMIN]: [
      { path: '/admin', label: 'Dashboard', icon: LayoutDashboard },
      { path: '/admin/users', label: 'Manajemen User', icon: Users },
      { path: '/admin/products', label: 'Produk', icon: Package },
      { path: '/admin/transactions', label: 'Transaksi', icon: ShoppingCart },
      { path: '/admin/analytics', label: 'Analytics', icon: BarChart3 },
      { path: '/admin/reports', label: 'Laporan', icon: FileText },
      { path: '/admin/settings', label: 'Settings', icon: Settings }
    ],
    [ROLES.KASIR]: [
      { path: '/kasir', label: 'Dashboard', icon: LayoutDashboard },
      { path: '/kasir/pos', label: 'POS', icon: ShoppingCart },
      { path: '/kasir/transactions', label: 'Transaksi', icon: FileText },
      { path: '/kasir/profile', label: 'Profile', icon: Users },
      { path: '/kasir/settings', label: 'Settings', icon: Settings }
    ]
  };

  const items = navigationItems[user?.role] || [];

  const getBasePath = (path) => {
    if (user?.role === ROLES.PEMILIK) return '/owner';
    if (user?.role === ROLES.ADMIN) return '/admin';
    if (user?.role === ROLES.KASIR) return '/kasir';
    return '/';
  };

  const isActiveLink = (path) => {
    const basePath = getBasePath();
    if (path === basePath) {
      return location.pathname === basePath;
    }
    return location.pathname.startsWith(path);
  };

  return (
    <aside className={`
      bg-light-surface border-r border-light-border dark:bg-dark-surface dark:border-dark-border
      transition-all duration-300 ease-in-out overflow-hidden
      ${isOpen ? 'w-64' : 'w-0 md:w-20'}
    `}>
      <div className="flex flex-col h-full">
        {/* Logo */}
        <div className="flex items-center justify-center p-4 border-b border-light-border dark:border-dark-border">
          {isOpen ? (
            <div className="flex items-center space-x-2">
              <Store className="text-light-primary dark:text-dark-primary" size={24} />
              <span className="text-lg font-bold text-light-text dark:text-dark-text">
                BeznPOS
              </span>
            </div>
          ) : (
            <Store className="text-light-primary dark:text-dark-primary" size={24} />
          )}
        </div>

        {/* Navigation */}
        <nav className="flex-1 p-4 space-y-2">
          {items.map((item) => {
            const Icon = item.icon;
            const isActive = isActiveLink(item.path);
            
            return (
              <NavLink
                key={item.path}
                to={item.path}
                className={`
                  flex items-center rounded-lg px-3 py-3 transition-all duration-200 group
                  ${isActive
                    ? 'bg-light-primary text-white shadow-lg shadow-blue-500/25 dark:bg-dark-primary'
                    : 'text-light-textSecondary hover:bg-light-background hover:text-light-text dark:text-dark-textSecondary dark:hover:bg-dark-background dark:hover:text-dark-text'
                  }
                  ${!isOpen && 'justify-center'}
                `}
                title={!isOpen ? item.label : ''}
              >
                <Icon 
                  size={20} 
                  className={`
                    ${isOpen ? 'mr-3' : 'mx-auto'}
                    ${isActive ? 'text-white' : ''}
                  `} 
                />
                {isOpen && (
                  <span className="font-medium">{item.label}</span>
                )}
                
                {/* Tooltip for collapsed state */}
                {!isOpen && (
                  <div className="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-50">
                    {item.label}
                  </div>
                )}
              </NavLink>
            );
          })}
        </nav>

        {/* User Info (only when expanded) */}
        {isOpen && (
          <div className="p-4 border-t border-light-border dark:border-dark-border">
            <div className="flex items-center space-x-3">
              <div className="w-10 h-10 bg-gradient-to-r from-light-primary to-blue-600 dark:from-dark-primary dark:to-blue-500 rounded-full flex items-center justify-center">
                <Users size={20} className="text-white" />
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-light-text dark:text-dark-text truncate">
                  {user?.nama_lengkap}
                </p>
                <p className="text-xs text-light-textSecondary dark:text-dark-textSecondary capitalize">
                  {user?.role}
                </p>
              </div>
            </div>
          </div>
        )}
      </div>
    </aside>
  );
};

export default Sidebar;