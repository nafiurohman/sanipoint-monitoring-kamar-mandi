// SANIPOINT Main JavaScript - Enhanced Version
class SaniPoint {
    constructor() {
        this.init();
        this.startRealtimeUpdates();
        this.setupAnimations();
    }
    
    init() {
        this.setupCSRFToken();
        this.setupEventListeners();
        this.setupModals();
        this.setupTooltips();
        this.setupLazyLoading();
    }
    
    setupCSRFToken() {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) {
            this.csrfToken = token;
        }
    }
    
    setupEventListeners() {
        // Enhanced form submissions with loading states
        document.addEventListener('submit', (e) => {
            if (e.target.classList.contains('ajax-form')) {
                e.preventDefault();
                this.handleAjaxForm(e.target);
            }
        });
        
        // Modal triggers with animation
        document.addEventListener('click', (e) => {
            if (e.target.hasAttribute('data-modal')) {
                e.preventDefault();
                this.openModal(e.target.getAttribute('data-modal'));
            }
            
            if (e.target.classList.contains('modal-close') || e.target.closest('.modal-close')) {
                this.closeModal();
            }
        });
        
        // Enhanced cart functionality
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-to-cart')) {
                e.preventDefault();
                this.addToCart(e.target);
            }
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Escape key closes modals
            if (e.key === 'Escape') {
                this.closeModal();
            }
            
            // Ctrl/Cmd + K for search (future feature)
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                // TODO: Open search modal
            }
        });
    }
    
    setupModals() {
        // Close modal on outside click
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-overlay')) {
                this.closeModal();
            }
        });
    }
    
    setupTooltips() {
        // Simple tooltip system
        document.querySelectorAll('[data-tooltip]').forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target, e.target.getAttribute('data-tooltip'));
            });
            
            element.addEventListener('mouseleave', () => {
                this.hideTooltip();
            });
        });
    }
    
    setupLazyLoading() {
        // Lazy load images and content
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const element = entry.target;
                    if (element.dataset.src) {
                        element.src = element.dataset.src;
                        element.removeAttribute('data-src');
                    }
                    observer.unobserve(element);
                }
            });
        });
        
        document.querySelectorAll('[data-src]').forEach(img => {
            observer.observe(img);
        });
    }
    
    setupAnimations() {
        // Animate elements on scroll
        const animateObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                }
            });
        }, { threshold: 0.1 });
        
        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            animateObserver.observe(el);
        });
    }
    
    async handleAjaxForm(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn?.innerHTML;
        
        try {
            // Show loading state
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
            }
            
            const formData = new FormData(form);
            formData.append('csrf_token', this.csrfToken);
            
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showToast(result.message, 'success');
                if (form.hasAttribute('data-reload')) {
                    setTimeout(() => location.reload(), 1000);
                }
                this.closeModal();
                form.reset();
            } else {
                this.showToast(result.message, 'error');
                this.displayFormErrors(form, result.errors);
            }
        } catch (error) {
            this.showToast('Terjadi kesalahan sistem', 'error');
            console.error('Form submission error:', error);
        } finally {
            // Reset button state
            if (submitBtn && originalText) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
    }
    
    displayFormErrors(form, errors) {
        // Clear previous errors
        form.querySelectorAll('.error-message').forEach(el => el.remove());
        
        if (errors) {
            Object.keys(errors).forEach(field => {
                const input = form.querySelector(`[name="${field}"]`);
                if (input) {
                    input.classList.add('border-red-500', 'focus:ring-red-500');
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message text-red-500 text-sm mt-1 animate-slide-up';
                    errorDiv.textContent = errors[field];
                    input.parentNode.appendChild(errorDiv);
                }
            });
        }
    }
    
    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('animate-fade-in');
            const content = modal.querySelector('.modal-content, .bg-white, .bg-gray-800');
            if (content) {
                content.classList.add('animate-scale-in');
            }
            document.body.style.overflow = 'hidden';
        }
    }
    
    closeModal() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.add('hidden');
            modal.classList.remove('animate-fade-in');
        });
        document.body.style.overflow = '';
        
        // Clear form errors when closing
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('.border-red-500').forEach(el => {
            el.classList.remove('border-red-500', 'focus:ring-red-500');
        });
    }
    
    showToast(message, type = 'info', duration = 5000) {
        if (window.themeManager) {
            const toast = window.themeManager.createToast(message, type, duration);
            window.themeManager.showToast(toast);
        } else {
            // Fallback notification
            alert(message);
        }
    }
    
    showTooltip(element, text) {
        const tooltip = document.createElement('div');
        tooltip.id = 'tooltip';
        tooltip.className = 'absolute z-50 px-3 py-2 text-sm bg-gray-900 dark:bg-gray-700 text-white rounded-lg shadow-lg pointer-events-none animate-fade-in';
        tooltip.textContent = text;
        
        document.body.appendChild(tooltip);
        
        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
    }
    
    hideTooltip() {
        const tooltip = document.getElementById('tooltip');
        if (tooltip) {
            tooltip.remove();
        }
    }
    
    async addToCart(button) {
        const productId = button.getAttribute('data-product-id');
        const quantity = 1;
        
        // Add loading state
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding...';
        
        try {
            const response = await fetch('/sanipoint/karyawan/marketplace', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'add_to_cart',
                    product_id: productId,
                    quantity: quantity,
                    csrf_token: this.csrfToken
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showToast('Produk ditambahkan ke keranjang', 'success');
                this.updateCartCount();
                
                // Add visual feedback
                button.classList.add('bg-green-500', 'text-white');
                button.innerHTML = '<i class="fas fa-check mr-2"></i>Added!';
                
                setTimeout(() => {
                    button.classList.remove('bg-green-500', 'text-white');
                    button.innerHTML = originalText;
                    button.disabled = false;
                }, 2000);
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            this.showToast(error.message || 'Gagal menambahkan ke keranjang', 'error');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    }
    
    updateCartCount() {
        const cartCount = document.getElementById('cart-count');
        if (cartCount) {
            const currentCount = parseInt(cartCount.textContent) || 0;
            cartCount.textContent = currentCount + 1;
            
            // Animate the count
            cartCount.classList.add('animate-pulse');
            setTimeout(() => {
                cartCount.classList.remove('animate-pulse');
            }, 1000);
        }
    }
    
    startRealtimeUpdates() {
        // Update every 5 seconds
        setInterval(() => {
            this.updateRealtimeData();
        }, 5000);
    }
    
    async updateRealtimeData() {
        try {
            const response = await fetch('/sanipoint/api/realtime-status');
            const data = await response.json();
            
            this.updateBathroomStatus(data.bathrooms);
            this.updateSensorData(data.sensors);
            this.updateConnectionStatus(true);
        } catch (error) {
            console.error('Failed to fetch realtime data:', error);
            this.updateConnectionStatus(false);
        }
    }
    
    updateBathroomStatus(bathrooms) {
        bathrooms?.forEach(bathroom => {
            const statusElement = document.querySelector(`[data-bathroom-id="${bathroom.id}"] .bathroom-status`);
            if (statusElement) {
                statusElement.className = `bathroom-status status-badge status-${bathroom.computed_status}`;
                statusElement.textContent = this.formatStatus(bathroom.computed_status);
            }
            
            const visitorElement = document.querySelector(`[data-bathroom-id="${bathroom.id}"] .visitor-count`);
            if (visitorElement) {
                visitorElement.textContent = `${bathroom.current_visitors}/${bathroom.max_visitors}`;
            }
        });
    }
    
    updateSensorData(sensors) {
        sensors?.forEach(sensor => {
            const sensorElement = document.querySelector(`[data-sensor-id="${sensor.id}"] .sensor-value`);
            if (sensorElement) {
                sensorElement.textContent = `${sensor.value} ${sensor.unit || ''}`;
            }
        });
    }
    
    updateConnectionStatus(isConnected) {
        const statusElement = document.getElementById('connection-status');
        if (statusElement) {
            if (isConnected) {
                statusElement.className = 'w-2 h-2 bg-green-500 rounded-full animate-pulse';
            } else {
                statusElement.className = 'w-2 h-2 bg-red-500 rounded-full';
            }
        }
    }
    
    formatStatus(status) {
        const statusMap = {
            'available': 'Tersedia',
            'needs_cleaning': 'Perlu Dibersihkan',
            'being_cleaned': 'Sedang Dibersihkan',
            'maintenance': 'Maintenance'
        };
        return statusMap[status] || status;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.sanipoint = new SaniPoint();
});

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatPoints(points) {
    return points.toLocaleString('id-ID') + ' pts';
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}