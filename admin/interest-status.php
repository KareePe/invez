<?php
require_once('auth.php');
require_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: interests');
    exit;
}
csrf_verify();

$id     = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';
$valid  = ['pending', 'confirmed', 'rejected', 'completed'];

if ($id < 1 || !in_array($status, $valid, true)) {
    flash('error', 'คำขอไม่ถูกต้อง');
    header('Location: interests');
    exit;
}

$int_label = db()->prepare('SELECT p.title FROM property_interests pi JOIN properties p ON p.id = pi.property_id WHERE pi.id = ?');
$int_label->execute([$id]);
$int_label = (string)($int_label->fetchColumn() ?: '');

db()->prepare("UPDATE property_interests SET status = ? WHERE id = ?")->execute([$status, $id]);
log_admin_activity('update', 'interest', $id, $int_label . ' → ' . $status);
flash('success', 'อัปเดตสถานะเรียบร้อย');
header('Location: interests');
exit;
