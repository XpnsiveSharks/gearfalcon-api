-- USERS (step 1: register with email + password)
CREATE TABLE users (
  id CHAR(36) PRIMARY KEY, -- UUID
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL, -- hashed password
  role ENUM('admin','customer','technician') NOT NULL DEFAULT 'customer',
  name VARCHAR(255) NULL, -- optional at first, can be filled later
  phone VARCHAR(50) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL
);

-- CUSTOMERS (step 2: complete profile after login)
CREATE TABLE customers (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id CHAR(36) UNIQUE NOT NULL, -- FK → users.id
  company_name VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- CUSTOMER ADDRESSES
CREATE TABLE customer_addresses (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  customer_id BIGINT NOT NULL, -- FK → customers.id
  house_number VARCHAR(20) NOT NULL,
  street VARCHAR(100) NOT NULL,
  barangay VARCHAR(100) NOT NULL,
  city VARCHAR(50) NOT NULL,
  province VARCHAR(100) NOT NULL,
  region VARCHAR(100) NOT NULL,
  postal_code VARCHAR(20) NOT NULL,
  is_primary BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- TECHNICIANS
CREATE TABLE technicians (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id CHAR(36) UNIQUE NOT NULL, -- FK → users.id
  specialization VARCHAR(255),
  certification TEXT,
  experience_years INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- SKILLS
CREATE TABLE skills (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL
);

-- TECHNICIAN SKILLS (many-to-many)
CREATE TABLE technician_skills (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  technician_id BIGINT NOT NULL, -- FK → technicians.id
  skill_id BIGINT NOT NULL, -- FK → skills.id
  proficiency ENUM('beginner','intermediate','expert') DEFAULT 'intermediate',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  FOREIGN KEY (technician_id) REFERENCES technicians(id) ON DELETE CASCADE,
  FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE,
  UNIQUE (technician_id, skill_id) -- prevent duplicates
);

-- SERVICE CATEGORIES
CREATE TABLE service_categories (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) UNIQUE NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL
);

-- SERVICES
CREATE TABLE services (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  category_id BIGINT NOT NULL, -- FK → service_categories.id
  name VARCHAR(255) NOT NULL,
  description TEXT,
  base_price DECIMAL(10,2) DEFAULT 0.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  FOREIGN KEY (category_id) REFERENCES service_categories(id) ON DELETE CASCADE
);

-- CARTS
CREATE TABLE carts (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  customer_id BIGINT NOT NULL, -- FK → customers.id
  status ENUM('active','checked_out','abandoned') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- CART ITEMS
CREATE TABLE cart_items (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  cart_id BIGINT NOT NULL, -- FK → carts.id
  service_id BIGINT NOT NULL, -- FK → services.id
  quantity INT DEFAULT 1,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
  FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- JOBS
CREATE TABLE jobs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  customer_id BIGINT NOT NULL, -- FK → customers.id
  customer_address_id BIGINT NOT NULL, -- FK → customer_addresses.id
  service_id BIGINT NOT NULL, -- FK → services.id
  cart_id BIGINT NULL, -- FK → carts.id
  status ENUM('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  scheduled_date DATE,
  completed_date DATE,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
  FOREIGN KEY (customer_address_id) REFERENCES customer_addresses(id) ON DELETE CASCADE,
  FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
  FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE SET NULL
);

-- JOB ASSIGNMENTS (jobs ↔ technicians)
CREATE TABLE job_assignments (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  job_id BIGINT NOT NULL, -- FK → jobs.id
  technician_id BIGINT NOT NULL, -- FK → technicians.id
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
  FOREIGN KEY (technician_id) REFERENCES technicians(id) ON DELETE CASCADE,
  UNIQUE (job_id, technician_id) -- prevent duplicate assignments
);

-- QUOTES
CREATE TABLE quotes (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  customer_id BIGINT NOT NULL,
  customer_address_id BIGINT NOT NULL,
  cart_id BIGINT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  status ENUM('draft','sent','accepted','declined','expired') DEFAULT 'draft',
  valid_until DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
  FOREIGN KEY (customer_address_id) REFERENCES customer_addresses(id) ON DELETE CASCADE,
  FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE SET NULL
);
