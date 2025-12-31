-- IoT Integration Migration for SANIPOINT
-- Add this to your existing database

-- Add IoT-specific tables that integrate with existing schema

-- RFID Cards table for managing registered cards
CREATE TABLE IF NOT EXISTS rfid_cards (
    id VARCHAR(36) PRIMARY KEY,
    uid VARCHAR(20) UNIQUE NOT NULL,
    user_id VARCHAR(36) NULL,
    nama_pemilik VARCHAR(100),
    peran ENUM('Admin', 'Karyawan', 'Guest') DEFAULT 'Guest',
    status ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Usage logs for IoT events
CREATE TABLE IF NOT EXISTS usage_logs (
    id VARCHAR(36) PRIMARY KEY,
    bathroom_id VARCHAR(36) NOT NULL,
    uid_pengakses VARCHAR(20),
    user_id VARCHAR(36) NULL,
    keterangan VARCHAR(255),
    action_type ENUM('enter', 'exit', 'admin_reset', 'start_cleaning', 'finish_cleaning') NOT NULL,
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bathroom_id) REFERENCES bathrooms(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert default bathrooms if not exists
INSERT IGNORE INTO bathrooms (id, name, location, max_visitors) VALUES 
('toilet-1-uuid-000000000000000000000001', 'Toilet 1', 'Lantai 1 - Kiri', 5),
('toilet-2-uuid-000000000000000000000002', 'Toilet 2', 'Lantai 1 - Kanan', 5);

-- Insert sensors for each bathroom
INSERT IGNORE INTO sensors (id, bathroom_id, sensor_type, sensor_code) VALUES
-- Toilet 1 sensors
('sensor-ir-1-00000000000000000000001', 'toilet-1-uuid-000000000000000000000001', 'ir', 'IR_T1'),
('sensor-mq-1-00000000000000000000001', 'toilet-1-uuid-000000000000000000000001', 'mq135', 'MQ135_T1'),
('sensor-rfid-1-0000000000000000000001', 'toilet-1-uuid-000000000000000000000001', 'rfid', 'RFID_T1'),

-- Toilet 2 sensors  
('sensor-ir-2-00000000000000000000002', 'toilet-2-uuid-000000000000000000000002', 'ir', 'IR_T2'),
('sensor-mq-2-00000000000000000000002', 'toilet-2-uuid-000000000000000000000002', 'mq135', 'MQ135_T2'),
('sensor-rfid-2-0000000000000000000002', 'toilet-2-uuid-000000000000000000000002', 'rfid', 'RFID_T2');

-- Register admin RFID cards
INSERT IGNORE INTO rfid_cards (id, uid, nama_pemilik, peran, status) VALUES
('rfid-admin-1-000000000000000000001', 'B490FBB0', 'Admin Card 1', 'Admin', 'Aktif'),
('rfid-admin-2-000000000000000000002', 'C6861BFF', 'Admin Card 2', 'Admin', 'Aktif');

-- Link existing karyawan with RFID card (using employee_code as UID for now)
UPDATE users SET employee_code = 'EMP001' WHERE username = 'karyawan1';

INSERT IGNORE INTO rfid_cards (id, uid, user_id, nama_pemilik, peran, status) 
SELECT 
    CONCAT('rfid-emp-', id), 
    employee_code, 
    id, 
    full_name, 
    'Karyawan', 
    'Aktif'
FROM users 
WHERE role = 'karyawan' AND employee_code IS NOT NULL;