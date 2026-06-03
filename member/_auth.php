<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Compute base path before any redirect
$_member_base = rtrim(str_replace('\\', '/', str_replace(
    realpath($_SERVER['DOCUMENT_ROOT']),
    '',
    realpath(dirname(__DIR__))
)), '/');

if (empty($_SESSION['member_id'])) {
    header('Location: ' . $_member_base . '/login');
    exit;
}

function member_csrf_token(): string {
    if (empty($_SESSION['member_csrf'])) {
        $_SESSION['member_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['member_csrf'];
}

function member_csrf_verify(): void {
    if (!isset($_POST['csrf_token']) || !hash_equals(
        $_SESSION['member_csrf'] ?? '',
        $_POST['csrf_token']
    )) {
        http_response_code(403);
        die('Invalid token');
    }
}

function member_flash(string $type, string $msg): void {
    $_SESSION['member_flash'][$type] = $msg;
}
