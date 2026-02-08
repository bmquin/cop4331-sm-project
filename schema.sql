CREATE DATABASE IF NOT EXISTS contact_manager;

USE contact_manager;

-- Users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(25) NOT NULL UNIQUE;
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL, --UNIQUE !!! lol You and user_123 share the same password
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contacts (per user)
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15), --googled max size of phone numbers
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_contacts_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

CREATE INDEX idx_contacts_user ON contacts (user_id);

CREATE INDEX idx_contacts_search ON contacts (first_name, last_name, phone, email);
