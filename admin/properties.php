<?php
require_once('auth.php');
require_once('../config/db.php');

$page_title = 'ทรัพย์สิน';

$filter = $_GET['status'] ?? 'all';
if (!in_array($filter, ['all','pending'], true)) $filter = 'all';

if ($filter === 'pending') {
    $properties = db()->query(
        "SELECT p.id, p.title, p.category, p.price_display, p.status, p.is_active, p.approval_status,
                m.first_name, m.last_name,
                (SELECT filename FROM property_images WHERE property_id=p.id ORDER BY sort_order ASC LIMIT 1) AS thumb
         FROM properties p LEFT JOIN members m ON m.id = p.submitted_by
         WHERE p.approval_status = 'pending'
         ORDER BY p.id DESC"
    )->fetchAll();
} else {
    $properties = db()->query(
        "SELECT p.id, p.title, p.category, p.price_display, p.status, p.is_active, p.approval_status,
                (SELECT filename FROM property_images WHERE property_id=p.id ORDER BY sort_order ASC LIMIT 1) AS thumb
         FROM properties p
         WHERE p.approval_status = 'approved' OR p.submitted_by IS NULL
         ORDER BY p.sort_order ASC, p.id ASC"
    )->fetchAll();
}

$pending_count = (int)db()->query("SELECT COUNT(*) FROM properties WHERE approval_status='pending'")->fetchColumn();

include('_header.php');
?>

<div class="flex items-center justify-between mb-5">
    <div class="flex gap-2 text-xs">
        <a href="properties"
           class="px-3 py-1.5 rounded-lg font-medium transition-colors <?= $filter === 'all' ? 'bg-[#1a1714] text-white' : 'bg-white border border-gray-200 text-gray-500 hover:border-gray-400' ?>">
            ทั้งหมด
        </a>
        <a href="properties?status=pending"
           class="px-3 py-1.5 rounded-lg font-medium transition-colors flex items-center gap-1.5 <?= $filter === 'pending' ? 'bg-amber-500 text-white' : 'bg-white border border-gray-200 text-gray-500 hover:border-gray-400' ?>">
            รอยืนยัน
            <?php if ($pending_count > 0): ?>
            <span class="<?= $filter === 'pending' ? 'bg-white/30 text-white' : 'bg-amber-100 text-amber-700' ?> text-[10px] font-bold px-1.5 py-0.5 rounded-full"><?= $pending_count ?></span>
            <?php endif; ?>
        </a>
    </div>
    <?php if ($filter !== 'pending'): ?>
    <a href="property-edit" class="bg-[#c9a96e] hover:bg-[#b8965e] text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">+ เพิ่มทรัพย์สิน</a>
    <?php endif; ?>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-xs text-gray-400 uppercase">
                <th class="px-5 py-3 text-left font-medium w-12">ID</th>
                <th class="px-5 py-3 text-left font-medium">ทรัพย์สิน</th>
                <th class="px-5 py-3 text-left font-medium">ราคา</th>
                <?php if ($filter === 'pending'): ?>
                <th class="px-5 py-3 text-left font-medium">สมาชิก</th>
                <?php else: ?>
                <th class="px-5 py-3 text-left font-medium">สถานะ</th>
                <th class="px-5 py-3 text-left font-medium">แสดง</th>
                <?php endif; ?>
                <th class="px-5 py-3 text-left font-medium">จัดการ</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php if (empty($properties)): ?>
            <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">ไม่มีข้อมูล</td></tr>
            <?php endif; ?>
            <?php foreach ($properties as $p): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 text-gray-400 text-xs"><?= $p['id'] ?></td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <?php if ($p['thumb']): ?>
                        <img src="<?= $_base_path ?>/assets/images/properties/<?= $p['id'] ?>/<?= htmlspecialchars($p['thumb']) ?>"
                             class="w-10 h-10 object-cover rounded-lg flex-shrink-0 bg-gray-100" alt="">
                        <?php else: ?>
                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex-shrink-0"></div>
                        <?php endif; ?>
                        <div>
                            <div class="font-medium text-gray-800 leading-5"><?= htmlspecialchars($p['title']) ?></div>
                            <div class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($p['category']) ?></div>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3 text-[#c9a96e] text-sm"><?= htmlspecialchars($p['price_display'] ?? '-') ?></td>
                <?php if ($filter === 'pending'): ?>
                <td class="px-5 py-3 text-xs text-gray-500"><?= htmlspecialchars(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')) ?></td>
                <?php else: ?>
                <td class="px-5 py-3 text-xs text-gray-500"><?= htmlspecialchars($p['status'] ?? '-') ?></td>
                <td class="px-5 py-3">
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium <?= $p['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                        <?= $p['is_active'] ? 'แสดง' : 'ซ่อน' ?>
                    </span>
                </td>
                <?php endif; ?>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                        <?php if ($filter === 'pending'): ?>
                        <form method="POST" action="submission-approve">
                            <input type="hidden" name="type" value="property">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <button type="submit" class="text-xs text-green-600 hover:text-green-800 font-medium">อนุมัติ</button>
                        </form>
                        <form method="POST" action="submission-approve">
                            <input type="hidden" name="type" value="property">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">ปฏิเสธ</button>
                        </form>
                        <?php endif; ?>
                        <a href="property-edit?id=<?= $p['id'] ?>" class="text-xs text-blue-600 hover:text-blue-800">แก้ไข</a>
                        <form method="POST" action="property-delete" data-confirm="ลบทรัพย์สินนี้?">
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

<?php include('_footer.php'); ?>
