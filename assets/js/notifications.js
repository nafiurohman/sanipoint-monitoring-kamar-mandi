// Enhanced Toast Notification System
console.log('🍞 Toast Notification System Loading...');

class ToastNotification {
    constructor() {
        console.log('📢 ToastNotification class initialized');
        this.container = this.createContainer();
        this.toasts = [];
    }
    
    createContainer() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed top-4 right-4 z-50 space-y-2';
            document.body.appendChild(container);
        }
        return container;
    }
    
    show(message, type = 'info', duration = 5000) {
        console.log(`📢 Toast: [${type.toUpperCase()}] ${message}`);
        const toast = this.createToast(message, type, duration);
        this.container.appendChild(toast);
        this.toasts.push(toast);
        
        // Animate in
        setTimeout(() => {
            toast.classList.add('animate-slide-in');
        }, 10);
        
        // Auto remove
        setTimeout(() => {
            this.remove(toast);
        }, duration);
        
        return toast;
    }
    
    createToast(message, type, duration) {
        const toast = document.createElement('div');
        toast.className = `toast-notification bg-white dark:bg-gray-800 border-l-4 p-4 rounded-lg shadow-lg max-w-sm transform translate-x-full transition-all duration-300 ${this.getTypeClasses(type)}`;
        
        toast.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-${this.getIcon(type)} ${this.getIconColor(type)}"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">${message}</p>
                </div>
                <div class="ml-4 flex-shrink-0">
                    <button class="toast-close text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
            </div>
            <div class="toast-progress absolute bottom-0 left-0 h-1 bg-current opacity-30 transition-all duration-${duration}" style="width: 100%"></div>
        `;
        
        // Close button functionality
        toast.querySelector('.toast-close').addEventListener('click', () => {
            this.remove(toast);
        });
        
        // Progress bar animation
        setTimeout(() => {
            const progress = toast.querySelector('.toast-progress');
            if (progress) {
                progress.style.width = '0%';
            }
        }, 100);
        
        return toast;
    }
    
    getTypeClasses(type) {
        const classes = {
            success: 'border-green-500',
            error: 'border-red-500',
            warning: 'border-yellow-500',
            info: 'border-blue-500'
        };
        return classes[type] || classes.info;
    }
    
    getIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || icons.info;
    }
    
    getIconColor(type) {
        const colors = {
            success: 'text-green-500',
            error: 'text-red-500',
            warning: 'text-yellow-500',
            info: 'text-blue-500'
        };
        return colors[type] || colors.info;
    }
    
    remove(toast) {
        if (toast && toast.parentNode) {
            toast.classList.add('animate-slide-out');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
                this.toasts = this.toasts.filter(t => t !== toast);
            }, 300);
        }
    }
    
    clear() {
        this.toasts.forEach(toast => this.remove(toast));
    }
}

// Confirmation Dialog System
class ConfirmationDialog {
    constructor() {
        this.modal = this.createModal();
        this.setupEventListeners();
    }
    
    createModal() {
        let modal = document.getElementById('confirmation-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'confirmation-modal';
            modal.className = 'hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center';
            modal.innerHTML = `
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-2xl max-w-md w-full mx-4 animate-scale-in">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900/30 mb-4">
                            <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2" id="confirmation-title">Konfirmasi</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6" id="confirmation-message">Apakah Anda yakin?</p>
                        <div class="flex space-x-3">
                            <button id="confirmation-cancel" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                Batal
                            </button>
                            <button id="confirmation-confirm" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                                Ya, Lanjutkan
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
        return modal;
    }
    
    setupEventListeners() {
        // Close on outside click
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.hide();
            }
        });
        
        // Cancel button
        document.getElementById('confirmation-cancel').addEventListener('click', () => {
            this.hide();
        });
        
        // ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !this.modal.classList.contains('hidden')) {
                this.hide();
            }
        });
    }
    
    show(options = {}) {
        const {
            title = 'Konfirmasi',
            message = 'Apakah Anda yakin?',
            confirmText = 'Ya, Lanjutkan',
            cancelText = 'Batal',
            type = 'warning',
            onConfirm = () => {},
            onCancel = () => {}
        } = options;
        
        // Update content
        document.getElementById('confirmation-title').textContent = title;
        document.getElementById('confirmation-message').textContent = message;
        
        const confirmBtn = document.getElementById('confirmation-confirm');
        const cancelBtn = document.getElementById('confirmation-cancel');
        
        confirmBtn.textContent = confirmText;
        cancelBtn.textContent = cancelText;
        
        // Update button style based on type
        confirmBtn.className = `flex-1 px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors ${this.getButtonClass(type)}`;
        
        // Remove previous event listeners
        const newConfirmBtn = confirmBtn.cloneNode(true);
        const newCancelBtn = cancelBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
        
        // Add new event listeners
        newConfirmBtn.addEventListener('click', () => {
            onConfirm();
            this.hide();
        });
        
        newCancelBtn.addEventListener('click', () => {
            onCancel();
            this.hide();
        });
        
        // Show modal
        this.modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        return new Promise((resolve) => {
            newConfirmBtn.addEventListener('click', () => resolve(true));
            newCancelBtn.addEventListener('click', () => resolve(false));
        });
    }
    
    getButtonClass(type) {
        const classes = {
            danger: 'bg-red-600 hover:bg-red-700',
            warning: 'bg-yellow-600 hover:bg-yellow-700',
            info: 'bg-blue-600 hover:bg-blue-700',
            success: 'bg-green-600 hover:bg-green-700'
        };
        return classes[type] || classes.warning;
    }
    
    hide() {
        this.modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

// Global instances
window.toast = new ToastNotification();
window.confirm = new ConfirmationDialog();

// Utility functions
window.showToast = (message, type = 'info', duration = 5000) => {
    return window.toast.show(message, type, duration);
};

window.showConfirm = (options) => {
    return window.confirm.show(options);
};

// Replace default alert and confirm
window.originalAlert = window.alert;
window.originalConfirm = window.confirm;

window.alert = (message) => {
    window.toast.show(message, 'info');
};

// Enhanced confirm with promise support
window.confirmAction = (message, title = 'Konfirmasi') => {
    return window.confirm.show({
        title: title,
        message: message,
        type: 'warning'
    });
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize toast and confirmation systems
    if (!window.toast) {
        window.toast = new ToastNotification();
    }
    if (!window.confirm) {
        window.confirm = new ConfirmationDialog();
    }
    
    // Add confirmation to delete buttons
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('delete-btn') || e.target.closest('.delete-btn')) {
            e.preventDefault();
            const button = e.target.classList.contains('delete-btn') ? e.target : e.target.closest('.delete-btn');
            const itemName = button.getAttribute('data-name') || 'item ini';
            
            window.confirm.show({
                title: 'Hapus Data',
                message: `Apakah Anda yakin ingin menghapus ${itemName}? Tindakan ini tidak dapat dibatalkan.`,
                type: 'danger',
                confirmText: 'Ya, Hapus',
                onConfirm: () => {
                    // If it's a form, submit it
                    const form = button.closest('form');
                    if (form) {
                        form.submit();
                    } else if (button.href) {
                        window.location.href = button.href;
                    }
                }
            });
        }
    });
});