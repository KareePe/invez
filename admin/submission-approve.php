<?php
require_once('auth.php');
require_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index');
    exit;
}
csrf_verify();

$type   = $_POST['type']   ?? '';
$id     = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($id < 1 || !in_array($type, ['property','article'], true) || !in_array($action, ['approve','reject'], true)) {
    flash('error', 'คำขอไม่ถูกต้อง');
    header('Location: ' . ($type === 'article' ? 'articles' : 'properties'));
    exit;
}

$table = $type === 'article' ? 'articles' : 'properties';
$back  = $type === 'article' ? 'articles?status=pending' : 'properties?status=pending';

$sub_label = db()->prepare("SELECT title FROM {$table} WHERE id = ?");
$sub_label->execute([$id]);
$sub_label = (string)($sub_label->fetchColumn() ?: '');

if ($action === 'approve') {
    db()->prepare("UPDATE {$table} SET is_active=1, approval_status='approved' WHERE id=?")->execute([$id]);
    log_admin_activity('update', $type, $id, $sub_label . ' (อนุมัติ)');
    flash('success', 'อนุมัติสำเร็จ');
} else {
    db()->prepare("UPDATE {$table} SET is_active=0, approval_status='rejected' WHERE id=?")->execute([$id]);
    log_admin_activity('update', $type, $id, $sub_label . ' (ปฏิเสธ)');
    flash('success', 'ปฏิเสธสำเร็จ');
}

header('Location: ' . $back);
exit;
