<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Base path from project root to use in asset URLs
$_base_path = rtrim(str_replace('\\', '/', str_replace(
    realpath($_SERVER['DOCUMENT_ROOT']),
    '',
    realpath(dirname(__DIR__))
)), '/');

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(): void {
    if (!isset($_POST['csrf_token']) || !hash_equals(
        $_SESSION['csrf_token'] ?? '',
        $_POST['csrf_token']
    )) {
        http_response_code(403);
        die('CSRF token mismatch');
    }
}

function flash(string $type, string $msg): void {
    $_SESSION['flash'][$type] = $msg;
}
