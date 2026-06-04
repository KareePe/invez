<?php
$_page_title  = $page_title ?? 'Admin';
$_current     = basename($_SERVER['PHP_SELF'], '.php');
function _nav_active(string $group): string {
    global $_current;
    static $groups = [
        'index'      => ['index'],
        'properties' => ['properties', 'property-edit', 'property-delete'],
        'portfolios' => ['portfolios', 'portfolio-edit', 'portfolio-delete'],
        'articles'   => ['articles', 'article-edit', 'article-delete'],
        'members'    => ['members', 'member-status'],
        'interests'  => ['interests', 'interest-status'],
        'admins'     => ['admins', 'admin-edit', 'admin-delete'],
    ];
    return in_array($_current, $groups[$group] ?? [], true)
        ? 'bg-[#fdf6e8] text-[#c9a96e] font-medium'
        : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($_page_title) ?> — INVEZ Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .swal-popup {
            border-radius: 8px !important;
            padding: 24px !important;
            box-shadow: 0 0 0 1px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.08) !important;
            font-family: inherit !important;
            max-width: 360px !important;
            width: 360px !important;
        }
        .swal-title {
            font-size: 14px !important;
            font-weight: 600 !important;
            color: #111827 !important;
            text-align: left !important;
            padding: 0 !important;
            margin: 0 0 4px !important;
            line-height: 1.4 !important;
        }
        .swal-text {
            font-size: 13px !important;
            color: #6b7280 !important;
            text-align: left !important;
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1.5 !important;
        }
        .swal-actions {
            justify-content: flex-end !important;
            gap: 8px !important;
            margin-top: 20px !important;
            padding: 0 !important;
        }
        .swal-cancel {
            border: 1px solid #e5e7eb !important;
            background: #fff !important;
            color: #374151 !important;
            border-radius: 6px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            padding: 6px 14px !important;
            margin: 0 !important;
            transition: background 0.15s !important;
        }
        .swal-cancel:hover { background: #f9fafb !important; }
        .swal-confirm {
            background: #dc2626 !important;
            color: #fff !important;
            border-radius: 6px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            padding: 6px 14px !important;
            margin: 0 !important;
            box-shadow: none !important;
            transition: background 0.15s !important;
        }
        .swal-confirm:hover { background: #b91c1c !important; }
        .swal2-backdrop-show { background: rgba(0,0,0,0.3) !important; }
    </style>
    <style>
        .sidebar-link { display:flex; align-items:center; gap:10px; padding:9px 12px; border-radius:8px; font-size:14px; transition:all .15s; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
<div class="flex min-h-screen">

<!-- Sidebar -->
<aside class="w-52 bg-white border-r border-gray-200 flex flex-col fixed inset-y-0 left-0 z-30 overflow-y-auto">
    <div class="px-5 py-5 border-b border-gray-100 flex-shrink-0">
        <div class="text-[#c9a96e] font-semibold text-base tracking-wide">INVEZ</div>
        <div class="text-gray-400 text-xs mt-0.5">Admin Panel</div>
    </div>
    <nav class="flex-1 px-3 py-3 space-y-0.5">
        <a href="index" class="sidebar-link <?= _nav_active('index') ?>">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a href="properties" class="sidebar-link <?= _nav_active('properties') ?>">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            ทรัพย์สิน
        </a>
        <a href="portfolios" class="sidebar-link <?= _nav_active('portfolios') ?>">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            ผลงาน
        </a>
        <a href="articles" class="sidebar-link <?= _nav_active('articles') ?>">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            คอนเทนท์
        </a>
        <a href="members" class="sidebar-link <?= _nav_active('members') ?>">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            สมาชิก
            <?php
            $pending_count = db()->query("SELECT COUNT(*) FROM members WHERE status = 'pending'")->fetchColumn();
            if ($pending_count > 0):
            ?>
            <span class="ml-auto bg-amber-400 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full leading-none"><?= $pending_count ?></span>
            <?php endif; ?>
        </a>
        <a href="interests" class="sidebar-link <?= _nav_active('interests') ?>">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            คำสั่งซื้อ
            <?php
            $interest_pending = db()->query("SELECT COUNT(*) FROM property_interests WHERE status = 'pending' AND slip_filename IS NOT NULL")->fetchColumn();
            if ($interest_pending > 0):
            ?>
            <span class="ml-auto bg-amber-400 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full leading-none"><?= $interest_pending ?></span>
            <?php endif; ?>
        </a>
        <a href="admins" class="sidebar-link <?= _nav_active('admins') ?>">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            ผู้ดูแลระบบ
        </a>
    </nav>
    <div class="px-3 py-3 border-t border-gray-100 flex-shrink-0">
        <div class="text-gray-400 text-xs px-3 mb-1 truncate"><?= htmlspecialchars($_SESSION['admin_name'] ?? '') ?></div>
        <a href="logout" class="sidebar-link text-gray-400 hover:bg-gray-100 hover:text-gray-700">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            ออกจากระบบ
        </a>
    </div>
</aside>

<!-- Content -->
<div class="flex-1 ml-52 flex flex-col min-h-screen">
    <header class="bg-white border-b border-gray-200 px-6 py-4 sticky top-0 z-20">
        <h1 class="font-semibold text-gray-800"><?= htmlspecialchars($_page_title) ?></h1>
    </header>
    <?php if (!empty($_SESSION['flash'])): ?>
    <div class="px-6 pt-4">
        <?php foreach ($_SESSION['flash'] as $type => $msg): ?>
        <div class="p-3 rounded-lg text-sm mb-2 <?= $type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
            <?= htmlspecialchars($msg) ?>
        </div>
        <?php endforeach; unset($_SESSION['flash']); ?>
    </div>
    <?php endif; ?>
    <main class="flex-1 p-6">
