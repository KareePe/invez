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

db()->prepare("UPDATE property_interests SET status = ? WHERE id = ?")->execute([$status, $id]);
flash('success', 'อัปเดตสถานะเรียบร้อย');
header('Location: interests');
exit;
