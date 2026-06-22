<?php
require_once('auth.php');
require_once('../config/db.php');

db()->exec("
CREATE TABLE IF NOT EXISTS `admin_activity_log` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `admin_id`   INT NOT NULL,
  `admin_name` VARCHAR(100) NOT NULL,
  `action`     ENUM('create','update','delete') NOT NULL,
  `entity`     VARCHAR(50) NOT NULL,
  `entity_id`  INT DEFAULT NULL,
  `label`      VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_admin_id`   (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo 'OK — ตารางพร้อมใช้งาน <a href="logs">ไปที่ Activity Log</a>';
