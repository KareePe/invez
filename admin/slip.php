<?php
require_once('auth.php');

$file = basename($_GET['f'] ?? '');

if (!$file || !preg_match('/^[a-zA-Z0-9_\-\.]+$/', $file)) {
    http_response_code(404); exit;
}

$base = realpath(__DIR__ . '/../assets/uploads/slips');
$path = realpath($base . DIRECTORY_SEPARATOR . $file);

if (!$path || strpos($path, $base . DIRECTORY_SEPARATOR) !== 0 || !is_file($path)) {
    http_response_code(404); exit;
}

$ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mime = match($ext) {
    'jpg', 'jpeg' => 'image/jpeg',
    'png'         => 'image/png',
    'webp'        => 'image/webp',
    'pdf'         => 'application/pdf',
    default       => null,
};
if (!$mime) { http_response_code(404); exit; }

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, no-store');
readfile($path);
