-- Existing inventory custodian accounts become view-only Users.
-- Run once against inventory_hq when upgrading an older installation.
UPDATE users SET role = 'user' WHERE role = 'inventory_custodian';
ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin') NOT NULL;
