<?php
require_once('auth.php');
require_once('../config/db.php');

$page_title = 'คอนเทนท์';

$articles = db()->query(
    'SELECT id, icon, category, title, is_active FROM articles ORDER BY id ASC'
)->fetchAll();

include('_header.php');
?>

<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-gray-500">ทั้งหมด <?= count($articles) ?> บทความ</p>
    <a href="article-edit.php"
       class="bg-[#c9a96e] hover:bg-[#b8965e] text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        + เพิ่มบทความ
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-xs text-gray-400 uppercase">
                <th class="px-5 py-3 text-left font-medium w-12">ID</th>
                <th class="px-5 py-3 text-left font-medium">บทความ</th>
                <th class="px-5 py-3 text-left font-medium">หมวด</th>
                <th class="px-5 py-3 text-left font-medium">แสดง</th>
                <th class="px-5 py-3 text-left font-medium">จัดการ</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php foreach ($articles as $a): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 text-gray-400 text-xs"><?= $a['id'] ?></td>
                <td class="px-5 py-3 font-medium text-gray-800">
                    <a href="article-edit.php?id=<?= $a['id'] ?>" class="hover:text-[#c9a96e]">
                        <?= htmlspecialchars($a['title']) ?>
                    </a>
                </td>
                <td class="px-5 py-3 text-xs text-gray-500"><?= htmlspecialchars($a['category']) ?></td>
                <td class="px-5 py-3">
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium <?= $a['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                        <?= $a['is_active'] ? 'แสดง' : 'ซ่อน' ?>
                    </span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <a href="article-edit.php?id=<?= $a['id'] ?>" class="text-xs text-blue-600 hover:text-blue-800">แก้ไข</a>
                        <form method="POST" action="article-delete.php"
                              onsubmit="return confirm('ลบบทความนี้?')">
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
