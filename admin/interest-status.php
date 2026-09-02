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
$valid  = ['pending', 'confirmed', 'contracted', 'rejected', 'completed'];

if ($id < 1 || !in_array($status, $valid, true)) {
    flash('error', 'คำขอไม่ถูกต้อง');
    header('Location: interests');
    exit;
}

$row = db()->prepare('SELECT pi.status, p.title FROM property_interests pi JOIN properties p ON p.id = pi.property_id WHERE pi.id = ?');
$row->execute([$id]);
$row = $row->fetch();

if (!$row) {
    flash('error', 'ไม่พบคำสั่งซื้อ');
    header('Location: interests');
    exit;
}

$int_label = (string)($row['title'] ?? '');

// contracted_at drives the 90-day countdown shown on the list page.
// Stamp it when the order first becomes "contracted", clear it when it leaves that status.
if ($status === 'contracted') {
    if ($row['status'] === 'contracted') {
        db()->prepare("UPDATE property_interests SET status = ? WHERE id = ?")->execute([$status, $id]);
    } else {
        db()->prepare("UPDATE property_interests SET status = ?, contracted_at = NOW() WHERE id = ?")->execute([$status, $id]);
    }
} else {
    db()->prepare("UPDATE property_interests SET status = ?, contracted_at = NULL WHERE id = ?")->execute([$status, $id]);
}
log_admin_activity('update', 'interest', $id, $int_label . ' → ' . $status);
flash('success', 'อัปเดตสถานะเรียบร้อย');
header('Location: interests');
exit;
