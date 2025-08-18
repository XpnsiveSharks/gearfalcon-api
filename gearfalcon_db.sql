CREATE TABLE users(
    id CHAR(36) PRIMARY KEY, -- UUIDs are 36 characters including hyphens
    user_role ENUM('Customer', 'Technician', 'Admin') NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARHCAR(50),
    phone VARHCAR(50),
    password_hash(255) NOT NULL,
    address TEXT,
    profile_picture VARCHAR(255),
    created_at,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);