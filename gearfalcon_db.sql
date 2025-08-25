CREATE TABLE users (
    id              CHAR(36) PRIMARY KEY, -- UUID
    role            VARCHAR(20) NOT NULL CHECK (role IN ('Customer', 'Technician', 'Admin')),
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,

    -- Profile fields
    first_name      VARCHAR(100) NOT NULL,
    last_name       VARCHAR(100) NOT NULL,
    middle_name     VARCHAR(100) NULL,
    avatar_url      TEXT NULL,

    -- ContactInfo fields
    phone           VARCHAR(20) NULL,

    -- Credentials fields
    email           VARCHAR(50) UNIQUE,
    password_hash   VARCHAR(255) NOT NULL, -- store only hashed password

    -- Address fields
    house_number     VARCHAR(20) NOT NULL,
    street           VARCHAR(100) NOT NULL,
    barangay         VARCHAR(100) NOT NULL,
    city             VARCHAR(50) NOT NULL,
    province         VARCHAR(100) NOT NULL,
    region           VARCHAR(100) NOT NULL,
    postal_code      VARCHAR(20) NOT NULL,


    -- Metadata
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP NULL DEFAULT NULL
);
