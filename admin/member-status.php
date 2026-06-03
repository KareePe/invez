<?php
require_once('auth.php');
require_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: members');
    exit;
}
csrf_verify();

$id     = (int)($_POST['id']     ?? 0);
$action = $_POST['action'] ?? '';

if ($id < 1 || !in_array($action, ['approve', 'reject', 'delete'], true)) {
    flash('error', 'คำขอไม่ถูกต้อง');
    header('Location: members');
    exit;
}

if ($action === 'delete') {
    db()->prepare('DELETE FROM members WHERE id = ?')->execute([$id]);
    flash('success', 'ลบสมาชิกสำเร็จ');
} elseif ($action === 'approve') {
    db()->prepare("UPDATE members SET status = 'approved' WHERE id = ?")->execute([$id]);
    flash('success', 'อนุมัติสมาชิกสำเร็จ');
} elseif ($action === 'reject') {
    db()->prepare("UPDATE members SET status = 'rejected' WHERE id = ?")->execute([$id]);
    flash('success', 'ปฏิเสธสมาชิกสำเร็จ');
}

header('Location: members');
exit;
