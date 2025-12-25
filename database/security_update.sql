-- Add PIN and security fields to users table
ALTER TABLE users ADD COLUMN pin VARCHAR(6) NULL AFTER password;
ALTER TABLE users ADD COLUMN pin_created_at TIMESTAMP NULL AFTER pin;
ALTER TABLE users ADD COLUMN last_password_change TIMESTAMP NULL AFTER pin_created_at;

-- Add bathroom monitoring permissions
ALTER TABLE users ADD COLUMN can_monitor_bathroom BOOLEAN DEFAULT TRUE AFTER is_active;