CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME NULL,
    INDEX idx_admin_email (email)
);

CREATE TABLE IF NOT EXISTS blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT NULL,
    content MEDIUMTEXT NOT NULL,
    featured_image VARCHAR(255) NULL,
    banner_image VARCHAR(255) NULL,
    template ENUM('standard','feature') NOT NULL DEFAULT 'standard',
    status ENUM('draft','published','scheduled') NOT NULL DEFAULT 'draft',
    publish_date DATETIME NULL,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    meta_keywords TEXT NULL,
    author_name VARCHAR(150) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    INDEX idx_status (status),
    INDEX idx_publish_date (publish_date),
    INDEX idx_not_deleted (is_deleted)
);

CREATE TABLE IF NOT EXISTS media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO admins (name, email, password_hash, created_at)
VALUES ('Administrator', 'admin@example.com', '$2y$10$F167LcvJWTaTAD.5AWANOugTQhzWIV.gUsCoJcMGHlulUxUc6kuq2', NOW());
