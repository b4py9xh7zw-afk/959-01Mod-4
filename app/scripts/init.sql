-- Database initialization script
-- This script creates the necessary tables and seeds initial data

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create licenses table
CREATE TABLE IF NOT EXISTS licenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    license_key VARCHAR(100) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    expires_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_license_key (license_key),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create device_bindings table for license-device fingerprint binding
CREATE TABLE IF NOT EXISTS device_bindings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    license_id INT NOT NULL,
    device_fingerprint VARCHAR(255) NOT NULL,
    device_info TEXT NULL,
    last_activated_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (license_id) REFERENCES licenses(id) ON DELETE CASCADE,
    UNIQUE KEY uk_license_fingerprint (license_id, device_fingerprint),
    INDEX idx_license_id (license_id),
    INDEX idx_device_fingerprint (device_fingerprint)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create device_fingerprint_approvals table for fingerprint change requests
CREATE TABLE IF NOT EXISTS device_fingerprint_approvals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    license_id INT NOT NULL,
    old_fingerprint VARCHAR(255) NOT NULL,
    new_fingerprint VARCHAR(255) NOT NULL,
    change_reason TEXT NOT NULL,
    screenshot_path VARCHAR(500) NULL,
    status ENUM('pending', 'risk_review', 'approved', 'rejected') DEFAULT 'pending',
    risk_level ENUM('low', 'medium', 'high') DEFAULT 'low',
    review_notes TEXT NULL,
    reviewer_id INT NULL,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (license_id) REFERENCES licenses(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_license_id (license_id),
    INDEX idx_status (status),
    INDEX idx_risk_level (risk_level),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create banned_devices table for disabled old fingerprints after approval
CREATE TABLE IF NOT EXISTS banned_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    license_id INT NOT NULL,
    device_fingerprint VARCHAR(255) NOT NULL,
    approval_id INT NULL,
    ban_reason TEXT NOT NULL,
    banned_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (license_id) REFERENCES licenses(id) ON DELETE CASCADE,
    FOREIGN KEY (approval_id) REFERENCES device_fingerprint_approvals(id) ON DELETE SET NULL,
    FOREIGN KEY (banned_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_license_fingerprint (license_id, device_fingerprint),
    INDEX idx_license_id (license_id),
    INDEX idx_device_fingerprint (device_fingerprint)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: User seeding is handled by PHP script (app/scripts/seed_users.php)
-- This ensures correct password hashing. Users will be created on first container startup.
-- Sample licenses will be created by app/scripts/seed_licenses.php after users are created.
