<?php
require_once('auth.php');
require_once('../config/db.php');

$page_title = 'ผลงาน';

$per_page = 10;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $per_page;

$total      = (int)db()->query("SELECT COUNT(*) FROM portfolios")->fetchColumn();
$portfolios = db()->query(
    "SELECT p.id, p.category, p.title, p.is_active,
            (SELECT COUNT(*) FROM portfolio_images WHERE portfolio_id = p.id) AS img_count
     FROM portfolios p ORDER BY p.sort_order ASC, p.id ASC
     LIMIT {$per_page} OFFSET {$offset}"
)->fetchAll();

include('_header.php');
?>

<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-gray-500">ทั้งหมด <?= $total ?> ผลงาน</p>
    <a href="portfolio-edit" class="bg-[#c9a96e] hover:bg-[#b8965e] text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">+ เพิ่มผลงาน</a>
</div>

<div class="bg-white rounded-xl border border-gray-200">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[480px]">
            <thead>
                <tr class="border-b border-gray-100 text-xs text-gray-400 uppercase">
                    <th class="px-5 py-3 text-left font-medium w-12">ID</th>
                    <th class="px-5 py-3 text-left font-medium">ผลงาน</th>
                    <th class="px-5 py-3 text-left font-medium">หมวด</th>
                    <th class="px-5 py-3 text-left font-medium">รูป</th>
                    <th class="px-5 py-3 text-left font-medium">แสดง</th>
                    <th class="px-5 py-3 text-left font-medium">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php if (empty($portfolios)): ?>
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">ไม่มีข้อมูล</td></tr>
                <?php endif; ?>
                <?php foreach ($portfolios as $p): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 text-gray-400 text-xs"><?= $p['id'] ?></td>
                    <td class="px-5 py-3 font-medium text-gray-800">
                        <a href="portfolio-edit?id=<?= $p['id'] ?>" class="hover:text-[#c9a96e]"><?= htmlspecialchars($p['title']) ?></a>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap"><?= htmlspecialchars($p['category']) ?></td>
                    <td class="px-5 py-3 text-xs text-gray-500"><?= $p['img_count'] ?> รูป</td>
                    <td class="px-5 py-3">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium <?= $p['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                            <?= $p['is_active'] ? 'แสดง' : 'ซ่อน' ?>
                        </span>
                    </td>
                    <td class="px-5 py-3 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <a href="portfolio-edit?id=<?= $p['id'] ?>" class="text-xs text-blue-600 hover:text-blue-800">แก้ไข</a>
                            <form method="POST" data-loading="overlay" action="portfolio-delete" data-confirm="ลบผลงานนี้?">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700">ลบ</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= admin_pagination($total, $per_page, $page, 'portfolios') ?>

<?php include('_footer.php'); ?>
