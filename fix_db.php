<?php
require_once 'config/database.php';

try {
    $db = Database::getInstance();
    
    echo "Updating database schema...<br>";
    
    // Add status column to users table if it doesn't exist
    $db->exec("ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `status` ENUM('Active', 'Blocked') DEFAULT 'Active'");
    echo "Successfully added 'status' column to users table.<br>";
    
    echo "<br><b>Database update complete!</b> You can now delete this file.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
