import React, { useState } from 'react';
import Header from '../Header/Header';
import Sidebar from '../Sidebar/Sidebar';

const DashboardLayout = ({ children }) => {
  const [sidebarOpen, setSidebarOpen] = useState(true);

  const toggleSidebar = () => {
    setSidebarOpen(!sidebarOpen);
  };

  return (
    <div className="flex h-screen bg-light-background dark:bg-dark-background transition-colors duration-200">
      {/* Sidebar */}
      <Sidebar isOpen={sidebarOpen} />
      
      {/* Main Content */}
      <div className="flex-1 flex flex-col overflow-hidden">
        {/* Header */}
        <Header onToggleSidebar={toggleSidebar} sidebarOpen={sidebarOpen} />
        
        {/* Page Content */}
        <main className="flex-1 overflow-auto p-6">
          {children}
        </main>
      </div>
    </div>
  );
};

export default DashboardLayout;