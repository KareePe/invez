<?php
require_once('_auth.php');
require_once('../config/lang.php');
require_once('../config/db.php');

$page_title = t('คอนเท้นของฉัน','My Content');

$stmt = db()->prepare(
    'SELECT id, category, title, approval_status, created_at FROM articles
     WHERE submitted_by = ? ORDER BY created_at DESC'
);
$stmt->execute([$_SESSION['member_id']]);
$articles = $stmt->fetchAll();

include('_header.php');
?>

<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-[#9d8f82]"><?= t('ทั้งหมด','Total') ?> <?= count($articles) ?> <?= t('รายการ','items') ?></p>
    <a href="article-edit"
       class="bg-[#c9a96e] hover:bg-[#b8965e] text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        + <?= t('ลงคอนเท้น','Submit Content') ?>
    </a>
</div>

<div class="bg-white rounded-xl border border-[#e8e4df] overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-[#f0ebe3] text-xs text-[#9d8f82] uppercase">
                <th class="px-5 py-3 text-left font-medium"><?= t('บทความ','Article') ?></th>
                <th class="px-5 py-3 text-left font-medium"><?= t('หมวด','Category') ?></th>
                <th class="px-5 py-3 text-left font-medium"><?= t('วันที่','Date') ?></th>
                <th class="px-5 py-3 text-left font-medium"><?= t('สถานะ','Status') ?></th>
                <th class="px-5 py-3 text-left font-medium"><?= t('จัดการ','Action') ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[#f5f3f0]">
            <?php if (empty($articles)): ?>
            <tr><td colspan="5" class="px-5 py-10 text-center text-[#9d8f82]"><?= t('ยังไม่มีคอนเท้น','No content yet') ?></td></tr>
            <?php endif; ?>
            <?php foreach ($articles as $a):
                $badge = match($a['approval_status']) {
                    'pending'  => ['bg-amber-100 text-amber-700', t('รอยืนยัน','Pending')],
                    'approved' => ['bg-green-100 text-green-700', t('อนุมัติ','Approved')],
                    'rejected' => ['bg-red-100 text-red-600',   t('ปฏิเสธ','Rejected')],
                    default    => ['bg-gray-100 text-gray-500',  $a['approval_status']],
                };
            ?>
            <tr class="hover:bg-[#fafaf8]">
                <td class="px-5 py-3 font-medium text-[#1a1714]">
                    <?php if ($a['approval_status'] === 'pending'): ?>
                    <a href="article-edit?id=<?= $a['id'] ?>" class="hover:text-[#c9a96e]"><?= htmlspecialchars($a['title']) ?></a>
                    <?php else: ?>
                    <?= htmlspecialchars($a['title']) ?>
                    <?php endif; ?>
                </td>
                <td class="px-5 py-3 text-xs text-[#6b5f52]"><?= htmlspecialchars($a['category']) ?></td>
                <td class="px-5 py-3 text-xs text-[#9d8f82]"><?= date('d/m/Y', strtotime($a['created_at'])) ?></td>
                <td class="px-5 py-3">
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium <?= $badge[0] ?>"><?= $badge[1] ?></span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <?php if ($a['approval_status'] === 'pending'): ?>
                        <a href="article-edit?id=<?= $a['id'] ?>" class="text-xs text-blue-600 hover:text-blue-800"><?= t('แก้ไข','Edit') ?></a>
                        <?php endif; ?>
                        <?php if ($a['approval_status'] !== 'approved'): ?>
                        <form method="POST" action="article-edit?delete=1" data-confirm="<?= t('ลบบทความนี้?','Delete this article?') ?>">
                            <input type="hidden" name="csrf_token" value="<?= member_csrf_token() ?>">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700"><?= t('ลบ','Delete') ?></button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include('_footer.php'); ?>
