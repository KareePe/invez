<?php
require_once('auth.php');
require_once('../config/db.php');

$page_title = 'คำสั่งซื้อ';

$filter   = $_GET['status'] ?? 'all';
$valid    = ['all', 'pending', 'confirmed', 'contracted', 'rejected', 'completed'];
if (!in_array($filter, $valid, true)) $filter = 'all';

$per_page = 20;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $per_page;
$base_url = 'interests' . ($filter !== 'all' ? '?status=' . $filter : '');

$where  = $filter === 'all' ? '' : 'WHERE pi.status = ?';
$params = $filter === 'all' ? [] : [$filter];

$cnt_stmt = db()->prepare("SELECT COUNT(*) FROM property_interests pi {$where}");
$cnt_stmt->execute($params);
$total = (int)$cnt_stmt->fetchColumn();

$stmt = db()->prepare("
    SELECT pi.*, p.title AS prop_title, CONCAT(m.first_name, ' ', m.last_name) AS member_name, m.email AS member_email
    FROM property_interests pi
    JOIN properties p ON p.id = pi.property_id
    JOIN members m ON m.id = pi.member_id
    {$where}
    ORDER BY pi.created_at DESC
    LIMIT {$per_page} OFFSET {$offset}
");
$stmt->execute($params);
$orders = $stmt->fetchAll();

$counts = db()->query("SELECT status, COUNT(*) AS cnt FROM property_interests GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

$status_label = [
    'pending'    => ['รอยืนยัน',      'bg-amber-100 text-amber-700'],
    'confirmed'  => ['ยืนยันแล้ว',     'bg-green-100 text-green-700'],
    'contracted' => ['เซ็นสัญญาแล้ว', 'bg-indigo-100 text-indigo-700'],
    'rejected'   => ['ไม่อนุมัติ',     'bg-red-100 text-red-700'],
    'completed'  => ['สำเร็จ',         'bg-blue-100 text-blue-700'],
];

// Orders that are "contracted" get a 90-day deadline counted from the day the
// status was set. Returns [due date, days remaining] — negative days = overdue.
function contract_deadline(?string $contracted_at): ?array {
    if (empty($contracted_at)) return null;
    $start = date_create(substr($contracted_at, 0, 10));
    if (!$start) return null;
    $due = (clone $start)->modify('+90 days');
    return [$due, (int)date_create('today')->diff($due)->format('%r%a')];
}

include('_header.php');
?>

<div class="flex flex-wrap items-center gap-2 mb-5">
    <?php
    $tabs = ['all' => 'ทั้งหมด', 'pending' => 'รอยืนยัน', 'confirmed' => 'ยืนยันแล้ว', 'contracted' => 'เซ็นสัญญาแล้ว', 'completed' => 'สำเร็จ', 'rejected' => 'ไม่อนุมัติ'];
    foreach ($tabs as $k => $label):
        $cnt = $k === 'all' ? array_sum($counts) : ($counts[$k] ?? 0);
    ?>
    <a href="interests<?= $k !== 'all' ? '?status=' . $k : '' ?>"
       class="text-xs px-3 py-1.5 rounded-full border transition-colors <?= $filter === $k ? 'bg-[#1a1714] text-white border-[#1a1714]' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-400' ?>">
        <?= $label ?><?php if ($cnt > 0): ?> <span class="ml-1 opacity-70"><?= $cnt ?></span><?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>

<?php if (empty($orders)): ?>
<div class="text-center py-16 text-gray-400 text-sm">ไม่มีคำสั่งซื้อ</div>
<?php else: ?>
<div class="bg-white rounded-xl border border-gray-200">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[860px]">
            <thead>
                <tr class="border-b border-gray-100 text-xs text-gray-400 uppercase">
                    <th class="px-4 py-3 text-left font-medium">Transaction ID</th>
                    <th class="px-4 py-3 text-left font-medium">สมาชิก</th>
                    <th class="px-4 py-3 text-left font-medium">ทรัพย์สิน</th>
                    <th class="px-4 py-3 text-right font-medium">ยอด</th>
                    <th class="px-4 py-3 text-left font-medium">ธนาคาร</th>
                    <th class="px-4 py-3 text-left font-medium">สลิป</th>
                    <th class="px-4 py-3 text-left font-medium">สถานะ</th>
                    <th class="px-4 py-3 text-left font-medium">วันที่</th>
                    <th class="px-4 py-3 text-left font-medium">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            <?php foreach ($orders as $o):
                [$sl, $sc] = $status_label[$o['status']] ?? [$o['status'], 'bg-gray-100 text-gray-600'];
                $deadline  = $o['status'] === 'contracted' ? contract_deadline($o['contracted_at'] ?? null) : null;
            ?>
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3 text-gray-400 font-mono text-[11px] max-w-[140px] truncate" title="<?= htmlspecialchars($o['transaction_id'] ?? '') ?>">
                    <?= htmlspecialchars($o['transaction_id'] ?? '—') ?>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <p class="font-medium text-gray-800 text-xs"><?= htmlspecialchars($o['member_name']) ?></p>
                    <p class="text-[11px] text-gray-400"><?= htmlspecialchars($o['member_email']) ?></p>
                </td>
                <td class="px-4 py-3 max-w-[160px]">
                    <p class="truncate text-gray-700 text-xs"><?= htmlspecialchars($o['prop_title']) ?></p>
                </td>
                <td class="px-4 py-3 text-right font-medium text-gray-800 whitespace-nowrap text-xs">
                    <?= number_format($o['amount_value']) ?> ฿
                    <span class="text-gray-400">(<?= $o['amount_percent'] ?>%)</span>
                </td>
                <td class="px-4 py-3 uppercase text-gray-600 text-xs font-medium whitespace-nowrap"><?= htmlspecialchars($o['bank']) ?></td>
                <td class="px-4 py-3">
                    <?php if (!empty($o['slip_filename'])): ?>
                    <a href="slip?f=<?= urlencode($o['slip_filename']) ?>"
                       target="_blank"
                       class="inline-flex items-center gap-1 text-xs text-[#c9a96e] hover:text-[#b8965e] font-medium whitespace-nowrap">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        ดูสลิป
                    </a>
                    <?php else: ?>
                    <span class="text-xs text-gray-300">—</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full whitespace-nowrap <?= $sc ?>"><?= $sl ?></span>
                    <?php if ($deadline): [$due, $days_left] = $deadline; ?>
                    <p class="text-[11px] text-gray-400 mt-1 whitespace-nowrap">
                        ครบกำหนด <?= $due->format('d/m/Y') ?>
                    </p>
                    <p class="text-[11px] font-medium whitespace-nowrap <?= $days_left < 0 ? 'text-red-600' : ($days_left <= 15 ? 'text-amber-600' : 'text-gray-500') ?>">
                        <?= $days_left < 0 ? 'เกินกำหนด ' . abs($days_left) . ' วัน' : ($days_left === 0 ? 'ครบกำหนดวันนี้' : 'เหลืออีก ' . $days_left . ' วัน') ?>
                    </p>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap"><?= date('d/m/Y', strtotime($o['created_at'])) ?></td>
                <td class="px-4 py-3">
                    <form method="POST" action="interest-status">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="id" value="<?= $o['id'] ?>">
                        <select name="status" onchange="this.form.submit()"
                                class="text-xs border border-gray-200 rounded px-2 py-1.5 bg-white text-gray-700 focus:outline-none focus:border-[#c9a96e] cursor-pointer">
                            <?php foreach ($status_label as $sk => [$sk_label, $_]): ?>
                            <option value="<?= $sk ?>" <?= $o['status'] === $sk ? 'selected' : '' ?>><?= $sk_label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?= admin_pagination($total, $per_page, $page, $base_url) ?>

<?php include('_footer.php'); ?>
