import React from 'react';

const Loading = ({ size = 'large', text = 'Memuat...' }) => {
  const sizeClasses = {
    small: 'w-6 h-6',
    medium: 'w-8 h-8',
    large: 'w-12 h-12'
  };

  return (
    <div className="flex flex-col items-center justify-center min-h-screen bg-light-background dark:bg-dark-background">
      <div className={`${sizeClasses[size]} border-4 border-light-primary border-t-transparent rounded-full animate-spin dark:border-dark-primary dark:border-t-transparent`}></div>
      {text && (
        <p className="mt-4 text-light-textSecondary dark:text-dark-textSecondary">
          {text}
        </p>
      )}
    </div>
  );
};

export default Loading;