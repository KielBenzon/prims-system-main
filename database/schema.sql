CREATE DATABASE IF NOT EXISTS `church`;

USE `church`;

CREATE TABLE IF NOT EXISTS `tusers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` enum('Admin', 'Parishioner') NOT NULL DEFAULT 'Admin',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tpriests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `last_name` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(255) NOT NULL,
    `middle_name` VARCHAR(255) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `date_of_birth` DATE NOT NULL,
    `phone_number` VARCHAR(255) NOT NULL,
    `email_address` VARCHAR(255) NOT NULL,
    `ordination_date` DATE NOT NULL,
    `image` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tdonations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `donor_name` VARCHAR(255) NOT NULL,
    `donor_email` VARCHAR(255) NOT NULL,
    `donor_phone` VARCHAR(255) NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `donation_date` DATE NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tmail` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `sender` VARCHAR(255) NOT NULL,
    `recipient` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `priority` enum('Very High', 'High', 'Normal', 'Low') NOT NULL DEFAULT 'Normal',
    `status` enum('Undelivered', 'Delivered') NOT NULL DEFAULT 'Undelivered',
    `date` DATE NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tdocuments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `document_type` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(255) NOT NULL,
    `file` VARCHAR(255) NOT NULL,
    `uploaded_by` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tcertificate_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `certificate_type` VARCHAR(255) NOT NULL UNIQUE,
    `description` VARCHAR(255) NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO `tcertificate_types` (`certificate_type`, `description`, `amount`) VALUES
('Baptismal Certificate', 'Baptismal Certificate', 100.00),
('Marriage Certificate', 'Marriage Certificate', 100.00),
('Death Certificate', 'Death Certificate', 100.00),
('Confirmation Certificate', 'Confirmation Certificate', 100.00);

CREATE TABLE IF NOT EXISTS `tcertificate_details` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `certificate_type` VARCHAR(255) NOT NULL,
    
    -- Baptismal Certificate
    `name_of_child` VARCHAR(255) NOT NULL,
    `date_of_birth` DATE NOT NULL,
    `place_of_birth` VARCHAR(255) NOT NULL,
    `baptism_schedule` DATE NOT NULL,
    `name_of_father` VARCHAR(255) NOT NULL,
    `name_of_mother` VARCHAR(255) NOT NULL,

    -- Marriage Certificate

    -- Bride Information
    `bride_name` VARCHAR(255) NOT NULL,
    `birthdate_bride` DATE NOT NULL,
    `age_bride` INT NOT NULL,
    `birthplace_bride` VARCHAR(255) NOT NULL,
    `citizenship_bride` VARCHAR(255) NOT NULL,
    `religion_bride` VARCHAR(255) NOT NULL,
    `residence_bride` VARCHAR(255) NOT NULL,
    `civil_status_bride` VARCHAR(255) NOT NULL,
    `name_of_father_bride` VARCHAR(255) NOT NULL,
    `name_of_mother_bride` VARCHAR(255) NOT NULL,

    -- Groom Information
    `name_of_groom` VARCHAR(255) NOT NULL,
    `birthdate_groom` DATE NOT NULL,
    `age_groom` INT NOT NULL,
    `birthplace_groom` VARCHAR(255) NOT NULL,
    `citizenship_groom` VARCHAR(255) NOT NULL,
    `religion_groom` VARCHAR(255) NOT NULL,
    `residence_groom` VARCHAR(255) NOT NULL,
    `civil_status_groom` VARCHAR(255) NOT NULL,
    `name_of_father_groom` VARCHAR(255) NOT NULL,
    `name_of_mother_groom` VARCHAR(255) NOT NULL,

    -- Death Certificate
    `first_name_death` VARCHAR(255) NOT NULL,
    `middle_name_death` VARCHAR(255) NOT NULL,
    `last_name_death` VARCHAR(255) NOT NULL,
    `date_of_birth_death` DATE NOT NULL,
    `date_of_death` DATE NOT NULL,
    `file_death` VARCHAR(255) NOT NULL,

    -- Confirmation Certificate
    `name_of_confirmand` VARCHAR(255) NOT NULL,
    `date_of_birth_confirmand` DATE NOT NULL,
    `date_of_confirmation` DATE NOT NULL,
    `file_confirmation` VARCHAR(255) NOT NULL,

    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`certificate_type`) REFERENCES `tcertificate_types`(`certificate_type`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `trequests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `requested_by` INT NOT NULL,
    `document_type` VARCHAR(255) NOT NULL,
    `approved_by` INT DEFAULT NULL,
    `status` VARCHAR(50) NOT NULL,
    `is_paid` ENUM('Paid', 'Unpaid') NOT NULL DEFAULT 'Unpaid',
    `is_deleted` BOOLEAN NOT NULL DEFAULT FALSE,
    `notes` TEXT DEFAULT NULL,
    `file` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tpayments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `payment_date` DATE NOT NULL,
    `payment_method` VARCHAR(255) NOT NULL,
    `payment_status` enum('Pending', 'Paid') NOT NULL DEFAULT 'Pending',
    `transaction_id` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`request_id`) REFERENCES `trequests`(`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tannouncements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `assigned_priest` INT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`assigned_priest`) REFERENCES `tpriests`(`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tnotifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `type` INT NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` BOOLEAN NOT NULL DEFAULT FALSE,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`type`) REFERENCES `tannouncements`(`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;