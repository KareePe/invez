<?php
require_once('auth.php');
require_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admins');
    exit;
}
csrf_verify();

$id = (int)($_POST['id'] ?? 0);

$target = db()->prepare('SELECT username FROM admins WHERE id = ?');
$target->execute([$id]);
$target = $target->fetchColumn();

if ($target === 'invez_test' && ($_SESSION['admin_username'] ?? '') !== 'invez_test') {
    flash('error', 'ไม่มีสิทธิ์ลบบัญชีนี้');
    header('Location: admins');
    exit;
}

if ($id === (int)$_SESSION['admin_id']) {
    flash('error', 'ไม่สามารถลบบัญชีตัวเองได้');
    header('Location: admins');
    exit;
}

// Prevent deleting the last admin
$count = db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
if ($count <= 1) {
    flash('error', 'ต้องมีผู้ดูแลระบบอย่างน้อย 1 บัญชี');
    header('Location: admins');
    exit;
}

db()->prepare('DELETE FROM admins WHERE id = ?')->execute([$id]);

log_admin_activity('delete', 'admin', $id, $target ?: '');
flash('success', 'ลบแอดมินสำเร็จ');
header('Location: admins');
exit;
