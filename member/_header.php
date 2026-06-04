<?php
$_page_title  = $page_title ?? 'Member';
$_current     = basename($_SERVER['PHP_SELF'], '.php');
$_member_name = $_SESSION['member_name'] ?? '';

function _mnav(string $page): string {
    global $_current;
    return $_current === $page
        ? 'bg-[#fdf6e8] text-[#c9a96e] font-medium'
        : 'text-[#6b5f52] hover:bg-[#f5f3f0] hover:text-[#1a1714]';
}
?>
<!DOCTYPE html>
<html lang="<?= function_exists('lang') ? lang() : 'th' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($_page_title) ?> — INVEZ</title>
    <meta name="robots" content="noindex, nofollow">
    <?php
    $_member_dir_url = rtrim(str_replace('\\', '/', str_replace(
        realpath($_SERVER['DOCUMENT_ROOT']), '', realpath(__DIR__)
    )), '/') . '/';
    ?>
    <base href="<?= htmlspecialchars($_member_dir_url) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .mnav-link { display:flex; align-items:center; gap:10px; padding:9px 12px; border-radius:8px; font-size:14px; transition:all .15s; }
        .swal-popup { border-radius:8px!important;padding:24px!important;max-width:360px!important;font-family:inherit!important; }
        .swal-confirm { background:#dc2626!important;color:#fff!important;border-radius:6px!important;font-size:13px!important;font-weight:500!important;padding:6px 14px!important;box-shadow:none!important; }
        .swal-cancel { border:1px solid #e5e7eb!important;background:#fff!important;color:#374151!important;border-radius:6px!important;font-size:13px!important;font-weight:500!important;padding:6px 14px!important; }
        .swal2-backdrop-show { background:rgba(0,0,0,0.3)!important; }
    </style>
</head>
<body class="bg-[#fafaf8] text-[#1a1714]">
<div class="flex min-h-screen">

<!-- Sidebar -->
<aside class="w-56 bg-white border-r border-[#e8e4df] fixed inset-y-0 left-0 z-30 flex flex-col">
    <div class="px-5 py-4 border-b border-[#e8e4df]">
        <a href="../"><img src="../assets/images/logo-b.png" alt="INVEZ" class="h-5"></a>
        <p class="text-[10px] text-[#9d8f82] mt-1"><?= function_exists('t') ? t('พื้นที่สมาชิก','Member Area') : 'พื้นที่สมาชิก' ?></p>
    </div>
    <nav class="flex-1 px-3 py-3 space-y-0.5">
        <a href="settings" class="mnav-link <?= _mnav('settings') ?>">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
            <?= function_exists('t') ? t('ตั้งค่าผู้ใช้งาน','User Settings') : 'ตั้งค่าผู้ใช้งาน' ?>
        </a>
        <?php $_art_on = in_array($_current, ['articles', 'article-edit']); ?>
        <a href="articles" class="mnav-link <?= $_art_on ? 'bg-[#fdf6e8] text-[#c9a96e] font-medium' : 'text-[#6b5f52] hover:bg-[#f5f3f0] hover:text-[#1a1714]' ?>">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            <?= function_exists('t') ? t('ลงคอนเท้น','Submit Content') : 'ลงคอนเท้น' ?>
        </a>
        <?php $_prop_on = in_array($_current, ['properties', 'property-edit']); ?>
        <a href="properties" class="mnav-link <?= $_prop_on ? 'bg-[#fdf6e8] text-[#c9a96e] font-medium' : 'text-[#6b5f52] hover:bg-[#f5f3f0] hover:text-[#1a1714]' ?>">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <?= function_exists('t') ? t('ลงทรัพย์สิน','Submit Property') : 'ลงทรัพย์สิน' ?>
        </a>
        <a href="orders" class="mnav-link <?= _mnav('orders') ?>">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <?= function_exists('t') ? t('คำสั่งซื้อ','My Orders') : 'คำสั่งซื้อ' ?>
        </a>
    </nav>
    <div class="px-3 py-3 border-t border-[#e8e4df]">
        <div class="text-xs text-[#9d8f82] px-3 mb-1 truncate"><?= htmlspecialchars($_member_name) ?></div>
        <a href="../logout" class="mnav-link text-[#9d8f82] hover:bg-[#f5f3f0] hover:text-[#1a1714]">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            <?= function_exists('t') ? t('ออกจากระบบ','Logout') : 'ออกจากระบบ' ?>
        </a>
    </div>
</aside>

<!-- Content -->
<div class="flex-1 ml-56 flex flex-col min-h-screen">
    <header class="bg-white border-b border-[#e8e4df] px-6 py-4 sticky top-0 z-20">
        <h1 class="font-semibold text-[#1a1714]"><?= htmlspecialchars($_page_title) ?></h1>
    </header>
    <?php if (!empty($_SESSION['member_flash'])): ?>
    <div class="px-6 pt-4">
        <?php foreach ($_SESSION['member_flash'] as $type => $msg): ?>
        <div class="p-3 rounded-lg text-sm mb-2 <?= $type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
            <?= htmlspecialchars($msg) ?>
        </div>
        <?php endforeach; unset($_SESSION['member_flash']); ?>
    </div>
    <?php endif; ?>
    <main class="flex-1 p-6">
