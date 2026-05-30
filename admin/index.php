<?php
require_once('auth.php');
require_once('../config/db.php');

$page_title = 'Dashboard';

$prop_count    = db()->query('SELECT COUNT(*) FROM properties WHERE is_active = 1')->fetchColumn();
$article_count = db()->query('SELECT COUNT(*) FROM articles WHERE is_active = 1')->fetchColumn();
$admin_count   = db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();

$recent_props = db()->query(
    'SELECT id, title, category, price_display, created_at FROM properties ORDER BY created_at DESC LIMIT 5'
)->fetchAll();

include('_header.php');
?>

<div class="grid grid-cols-3 gap-5 mb-8">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="text-2xl font-bold text-[#c9a96e] mb-1"><?= $prop_count ?></div>
        <div class="text-sm text-gray-500">ทรัพย์สินทั้งหมด</div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="text-2xl font-bold text-[#c9a96e] mb-1"><?= $article_count ?></div>
        <div class="text-sm text-gray-500">บทความทั้งหมด</div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="text-2xl font-bold text-[#c9a96e] mb-1"><?= $admin_count ?></div>
        <div class="text-sm text-gray-500">ผู้ดูแลระบบ</div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-semibold text-gray-700 text-sm">ทรัพย์สินล่าสุด</h2>
        <a href="properties" class="text-xs text-[#c9a96e] hover:underline">ดูทั้งหมด</a>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-xs text-gray-400 uppercase">
                <th class="px-6 py-3 text-left font-medium">ID</th>
                <th class="px-6 py-3 text-left font-medium">ชื่อทรัพย์สิน</th>
                <th class="px-6 py-3 text-left font-medium">ราคา</th>
                <th class="px-6 py-3 text-left font-medium">วันที่</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php foreach ($recent_props as $p): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3 text-gray-400"><?= $p['id'] ?></td>
                <td class="px-6 py-3 font-medium text-gray-800">
                    <a href="property-edit?id=<?= $p['id'] ?>" class="hover:text-[#c9a96e]">
                        <?= htmlspecialchars($p['title']) ?>
                    </a>
                </td>
                <td class="px-6 py-3 text-[#c9a96e]"><?= htmlspecialchars($p['price_display'] ?? '-') ?></td>
                <td class="px-6 py-3 text-gray-400 text-xs"><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include('_footer.php'); ?>
