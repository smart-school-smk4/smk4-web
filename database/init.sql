-- Database initialization script for Docker
-- This file will be automatically executed when MySQL container starts

USE db_presensi;

-- Grant all privileges to laravel_user
GRANT ALL PRIVILEGES ON db_presensi.* TO 'laravel_user'@'%';
FLUSH PRIVILEGES;

-- You can add any initial data or additional setup here
-- Example:
-- INSERT INTO users (name, email, password) VALUES ('Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');