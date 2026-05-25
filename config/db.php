<?php
$_env = parse_ini_file(__DIR__ . '/../.env');
define('DB_HOST',    $_env['DB_HOST']    ?? 'localhost');
define('DB_NAME',    $_env['DB_NAME']    ?? '');
define('DB_USER',    $_env['DB_USER']    ?? '');
define('DB_PASS',    $_env['DB_PASS']    ?? '');
define('DB_CHARSET', $_env['DB_CHARSET'] ?? 'utf8mb4');

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE  => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES    => false,
                ]
            );
        } catch (PDOException $e) {
            error_log('DB connection failed: ' . $e->getMessage());
            die('ไม่สามารถเชื่อมต่อฐานข้อมูลได้ — กรุณาตรวจสอบ config/db.php');
        }
    }
    return $pdo;
}
