-- Add received_at column to orders table
ALTER TABLE orders ADD COLUMN received_at TIMESTAMP NULL AFTER status;