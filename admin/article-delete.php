<?php
require_once('auth.php');
require_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: articles.php');
    exit;
}
csrf_verify();

$id = (int)($_POST['id'] ?? 0);
if ($id < 1) {
    flash('error', 'ID ไม่ถูกต้อง');
    header('Location: articles.php');
    exit;
}

db()->prepare('DELETE FROM articles WHERE id = ?')->execute([$id]);

flash('success', 'ลบบทความสำเร็จ');
header('Location: articles.php');
exit;
