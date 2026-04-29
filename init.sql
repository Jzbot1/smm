CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `balance` DECIMAL(10,2) DEFAULT 0.00,
  `api_key` VARCHAR(100) NULL UNIQUE,
  `role` ENUM('user', 'admin') DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `sort_order` INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `api_service_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `api_rate` DECIMAL(10,4) NOT NULL,
  `selling_price` DECIMAL(10,4) NOT NULL,
  `min` INT NOT NULL,
  `max` INT NOT NULL,
  `description` TEXT,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`)
);

CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `service_id` INT NOT NULL,
  `api_order_id` INT NULL,
  `link` VARCHAR(1000) NOT NULL,
  `quantity` INT NOT NULL,
  `charge` DECIMAL(10,4) NOT NULL,
  `api_charge` DECIMAL(10,4) NOT NULL,
  `status` VARCHAR(50) DEFAULT 'Pending',
  `remains` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
  FOREIGN KEY (`service_id`) REFERENCES `services`(`id`)
);

CREATE TABLE IF NOT EXISTS `transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `type` ENUM('credit', 'debit') NOT NULL,
  `description` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);

CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT
);

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `tickets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('Open', 'Pending', 'Closed') DEFAULT 'Open',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);

CREATE TABLE IF NOT EXISTS `ticket_replies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);

-- Insert default admin (password is 'admin123')
INSERT IGNORE INTO `users` (`name`, `email`, `password`, `role`, `api_key`) VALUES 
('Admin', 'admin@example.com', '$2y$10$e/GkKpxn9t1bT9hE8K/M7u4R9uW9eK5v9k3f5v9k3f5v9k3f5v9k3', 'admin', 'ADMIN_MASTER_KEY_123');

-- Insert default settings
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('profit_percent', '5'),
('smm_api_url', 'https://justanotherpanel.com/api/v2'),
('smm_api_key', 'YOUR_SMM_API_KEY'),
('ekupi_key', ''),
('ekupi_base_url', 'https://ekupi.in'),
('ekupi_redirect_url', 'http://mirakiboosting.com.jzstore.in/payment_callback.php'),
('ekupi_webhook_token', ''),
('maintenance_mode', '0'),
('announcement_text', 'Welcome to our premium SMM panel! High quality services at the best price.'),
('announcement_enabled', '1');
