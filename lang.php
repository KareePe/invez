<?php
require_once(__DIR__ . '/config/lang.php');

$set = $_GET['set'] ?? 'th';
$_SESSION['lang'] = in_array($set, ['th', 'en'], true) ? $set : 'th';

$back = $_SERVER['HTTP_REFERER'] ?? '/';
$parsed = parse_url($back);
if (!empty($parsed['host']) && $parsed['host'] !== ($_SERVER['HTTP_HOST'] ?? '')) {
    $back = '/';
}

header('Location: ' . $back);
exit;
