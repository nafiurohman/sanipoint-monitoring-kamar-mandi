// Admin CRUD Operations JavaScript - Enhanced Version
console.log('🛠️ Admin CRUD System Loading...');

class AdminCRUD {
    constructor() {
        console.log('🎨 AdminCRUD class initialized');
        this.initializeEventListeners();
    }

    initializeEventListeners() {
        // Employee management
        document.addEventListener('click', (e) => {
            if (e.target.matches('.btn-edit-employee')) {
                this.editEmployee(e.target.dataset.id);
            }
            if (e.target.matches('.btn-delete-employee')) {
                this.deleteEmployee(e.target.dataset.id, e.target.dataset.name);
            }
            if (e.target.matches('.btn-toggle-employee')) {
                this.toggleEmployeeStatus(e.target.dataset.id);
            }

            // Bathroom management
            if (e.target.matches('.btn-edit-bathroom')) {
                this.editBathroom(e.target.dataset.id);
            }
            if (e.target.matches('.btn-delete-bathroom')) {
                this.deleteBathroom(e.target.dataset.id, e.target.dataset.name);
            }

            // Product management
            if (e.target.matches('.btn-edit-product')) {
                this.editProduct(e.target.dataset.id);
            }
            if (e.target.matches('.btn-delete-product')) {
                this.deleteProduct(e.target.dataset.id, e.target.dataset.name);
            }
            if (e.target.matches('.btn-toggle-product')) {
                this.toggleProductStatus(e.target.dataset.id);
            }
        });

        // Form submissions
        const employeeForm = document.getElementById('employeeForm');
        if (employeeForm) {
            employeeForm.addEventListener('submit', (e) => this.handleEmployeeForm(e));
        }

        const bathroomForm = document.getElementById('bathroomForm');
        if (bathroomForm) {
            bathroomForm.addEventListener('submit', (e) => this.handleBathroomForm(e));
        }

        const productForm = document.getElementById('productForm');
        if (productForm) {
            productForm.addEventListener('submit', (e) => this.handleProductForm(e));
        }
    }

    // Employee Management
    async editEmployee(id) {
        try {
            const basePath = window.basePath || '';
            const response = await this.makeRequest(basePath + '/admin/karyawan', {
                action: 'get_employee',
                id: id,
                csrf_token: this.getCSRFToken()
            });

            if (response.success) {
                this.populateEmployeeForm(response.employee);
                this.showModal('employeeModal');
            } else {
                window.showToast(response.message, 'error');
            }
        } catch (error) {
            window.showToast('Error loading employee data', 'error');
        }
    }

    async deleteEmployee(id, name = 'karyawan ini') {
        const confirmed = await window.showConfirm({
            title: 'Hapus Karyawan',
            message: `Apakah Anda yakin ingin menghapus ${name}? Tindakan ini tidak dapat dibatalkan.`,
            type: 'danger',
            confirmText: 'Ya, Hapus'
        });
        
        if (!confirmed) return;

        try {
            const basePath = window.basePath || '';
            const response = await this.makeRequest(basePath + '/admin/karyawan', {
                action: 'delete',
                id: id,
                csrf_token: this.getCSRFToken()
            });

            if (response.success) {
                window.showToast(response.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                window.showToast(response.message, 'error');
            }
        } catch (error) {
            window.showToast('Error deleting employee', 'error');
        }
    }

    async toggleEmployeeStatus(id) {
        try {
            const basePath = window.basePath || '';
            const response = await this.makeRequest(basePath + '/admin/karyawan', {
                action: 'toggle_status',
                id: id,
                csrf_token: this.getCSRFToken()
            });

            if (response.success) {
                window.showToast(response.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                window.showToast(response.message, 'error');
            }
        } catch (error) {
            window.showToast('Error updating employee status', 'error');
        }
    }

    // Bathroom Management
    async editBathroom(id) {
        try {
            const basePath = window.basePath || '';
            const response = await this.makeRequest(basePath + '/admin/kamar-mandi', {
                action: 'get_bathroom',
                id: id,
                csrf_token: this.getCSRFToken()
            });

            if (response.success) {
                this.populateBathroomForm(response.bathroom);
                this.showModal('bathroomModal');
            } else {
                window.showToast(response.message, 'error');
            }
        } catch (error) {
            window.showToast('Error loading bathroom data', 'error');
        }
    }

    async deleteBathroom(id, name = 'kamar mandi ini') {
        const confirmed = await window.showConfirm({
            title: 'Hapus Kamar Mandi',
            message: `Apakah Anda yakin ingin menghapus ${name}? Tindakan ini tidak dapat dibatalkan.`,
            type: 'danger',
            confirmText: 'Ya, Hapus'
        });
        
        if (!confirmed) return;

        try {
            const basePath = window.basePath || '';
            const response = await this.makeRequest(basePath + '/admin/kamar-mandi', {
                action: 'delete',
                id: id,
                csrf_token: this.getCSRFToken()
            });

            if (response.success) {
                window.showToast(response.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                window.showToast(response.message, 'error');
            }
        } catch (error) {
            window.showToast('Error deleting bathroom', 'error');
        }
    }

    // Product Management
    async editProduct(id) {
        try {
            const basePath = window.basePath || '';
            const response = await this.makeRequest(basePath + '/admin/produk', {
                action: 'get_product',
                id: id,
                csrf_token: this.getCSRFToken()
            });

            if (response.success) {
                this.populateProductForm(response.product);
                this.showModal('productModal');
            } else {
                window.showToast(response.message, 'error');
            }
        } catch (error) {
            window.showToast('Error loading product data', 'error');
        }
    }

    async deleteProduct(id, name = 'produk ini') {
        const confirmed = await window.showConfirm({
            title: 'Hapus Produk',
            message: `Apakah Anda yakin ingin menghapus ${name}? Tindakan ini tidak dapat dibatalkan.`,
            type: 'danger',
            confirmText: 'Ya, Hapus'
        });
        
        if (!confirmed) return;

        try {
            const basePath = window.basePath || '';
            const response = await this.makeRequest(basePath + '/admin/produk', {
                action: 'delete',
                id: id,
                csrf_token: this.getCSRFToken()
            });

            if (response.success) {
                window.showToast(response.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                window.showToast(response.message, 'error');
            }
        } catch (error) {
            window.showToast('Error deleting product', 'error');
        }
    }

    async toggleProductStatus(id) {
        try {
            const basePath = window.basePath || '';
            const response = await this.makeRequest(basePath + '/admin/produk', {
                action: 'toggle_status',
                id: id,
                csrf_token: this.getCSRFToken()
            });

            if (response.success) {
                window.showToast(response.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                window.showToast(response.message, 'error');
            }
        } catch (error) {
            window.showToast('Error updating product status', 'error');
        }
    }

    // Form Handlers
    async handleEmployeeForm(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const isEdit = formData.get('id') !== '';
        
        formData.append('action', isEdit ? 'update' : 'create');
        formData.append('csrf_token', this.getCSRFToken());

        try {
            const basePath = window.basePath || '';
            const response = await this.makeRequest(basePath + '/admin/karyawan', formData);
            
            if (response.success) {
                window.showToast(response.message, 'success');
                this.hideModal('employeeModal');
                setTimeout(() => location.reload(), 1000);
            } else {
                window.showToast(response.message, 'error');
            }
        } catch (error) {
            window.showToast('Error saving employee', 'error');
        }
    }

    async handleBathroomForm(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const isEdit = formData.get('id') !== '';
        
        formData.append('action', isEdit ? 'update' : 'create');
        formData.append('csrf_token', this.getCSRFToken());

        try {
            const basePath = window.basePath || '';
            const response = await this.makeRequest(basePath + '/admin/kamar-mandi', formData);
            
            if (response.success) {
                window.showToast(response.message, 'success');
                this.hideModal('bathroomModal');
                setTimeout(() => location.reload(), 1000);
            } else {
                window.showToast(response.message, 'error');
            }
        } catch (error) {
            window.showToast('Error saving bathroom', 'error');
        }
    }

    async handleProductForm(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const isEdit = formData.get('id') !== '';
        
        formData.append('action', isEdit ? 'update' : 'create');
        formData.append('csrf_token', this.getCSRFToken());

        try {
            const basePath = window.basePath || '';
            const response = await this.makeRequest(basePath + '/admin/produk', formData);
            
            if (response.success) {
                window.showToast(response.message, 'success');
                this.hideModal('productModal');
                setTimeout(() => location.reload(), 1000);
            } else {
                window.showToast(response.message, 'error');
            }
        } catch (error) {
            window.showToast('Error saving product', 'error');
        }
    }

    // Form Population
    populateEmployeeForm(employee) {
        document.getElementById('employee_id').value = employee.id;
        document.getElementById('employee_full_name').value = employee.full_name;
        document.getElementById('employee_code').value = employee.employee_code;
        document.getElementById('employee_email').value = employee.email || '';
        document.getElementById('employee_phone').value = employee.phone || '';
        document.getElementById('employeeModalTitle').textContent = 'Edit Employee';
    }

    populateBathroomForm(bathroom) {
        document.getElementById('bathroom_id').value = bathroom.id;
        document.getElementById('bathroom_name').value = bathroom.name;
        document.getElementById('bathroom_location').value = bathroom.location;
        document.getElementById('bathroom_max_visitors').value = bathroom.max_visitors;
        document.getElementById('bathroomModalTitle').textContent = 'Edit Bathroom';
    }

    populateProductForm(product) {
        document.getElementById('product_id').value = product.id;
        document.getElementById('product_name').value = product.name;
        document.getElementById('product_category_id').value = product.category_id;
        document.getElementById('product_description').value = product.description || '';
        document.getElementById('product_price_points').value = product.price_points;
        document.getElementById('product_stock').value = product.stock;
        document.getElementById('product_image_url').value = product.image_url || '';
        document.getElementById('productModalTitle').textContent = 'Edit Product';
    }

    // Utility Methods
    async makeRequest(url, data) {
        console.log('🚀 Making request to:', url);
        console.log('📝 Request data:', data);
        
        const options = {
            method: 'POST',
            body: data instanceof FormData ? data : new URLSearchParams(data)
        };

        try {
            const response = await fetch(url, options);
            const result = await response.json();
            console.log('✅ Response received:', result);
            return result;
        } catch (error) {
            console.error('❌ Request failed:', error);
            throw error;
        }
    }

    getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    hideModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            // Reset form
            const form = modal.querySelector('form');
            if (form) form.reset();
        }
    }

    // New Item Functions
    newEmployee() {
        document.getElementById('employeeForm').reset();
        document.getElementById('employee_id').value = '';
        document.getElementById('employeeModalTitle').textContent = 'Add New Employee';
        this.showModal('employeeModal');
    }

    newBathroom() {
        document.getElementById('bathroomForm').reset();
        document.getElementById('bathroom_id').value = '';
        document.getElementById('bathroomModalTitle').textContent = 'Add New Bathroom';
        this.showModal('bathroomModal');
    }

    newProduct() {
        document.getElementById('productForm').reset();
        document.getElementById('product_id').value = '';
        document.getElementById('productModalTitle').textContent = 'Add New Product';
        this.showModal('productModal');
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.adminCRUD = new AdminCRUD();
});

// Global functions for button clicks
function newEmployee() {
    window.adminCRUD.newEmployee();
}

function newBathroom() {
    window.adminCRUD.newBathroom();
}

function newProduct() {
    window.adminCRUD.newProduct();
}

function editProduct(id) {
    window.adminCRUD.editProduct(id);
}

function toggleProduct(id) {
    window.adminCRUD.toggleProductStatus(id);
}

function editEmployee(id) {
    window.adminCRUD.editEmployee(id);
}

function toggleEmployee(id) {
    window.adminCRUD.toggleEmployeeStatus(id);
}

function editBathroom(id) {
    window.adminCRUD.editBathroom(id);
}

function closeModal(modalId) {
    window.adminCRUD.hideModal(modalId);
}