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
            console.log('CSRF token loaded:', token.substring(0, 10) + '...');
        } else {
            console.error('CSRF token not found in meta tag');
        }
    }
    
    setupEventListeners() {
        // Enhanced form submissions with loading states
        document.addEventListener('submit', (e) => {
            if (e.target.classList.contains('ajax-form')) {
                e.preventDefault();
                console.log('Ajax form submitted:', e.target);
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
        console.log('=== AJAX FORM SUBMISSION START ===');
        console.log('Form:', form);
        console.log('Form ID:', form.id);
        console.log('Form classes:', form.className);
        
        // Special logging for transfer forms
        if (form.id === 'transfer-form' || window.location.pathname.includes('transfer')) {
            console.log('🔄 TRANSFER FORM DETECTED');
        }
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn?.innerHTML;
        
        console.log('Submit button found:', !!submitBtn);
        console.log('Original button text:', originalText);
        
        try {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
                console.log('Button disabled and loading state applied');
            }
            
            const formData = new FormData(form);
            formData.append('csrf_token', this.csrfToken);
            
            console.log('=== FORM DATA ANALYSIS ===');
            for (let [key, value] of formData.entries()) {
                if (key.includes('pin') || key.includes('password')) {
                    console.log(key + ':', value ? 'PROVIDED (' + value.length + ' chars)' : 'EMPTY');
                } else {
                    console.log(key + ':', value);
                }
            }
            
            // Use current page URL for form submission
            const actionUrl = window.location.pathname;
            console.log('Submission URL:', actionUrl);
            console.log('Full URL:', window.location.href);
            
            // Special transfer logging
            if (form.id === 'transfer-form' || window.location.pathname.includes('transfer')) {
                console.log('🔄 TRANSFER SPECIFIC DATA:');
                console.log('- To User ID:', formData.get('to_user_id'));
                console.log('- Amount:', formData.get('amount'));
                console.log('- PIN provided:', formData.get('pin') ? 'YES' : 'NO');
                console.log('- Description:', formData.get('description') || 'None');
            }
            
            console.log('Sending fetch request...');
            const response = await fetch(actionUrl, {
                method: 'POST',
                body: formData
            });
            
            console.log('=== RESPONSE ANALYSIS ===');
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            console.log('Response headers:', Object.fromEntries(response.headers.entries()));
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const responseText = await response.text();
            console.log('Raw response text:', responseText);
            console.log('Response length:', responseText.length);
            
            let result;
            try {
                result = JSON.parse(responseText);
                console.log('=== PARSED JSON RESULT ===');
                console.log('Success:', result.success);
                console.log('Message:', result.message);
                console.log('Full result:', result);
                
                // Special transfer logging
                if (form.id === 'transfer-form' || window.location.pathname.includes('transfer')) {
                    console.log('🔄 TRANSFER RESULT:', result.success ? '✅ SUCCESS' : '❌ FAILED');
                    if (!result.success) {
                        console.log('🔄 TRANSFER ERROR:', result.message);
                    }
                }
            } catch (parseError) {
                console.error('=== JSON PARSE ERROR ===');
                console.error('Parse error:', parseError);
                console.log('Response is not valid JSON, might be HTML error page');
                throw new Error('Server returned invalid JSON: ' + responseText.substring(0, 200));
            }
            
            if (result.success) {
                console.log('=== SUCCESS HANDLING ===');
                window.showToast(result.message, 'success');
                
                if (result.credentials) {
                    console.log('Credentials received:', result.credentials);
                    this.showCredentials(result.credentials);
                }
                
                if (form.hasAttribute('data-reload')) {
                    console.log('Form has data-reload attribute, will reload in 2 seconds');
                    setTimeout(() => location.reload(), 2000);
                } else if (form.id === 'transfer-form') {
                    console.log('🔄 Transfer successful, will reload in 1.5 seconds');
                    setTimeout(() => location.reload(), 1500);
                }
                this.closeModal();
                form.reset();
                console.log('Form reset and modal closed');
            } else {
                console.log('=== FAILURE HANDLING ===');
                console.log('Error message:', result.message);
                console.log('Errors object:', result.errors);
                window.showToast(result.message, 'error');
                this.displayFormErrors(form, result.errors);
            }
        } catch (error) {
            console.error('=== AJAX FORM ERROR ===');
            console.error('Error type:', error.constructor.name);
            console.error('Error message:', error.message);
            console.error('Error stack:', error.stack);
            window.showToast('Terjadi kesalahan: ' + error.message, 'error');
        } finally {
            console.log('=== CLEANUP PHASE ===');
            if (submitBtn && originalText) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                console.log('Button state restored');
            }
            console.log('=== AJAX FORM SUBMISSION END ===\n');
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
            const basePath = window.basePath || '';
            const response = await fetch(basePath + '/karyawan/marketplace', {
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
                window.showToast('Produk ditambahkan ke keranjang', 'success');
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
            window.showToast(error.message || 'Gagal menambahkan ke keranjang', 'error');
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
            const basePath = window.basePath || '';
            const response = await fetch(basePath + '/api/realtime-status');
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
    
    showCredentials(credentials) {
        document.getElementById('cred-username').textContent = credentials.username;
        document.getElementById('cred-code').textContent = credentials.employee_code;
        document.getElementById('cred-password').textContent = credentials.password;
        
        const modal = document.getElementById('credentials-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }
}

// Global function for closing credentials modal
function closeCredentialsModal() {
    const modal = document.getElementById('credentials-modal');
    if (modal) {
        modal.classList.add('hidden');
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