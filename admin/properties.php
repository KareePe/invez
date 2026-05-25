<?php
require_once('auth.php');
require_once('../config/db.php');

$page_title = 'ทรัพย์สิน';

$properties = db()->query(
    'SELECT p.id, p.title, p.category, p.price_display, p.status, p.is_active,
            (SELECT filename FROM property_images WHERE property_id = p.id ORDER BY sort_order ASC LIMIT 1) AS thumb
     FROM properties p
     ORDER BY p.sort_order ASC, p.id ASC'
)->fetchAll();

include('_header.php');
?>

<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-gray-500">ทั้งหมด <?= count($properties) ?> รายการ</p>
    <a href="property-edit.php"
       class="bg-[#c9a96e] hover:bg-[#b8965e] text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        + เพิ่มทรัพย์สิน
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-xs text-gray-400 uppercase">
                <th class="px-5 py-3 text-left font-medium w-12">ID</th>
                <th class="px-5 py-3 text-left font-medium">ทรัพย์สิน</th>
                <th class="px-5 py-3 text-left font-medium">ราคา</th>
                <th class="px-5 py-3 text-left font-medium">สถานะ</th>
                <th class="px-5 py-3 text-left font-medium">แสดง</th>
                <th class="px-5 py-3 text-left font-medium">จัดการ</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
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
                            <div class="font-medium text-gray-800 leading-5">
                                <?= htmlspecialchars($p['title']) ?>
                            </div>
                            <div class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($p['category']) ?></div>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3 text-[#c9a96e] text-sm"><?= htmlspecialchars($p['price_display'] ?? '-') ?></td>
                <td class="px-5 py-3 text-xs text-gray-500"><?= htmlspecialchars($p['status'] ?? '-') ?></td>
                <td class="px-5 py-3">
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium <?= $p['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                        <?= $p['is_active'] ? 'แสดง' : 'ซ่อน' ?>
                    </span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <a href="property-edit.php?id=<?= $p['id'] ?>"
                           class="text-xs text-blue-600 hover:text-blue-800">แก้ไข</a>
                        <form method="POST" action="property-delete.php"
                              onsubmit="return confirm('ลบทรัพย์สินนี้?')">
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
