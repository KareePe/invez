<?php
require_once('auth.php');
require_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: articles');
    exit;
}
csrf_verify();

$id = (int)($_POST['id'] ?? 0);
if ($id < 1) {
    flash('error', 'ID ไม่ถูกต้อง');
    header('Location: articles');
    exit;
}

$art_label = db()->prepare('SELECT title FROM articles WHERE id = ?');
$art_label->execute([$id]);
$art_label = (string)($art_label->fetchColumn() ?: '');

db()->prepare('DELETE FROM articles WHERE id = ?')->execute([$id]);

log_admin_activity('delete', 'article', $id, $art_label);
flash('success', 'ลบบทความสำเร็จ');
header('Location: articles');
exit;
