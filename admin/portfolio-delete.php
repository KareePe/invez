<?php
require_once('auth.php');
require_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: portfolios');
    exit;
}
csrf_verify();

$id = (int)($_POST['id'] ?? 0);
if ($id < 1) {
    flash('error', 'ID ไม่ถูกต้อง');
    header('Location: portfolios');
    exit;
}

// Delete image files
$imgs = db()->prepare('SELECT filename FROM portfolio_images WHERE portfolio_id = ?');
$imgs->execute([$id]);
foreach ($imgs->fetchAll() as $img) {
    $path = dirname(__DIR__) . '/assets/images/portfolios/' . $id . '/' . $img['filename'];
    if (file_exists($path)) unlink($path);
}

// Remove upload directory if empty
$dir = dirname(__DIR__) . '/assets/images/portfolios/' . $id;
if (is_dir($dir)) @rmdir($dir);

db()->prepare('DELETE FROM portfolio_images WHERE portfolio_id = ?')->execute([$id]);
db()->prepare('DELETE FROM portfolios WHERE id = ?')->execute([$id]);

flash('success', 'ลบผลงานสำเร็จ');
header('Location: portfolios');
exit;
