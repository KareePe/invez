<?php
require_once('auth.php');
require_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admins.php');
    exit;
}
csrf_verify();

$id = (int)($_POST['id'] ?? 0);

if ($id === (int)$_SESSION['admin_id']) {
    flash('error', 'ไม่สามารถลบบัญชีตัวเองได้');
    header('Location: admins.php');
    exit;
}

// Prevent deleting the last admin
$count = db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
if ($count <= 1) {
    flash('error', 'ต้องมีผู้ดูแลระบบอย่างน้อย 1 บัญชี');
    header('Location: admins.php');
    exit;
}

db()->prepare('DELETE FROM admins WHERE id = ?')->execute([$id]);

flash('success', 'ลบแอดมินสำเร็จ');
header('Location: admins.php');
exit;
