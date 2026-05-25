<?php
require_once('auth.php');
require_once('../config/db.php');

$page_title = 'ผู้ดูแลระบบ';

$admins = db()->query('SELECT id, username, name, created_at FROM admins ORDER BY id ASC')->fetchAll();

include('_header.php');
?>

<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-gray-500">ทั้งหมด <?= count($admins) ?> บัญชี</p>
    <a href="admin-edit.php"
       class="bg-[#c9a96e] hover:bg-[#b8965e] text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        + เพิ่มแอดมิน
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-xs text-gray-400 uppercase">
                <th class="px-5 py-3 text-left font-medium w-12">ID</th>
                <th class="px-5 py-3 text-left font-medium">Username</th>
                <th class="px-5 py-3 text-left font-medium">ชื่อ</th>
                <th class="px-5 py-3 text-left font-medium">สร้างเมื่อ</th>
                <th class="px-5 py-3 text-left font-medium">จัดการ</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php foreach ($admins as $a): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 text-gray-400 text-xs"><?= $a['id'] ?></td>
                <td class="px-5 py-3 font-medium text-gray-800"><?= htmlspecialchars($a['username']) ?></td>
                <td class="px-5 py-3 text-gray-600"><?= htmlspecialchars($a['name']) ?></td>
                <td class="px-5 py-3 text-gray-400 text-xs"><?= date('d/m/Y', strtotime($a['created_at'])) ?></td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <a href="admin-edit.php?id=<?= $a['id'] ?>" class="text-xs text-blue-600 hover:text-blue-800">แก้ไข</a>
                        <?php if ($a['id'] !== (int)$_SESSION['admin_id']): ?>
                        <form method="POST" action="admin-delete.php"
                              onsubmit="return confirm('ลบแอดมินนี้?')">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">ลบ</button>
                        </form>
                        <?php else: ?>
                        <span class="text-xs text-gray-300">ตัวเอง</span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include('_footer.php'); ?>
