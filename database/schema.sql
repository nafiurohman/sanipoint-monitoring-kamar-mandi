-- SANIPOINT Database Schema
CREATE DATABASE IF NOT EXISTS sanipoint_db;
USE sanipoint_db;

-- Users table (Admin & Karyawan)
CREATE TABLE users (
    id VARCHAR(36) PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    role ENUM('admin', 'karyawan') NOT NULL,
    employee_code VARCHAR(20) UNIQUE,
    pin VARCHAR(255) NULL,
    pin_created_at TIMESTAMP NULL,
    last_password_change TIMESTAMP NULL,
    theme ENUM('light', 'dark', 'system') DEFAULT 'system',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bathrooms table
CREATE TABLE bathrooms (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    max_visitors INT DEFAULT 10,
    current_visitors INT DEFAULT 0,
    status ENUM('available', 'needs_cleaning', 'being_cleaned', 'maintenance') DEFAULT 'available',
    last_cleaned TIMESTAMP NULL,
    last_cleaned_by VARCHAR(36),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (last_cleaned_by) REFERENCES users(id)
);

-- Sensors table
CREATE TABLE sensors (
    id VARCHAR(36) PRIMARY KEY,
    bathroom_id VARCHAR(36) NOT NULL,
    sensor_type ENUM('mq135', 'ir', 'rfid') NOT NULL,
    sensor_code VARCHAR(50) UNIQUE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bathroom_id) REFERENCES bathrooms(id)
);

-- Sensor logs table
CREATE TABLE sensor_logs (
    id VARCHAR(36) PRIMARY KEY,
    sensor_id VARCHAR(36) NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    unit VARCHAR(20),
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sensor_id) REFERENCES sensors(id)
);

-- RFID logs table
CREATE TABLE rfid_logs (
    id VARCHAR(36) PRIMARY KEY,
    bathroom_id VARCHAR(36) NOT NULL,
    user_id VARCHAR(36) NOT NULL,
    action ENUM('start_cleaning', 'finish_cleaning') NOT NULL,
    rfid_code VARCHAR(50) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bathroom_id) REFERENCES bathrooms(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Visitor counter table
CREATE TABLE visitor_counter (
    id VARCHAR(36) PRIMARY KEY,
    bathroom_id VARCHAR(36) NOT NULL,
    count_in INT DEFAULT 0,
    count_out INT DEFAULT 0,
    current_occupancy INT DEFAULT 0,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bathroom_id) REFERENCES bathrooms(id)
);

-- Cleaning logs table
CREATE TABLE cleaning_logs (
    id VARCHAR(36) PRIMARY KEY,
    bathroom_id VARCHAR(36) NOT NULL,
    user_id VARCHAR(36) NOT NULL,
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP NULL,
    duration_minutes INT NULL,
    points_earned INT DEFAULT 0,
    status ENUM('in_progress', 'completed', 'cancelled') DEFAULT 'in_progress',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bathroom_id) REFERENCES bathrooms(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Points table
CREATE TABLE points (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    current_balance INT DEFAULT 0,
    total_earned INT DEFAULT 0,
    total_spent INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Point transactions table
CREATE TABLE point_transactions (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    transaction_type ENUM('earned', 'spent', 'transfer_in', 'transfer_out') NOT NULL,
    amount INT NOT NULL,
    balance_after INT NOT NULL,
    reference_type ENUM('cleaning', 'purchase', 'transfer') NOT NULL,
    reference_id VARCHAR(36),
    from_user_id VARCHAR(36) NULL,
    to_user_id VARCHAR(36) NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (from_user_id) REFERENCES users(id),
    FOREIGN KEY (to_user_id) REFERENCES users(id)
);

-- Product categories table
CREATE TABLE product_categories (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id VARCHAR(36) PRIMARY KEY,
    category_id VARCHAR(36) NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price_points INT NOT NULL,
    stock INT DEFAULT 0,
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES product_categories(id)
);

-- Orders table
CREATE TABLE orders (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_points INT NOT NULL,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    qr_code VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order items table
CREATE TABLE order_items (
    id VARCHAR(36) PRIMARY KEY,
    order_id VARCHAR(36) NOT NULL,
    product_id VARCHAR(36) NOT NULL,
    quantity INT NOT NULL,
    points_per_item INT NOT NULL,
    total_points INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- QR codes table
CREATE TABLE qr_codes (
    id VARCHAR(36) PRIMARY KEY,
    order_id VARCHAR(36) NOT NULL,
    qr_code VARCHAR(255) UNIQUE NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Notifications table
CREATE TABLE notifications (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36),
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'success', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Activity logs table
CREATE TABLE activity_logs (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36),
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id VARCHAR(36),
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- System settings table
CREATE TABLE system_settings (
    id VARCHAR(36) PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user
INSERT INTO users (id, username, password, full_name, role, employee_code) VALUES 
('550e8400-e29b-41d4-a716-446655440000', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', 'ADM001');

-- Insert demo karyawan user with PIN
INSERT INTO users (id, username, password, full_name, role, employee_code, email, phone, pin, pin_created_at) VALUES 
('550e8400-e29b-41d4-a716-446655440001', 'karyawan1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Santoso', 'karyawan', 'EMP001', 'budi@example.com', '081234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW());

-- Insert default system settings
INSERT INTO system_settings (id, setting_key, setting_value, description) VALUES
('550e8400-e29b-41d4-a716-446655440010', 'points_per_cleaning', '10', 'Points earned per cleaning session'),
('550e8400-e29b-41d4-a716-446655440011', 'max_visitors_default', '10', 'Default maximum visitors before cleaning required'),
('550e8400-e29b-41d4-a716-446655440012', 'cleaning_timeout_minutes', '30', 'Maximum time allowed for cleaning session');

-- Insert sample product categories
INSERT INTO product_categories (id, name, description) VALUES
('550e8400-e29b-41d4-a716-446655440020', 'Makanan', 'Produk makanan dan snack'),
('550e8400-e29b-41d4-a716-446655440021', 'Minuman', 'Minuman segar dan kemasan'),
('550e8400-e29b-41d4-a716-446655440022', 'Kebutuhan Sehari-hari', 'Produk kebutuhan harian');

-- Insert sample products
INSERT INTO products (id, category_id, name, description, price_points, stock) VALUES
('550e8400-e29b-41d4-a716-446655440030', '550e8400-e29b-41d4-a716-446655440020', 'Indomie Goreng', 'Mie instan rasa ayam bawang', 5, 100),
('550e8400-e29b-41d4-a716-446655440031', '550e8400-e29b-41d4-a716-446655440020', 'Chitato', 'Keripik kentang rasa sapi panggang', 8, 50),
('550e8400-e29b-41d4-a716-446655440032', '550e8400-e29b-41d4-a716-446655440021', 'Aqua 600ml', 'Air mineral kemasan', 3, 200),
('550e8400-e29b-41d4-a716-446655440033', '550e8400-e29b-41d4-a716-446655440021', 'Teh Botol Sosro', 'Minuman teh manis', 4, 150),
('550e8400-e29b-41d4-a716-446655440034', '550e8400-e29b-41d4-a716-446655440022', 'Sabun Mandi', 'Sabun mandi cair', 15, 30);

-- Initialize points for demo karyawan
INSERT INTO points (id, user_id, current_balance, total_earned, total_spent) VALUES
('550e8400-e29b-41d4-a716-446655440040', '550e8400-e29b-41d4-a716-446655440001', 50, 50, 0);