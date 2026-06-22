<?php
require_once('auth.php');
require_once('../config/db.php');

$page_title = 'Activity Log';

$per_page = 50;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $per_page;

$filter_admin  = (int)($_GET['admin_id'] ?? 0);
$filter_action = $_GET['action'] ?? '';
$filter_entity = $_GET['entity'] ?? '';

$valid_actions = ['create', 'update', 'delete'];
$valid_entities = ['property', 'portfolio', 'article', 'admin', 'member', 'interest'];

$where = [];
$params = [];

if ($filter_admin > 0) {
    $where[]  = 'admin_id = ?';
    $params[] = $filter_admin;
}
if (in_array($filter_action, $valid_actions, true)) {
    $where[]  = 'action = ?';
    $params[] = $filter_action;
}
if (in_array($filter_entity, $valid_entities, true)) {
    $where[]  = 'entity = ?';
    $params[] = $filter_entity;
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$total_stmt = db()->prepare("SELECT COUNT(*) FROM admin_activity_log {$where_sql}");
$total_stmt->execute($params);
$total = (int)$total_stmt->fetchColumn();

$logs_stmt = db()->prepare(
    "SELECT * FROM admin_activity_log {$where_sql} ORDER BY created_at DESC LIMIT {$per_page} OFFSET {$offset}"
);
$logs_stmt->execute($params);
$logs = $logs_stmt->fetchAll();

$admins = db()->query('SELECT id, name FROM admins ORDER BY name ASC')->fetchAll();

$action_labels = [
    'create' => ['label' => 'เพิ่ม',  'class' => 'bg-green-50 text-green-700 border-green-200'],
    'update' => ['label' => 'แก้ไข', 'class' => 'bg-blue-50 text-blue-700 border-blue-200'],
    'delete' => ['label' => 'ลบ',    'class' => 'bg-red-50 text-red-700 border-red-200'],
];
$entity_labels = [
    'property'  => 'ทรัพย์สิน',
    'portfolio' => 'ผลงาน',
    'article'   => 'บทความ',
    'admin'     => 'แอดมิน',
    'member'    => 'สมาชิก',
    'interest'  => 'คำสั่งซื้อ',
];

$base_url = 'logs?' . http_build_query(array_filter([
    'admin_id' => $filter_admin ?: null,
    'action'   => $filter_action ?: null,
    'entity'   => $filter_entity ?: null,
]));

include('_header.php');
?>

<div class="flex items-center gap-3 mb-5 flex-wrap">
    <form method="GET" class="flex items-center gap-2 flex-wrap">
        <select name="admin_id" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs text-gray-600 focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
            <option value="">ทุกแอดมิน</option>
            <?php foreach ($admins as $a): ?>
            <option value="<?= $a['id'] ?>" <?= $filter_admin === (int)$a['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($a['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>

        <select name="action" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs text-gray-600 focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
            <option value="">ทุกการกระทำ</option>
            <?php foreach ($action_labels as $val => $info): ?>
            <option value="<?= $val ?>" <?= $filter_action === $val ? 'selected' : '' ?>><?= $info['label'] ?></option>
            <?php endforeach; ?>
        </select>

        <select name="entity" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs text-gray-600 focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
            <option value="">ทุกประเภท</option>
            <?php foreach ($entity_labels as $val => $lbl): ?>
            <option value="<?= $val ?>" <?= $filter_entity === $val ? 'selected' : '' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="bg-[#c9a96e] hover:bg-[#b8965e] text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">
            กรอง
        </button>
        <?php if ($filter_admin || $filter_action || $filter_entity): ?>
        <a href="logs" class="text-xs text-gray-400 hover:text-gray-600">ล้าง</a>
        <?php endif; ?>
    </form>

    <span class="ml-auto text-xs text-gray-400"><?= number_format($total) ?> รายการ</span>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <?php if (empty($logs)): ?>
    <div class="px-6 py-12 text-center text-sm text-gray-400">ยังไม่มีประวัติการดำเนินการ</div>
    <?php else: ?>
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-xs text-gray-400 uppercase">
                <th class="px-5 py-3 text-left font-medium w-36">เวลา</th>
                <th class="px-5 py-3 text-left font-medium w-28">แอดมิน</th>
                <th class="px-5 py-3 text-left font-medium w-20">การกระทำ</th>
                <th class="px-5 py-3 text-left font-medium w-24">ประเภท</th>
                <th class="px-5 py-3 text-left font-medium">รายละเอียด</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php foreach ($logs as $log):
                $act = $action_labels[$log['action']] ?? ['label' => $log['action'], 'class' => 'bg-gray-50 text-gray-500 border-gray-200'];
                $ent = $entity_labels[$log['entity']] ?? $log['entity'];
                $ts  = date('d/m/y H:i', strtotime($log['created_at']));
            ?>
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 text-gray-400 text-xs whitespace-nowrap"><?= $ts ?></td>
                <td class="px-5 py-3 text-gray-700 text-xs font-medium"><?= htmlspecialchars($log['admin_name']) ?></td>
                <td class="px-5 py-3">
                    <span class="inline-block border px-2 py-0.5 rounded-full text-xs font-medium <?= $act['class'] ?>">
                        <?= $act['label'] ?>
                    </span>
                </td>
                <td class="px-5 py-3 text-gray-500 text-xs"><?= $ent ?></td>
                <td class="px-5 py-3 text-gray-700 text-xs">
                    <?php if ($log['label']): ?>
                    <?= htmlspecialchars($log['label']) ?>
                    <?php endif; ?>
                    <span class="text-gray-300 ml-1">#<?= $log['entity_id'] ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?= admin_pagination($total, $per_page, $page, $base_url) ?>
    <?php endif; ?>
</div>

<?php include('_footer.php'); ?>
