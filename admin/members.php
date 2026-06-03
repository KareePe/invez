<?php
require_once('auth.php');
require_once('../config/db.php');

$page_title = 'สมาชิก';

$filter = $_GET['status'] ?? 'all';
$valid  = ['all', 'pending', 'approved', 'rejected'];
if (!in_array($filter, $valid, true)) $filter = 'all';

if ($filter === 'all') {
    $members = db()->query(
        "SELECT * FROM members ORDER BY
         FIELD(status,'pending','approved','rejected'), created_at DESC"
    )->fetchAll();
} else {
    $stmt = db()->prepare("SELECT * FROM members WHERE status = ? ORDER BY created_at DESC");
    $stmt->execute([$filter]);
    $members = $stmt->fetchAll();
}

$counts = db()->query(
    "SELECT status, COUNT(*) AS cnt FROM members GROUP BY status"
)->fetchAll(PDO::FETCH_KEY_PAIR);

include('_header.php');
?>

<!-- Filter tabs -->
<div class="flex items-center justify-between mb-5">
    <div class="flex gap-2 text-xs">
        <?php
        $tabs = [
            'all'      => ['ทั้งหมด', ($counts['pending'] ?? 0) + ($counts['approved'] ?? 0) + ($counts['rejected'] ?? 0)],
            'pending'  => ['รอยืนยัน', $counts['pending']  ?? 0],
            'approved' => ['อนุมัติแล้ว', $counts['approved'] ?? 0],
            'rejected' => ['ปฏิเสธ', $counts['rejected'] ?? 0],
        ];
        foreach ($tabs as $key => [$label, $count]):
        ?>
        <a href="members<?= $key !== 'all' ? '?status='.$key : '' ?>"
           class="px-3 py-1.5 rounded-lg font-medium transition-colors
                  <?= $filter === $key
                    ? 'bg-[#1a1714] text-white'
                    : 'bg-white border border-gray-200 text-gray-500 hover:border-gray-400' ?>">
            <?= $label ?>
            <span class="ml-1 <?= $filter === $key ? 'text-white/60' : 'text-gray-400' ?>">(<?= $count ?>)</span>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-xs text-gray-400 uppercase">
                <th class="px-5 py-3 text-left font-medium w-10">ID</th>
                <th class="px-5 py-3 text-left font-medium">ชื่อ-สกุล</th>
                <th class="px-5 py-3 text-left font-medium">ชื่อผู้ใช้งาน</th>
                <th class="px-5 py-3 text-left font-medium">อีเมล</th>
                <th class="px-5 py-3 text-left font-medium">เบอร์โทร</th>
                <th class="px-5 py-3 text-left font-medium">วันที่สมัคร</th>
                <th class="px-5 py-3 text-left font-medium">สถานะ</th>
                <th class="px-5 py-3 text-left font-medium">จัดการ</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php if (empty($members)): ?>
            <tr>
                <td colspan="8" class="px-5 py-10 text-center text-gray-400 text-sm">ไม่มีข้อมูล</td>
            </tr>
            <?php endif; ?>
            <?php foreach ($members as $m): ?>
            <tr class="hover:bg-gray-50 <?= $m['status'] === 'pending' ? 'bg-amber-50/40' : '' ?>">
                <td class="px-5 py-3 text-gray-400 text-xs"><?= $m['id'] ?></td>
                <td class="px-5 py-3 font-medium text-gray-800">
                    <?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?>
                </td>
                <td class="px-5 py-3 text-gray-600 text-xs font-mono"><?= htmlspecialchars($m['username']) ?></td>
                <td class="px-5 py-3 text-gray-500 text-xs"><?= htmlspecialchars($m['email']) ?></td>
                <td class="px-5 py-3 text-gray-500 text-xs"><?= htmlspecialchars($m['phone'] ?? '—') ?></td>
                <td class="px-5 py-3 text-gray-400 text-xs"><?= date('d/m/Y H:i', strtotime($m['created_at'])) ?></td>
                <td class="px-5 py-3">
                    <?php
                    $badge = match($m['status']) {
                        'pending'  => ['bg-amber-100 text-amber-700', 'รอยืนยัน'],
                        'approved' => ['bg-green-100 text-green-700', 'อนุมัติแล้ว'],
                        'rejected' => ['bg-red-100 text-red-600',   'ปฏิเสธ'],
                        default    => ['bg-gray-100 text-gray-500',  $m['status']],
                    };
                    ?>
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium <?= $badge[0] ?>">
                        <?= $badge[1] ?>
                    </span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                        <?php if ($m['status'] !== 'approved'): ?>
                        <form method="POST" action="member-status">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <button type="submit" class="text-xs text-green-600 hover:text-green-800 font-medium">อนุมัติ</button>
                        </form>
                        <?php endif; ?>
                        <?php if ($m['status'] !== 'rejected'): ?>
                        <form method="POST" action="member-status">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <button type="submit" class="text-xs text-amber-600 hover:text-amber-800 font-medium">ปฏิเสธ</button>
                        </form>
                        <?php endif; ?>
                        <form method="POST" action="member-status" data-confirm="ลบสมาชิกนี้?">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <input type="hidden" name="action" value="delete">
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
