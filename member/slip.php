<?php
require_once('_auth.php');
require_once('../config/db.php');

$file = basename($_GET['f'] ?? '');

if (!$file || !preg_match('/^[a-zA-Z0-9_\-\.]+$/', $file)) {
    http_response_code(404); exit;
}

// Verify ownership — member can only view their own slip
$stmt = db()->prepare("SELECT id FROM property_interests WHERE slip_filename = ? AND member_id = ? LIMIT 1");
$stmt->execute([$file, (int)$_SESSION['member_id']]);
if (!$stmt->fetch()) {
    http_response_code(403); exit;
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
