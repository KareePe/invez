<?php
require_once('auth.php');
require_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: properties');
    exit;
}
csrf_verify();

$id = (int)($_POST['id'] ?? 0);
if ($id < 1) {
    flash('error', 'ID ไม่ถูกต้อง');
    header('Location: properties');
    exit;
}

// Delete image files
$imgs = db()->prepare('SELECT filename FROM property_images WHERE property_id = ?');
$imgs->execute([$id]);
foreach ($imgs->fetchAll() as $img) {
    $path = dirname(__DIR__) . '/assets/images/properties/' . $id . '/' . $img['filename'];
    if (file_exists($path)) unlink($path);
}

// DB cascade deletes highlights and images
db()->prepare('DELETE FROM properties WHERE id = ?')->execute([$id]);

flash('success', 'ลบทรัพย์สินสำเร็จ');
header('Location: properties');
exit;
