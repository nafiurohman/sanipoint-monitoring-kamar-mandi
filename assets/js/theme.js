// Simplified Theme Management - Dark/Light only
class ThemeManager {
    constructor() {
        // Default to system preference if no setting exists
        const savedTheme = localStorage.getItem('theme');
        if (!savedTheme) {
            const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            this.theme = systemDark ? 'dark' : 'light';
        } else {
            this.theme = savedTheme === 'dark' ? 'dark' : 'light';
        }
        this.init();
    }
    
    init() {
        this.applyTheme();
        this.setupToggle();
    }
    
    applyTheme() {
        const html = document.documentElement;
        const themeIcon = document.getElementById('theme-icon');
        const themeText = document.querySelector('#theme-toggle .sidebar-text');
        
        html.classList.remove('dark');
        
        if (this.theme === 'dark') {
            html.classList.add('dark');
            if (themeIcon) themeIcon.className = 'fas fa-sun';
            if (themeText) themeText.textContent = 'Mode Terang';
        } else {
            if (themeIcon) themeIcon.className = 'fas fa-moon';
            if (themeText) themeText.textContent = 'Mode Gelap';
        }
        
        localStorage.setItem('theme', this.theme);
        
        // Trigger chart update if exists
        if (window.employeeChart) {
            this.updateChartColors();
        }
    }
    
    setupToggle() {
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleTheme();
            });
        }
    }
    
    toggleTheme() {
        this.theme = this.theme === 'dark' ? 'light' : 'dark';
        this.applyTheme();
    }
    
    updateChartColors() {
        const isDark = this.theme === 'dark';
        const textColor = isDark ? '#fff' : '#374151';
        const gridColor = isDark ? '#374151' : '#E5E7EB';
        
        if (window.employeeChart) {
            window.employeeChart.options.plugins.legend.labels.color = textColor;
            window.employeeChart.options.scales.y.ticks.color = textColor;
            window.employeeChart.options.scales.y.grid.color = gridColor;
            window.employeeChart.options.scales.x.ticks.color = textColor;
            window.employeeChart.options.scales.x.grid.color = gridColor;
            window.employeeChart.update();
        }
        
        if (window.pointChart) {
            window.pointChart.options.plugins.legend.labels.color = textColor;
            window.pointChart.update();
        }
    }
    
    // Compatibility method - delegate to main SaniPoint class
    createToast(message, type = 'info', duration = 5000) {
        if (window.sanipoint && window.sanipoint.showToast) {
            window.sanipoint.showToast(message, type, duration);
        } else {
            // Simple fallback toast
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            toast.innerHTML = `<span>${message}</span>`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), duration);
        }
    }
}

// Initialize immediately
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        window.themeManager = new ThemeManager();
    });
} else {
    window.themeManager = new ThemeManager();
}