// Simple AJAX form handler - Enhanced Version
document.addEventListener('DOMContentLoaded', function() {
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Handle form submissions
    document.addEventListener('submit', function(e) {
        if (e.target.classList.contains('ajax-form')) {
            e.preventDefault();
            handleFormSubmit(e.target);
        }
    });
    
    // Handle modal triggers
    document.addEventListener('click', function(e) {
        if (e.target.hasAttribute('data-modal')) {
            e.preventDefault();
            openModal(e.target.getAttribute('data-modal'));
        }
        
        if (e.target.classList.contains('modal-close')) {
            closeModal();
        }
    });
    
    async function handleFormSubmit(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn?.innerHTML;
        
        try {
            // Show loading
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
            }
            
            // Prepare form data
            const formData = new FormData(form);
            formData.append('csrf_token', csrfToken);
            
            // Submit to current URL
            const response = await fetch(window.location.pathname, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                window.showToast(result.message, 'success');
                
                // Show credentials if available
                if (result.credentials) {
                    showCredentials(result.credentials);
                }
                
                // Reload if needed
                if (form.hasAttribute('data-reload')) {
                    setTimeout(() => location.reload(), 2000);
                }
                
                closeModal();
                form.reset();
            } else {
                window.showToast(result.message, 'error');
            }
            
        } catch (error) {
            window.showToast('Terjadi kesalahan: ' + error.message, 'error');
            console.error('Form error:', error);
        } finally {
            // Reset button
            if (submitBtn && originalText) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
    }
    
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
        }
    }
    
    function closeModal() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.add('hidden');
        });
    }
    
    function showCredentials(credentials) {
        document.getElementById('cred-username').textContent = credentials.username;
        document.getElementById('cred-code').textContent = credentials.employee_code;
        document.getElementById('cred-password').textContent = credentials.password;
        
        openModal('credentials-modal');
    }
    
    // Global function for closing credentials modal
    window.closeCredentialsModal = function() {
        closeModal();
    };
});