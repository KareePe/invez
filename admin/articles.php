<?php
require_once('auth.php');
require_once('../config/db.php');

$page_title = 'คอนเทนท์';

$filter = $_GET['status'] ?? 'all';
if (!in_array($filter, ['all','pending'], true)) $filter = 'all';

if ($filter === 'pending') {
    $articles = db()->query(
        "SELECT a.id, a.icon, a.category, a.title, a.is_active, a.approval_status,
                m.first_name, m.last_name
         FROM articles a LEFT JOIN members m ON m.id = a.submitted_by
         WHERE a.approval_status = 'pending'
         ORDER BY a.id DESC"
    )->fetchAll();
} else {
    $articles = db()->query(
        "SELECT id, icon, category, title, is_active, approval_status
         FROM articles
         WHERE approval_status = 'approved' OR submitted_by IS NULL
         ORDER BY id ASC"
    )->fetchAll();
}

$pending_count = (int)db()->query("SELECT COUNT(*) FROM articles WHERE approval_status='pending'")->fetchColumn();

include('_header.php');
?>

<div class="flex items-center justify-between mb-5">
    <div class="flex gap-2 text-xs">
        <a href="articles"
           class="px-3 py-1.5 rounded-lg font-medium transition-colors <?= $filter === 'all' ? 'bg-[#1a1714] text-white' : 'bg-white border border-gray-200 text-gray-500 hover:border-gray-400' ?>">
            ทั้งหมด
        </a>
        <a href="articles?status=pending"
           class="px-3 py-1.5 rounded-lg font-medium transition-colors flex items-center gap-1.5 <?= $filter === 'pending' ? 'bg-amber-500 text-white' : 'bg-white border border-gray-200 text-gray-500 hover:border-gray-400' ?>">
            รอยืนยัน
            <?php if ($pending_count > 0): ?>
            <span class="<?= $filter === 'pending' ? 'bg-white/30 text-white' : 'bg-amber-100 text-amber-700' ?> text-[10px] font-bold px-1.5 py-0.5 rounded-full"><?= $pending_count ?></span>
            <?php endif; ?>
        </a>
    </div>
    <?php if ($filter !== 'pending'): ?>
    <a href="article-edit" class="bg-[#c9a96e] hover:bg-[#b8965e] text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">+ เพิ่มบทความ</a>
    <?php endif; ?>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-xs text-gray-400 uppercase">
                <th class="px-5 py-3 text-left font-medium w-12">ID</th>
                <th class="px-5 py-3 text-left font-medium">บทความ</th>
                <th class="px-5 py-3 text-left font-medium">หมวด</th>
                <?php if ($filter === 'pending'): ?>
                <th class="px-5 py-3 text-left font-medium">สมาชิก</th>
                <?php else: ?>
                <th class="px-5 py-3 text-left font-medium">แสดง</th>
                <?php endif; ?>
                <th class="px-5 py-3 text-left font-medium">จัดการ</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php if (empty($articles)): ?>
            <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">ไม่มีข้อมูล</td></tr>
            <?php endif; ?>
            <?php foreach ($articles as $a): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 text-gray-400 text-xs"><?= $a['id'] ?></td>
                <td class="px-5 py-3 font-medium text-gray-800">
                    <a href="article-edit?id=<?= $a['id'] ?>" class="hover:text-[#c9a96e]"><?= htmlspecialchars($a['title']) ?></a>
                </td>
                <td class="px-5 py-3 text-xs text-gray-500"><?= htmlspecialchars($a['category']) ?></td>
                <?php if ($filter === 'pending'): ?>
                <td class="px-5 py-3 text-xs text-gray-500"><?= htmlspecialchars(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? '')) ?></td>
                <?php else: ?>
                <td class="px-5 py-3">
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium <?= $a['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                        <?= $a['is_active'] ? 'แสดง' : 'ซ่อน' ?>
                    </span>
                </td>
                <?php endif; ?>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                        <?php if ($filter === 'pending'): ?>
                        <form method="POST" action="submission-approve">
                            <input type="hidden" name="type" value="article">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <button type="submit" class="text-xs text-green-600 hover:text-green-800 font-medium">อนุมัติ</button>
                        </form>
                        <form method="POST" action="submission-approve">
                            <input type="hidden" name="type" value="article">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">ปฏิเสธ</button>
                        </form>
                        <?php endif; ?>
                        <a href="article-edit?id=<?= $a['id'] ?>" class="text-xs text-blue-600 hover:text-blue-800">แก้ไข</a>
                        <form method="POST" action="article-delete" data-confirm="ลบบทความนี้?">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
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

<?php include('_footer.php'); ?>
