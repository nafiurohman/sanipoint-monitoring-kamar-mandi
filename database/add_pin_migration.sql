-- Migration to add PIN fields to users table
USE sanipoint_db;

-- Add PIN columns if they don't exist
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS pin VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS pin_created_at TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS last_password_change TIMESTAMP NULL;

-- Update demo karyawan with PIN (123456)
UPDATE users 
SET pin = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    pin_created_at = NOW() 
WHERE username = 'karyawan1';

-- Show updated structure
DESCRIBE users;