<?php
require_once('_auth.php');
require_once('../config/lang.php');
require_once('../config/db.php');

$page_title = t('คำสั่งซื้อของฉัน', 'My Orders');
$mid = (int)$_SESSION['member_id'];

// Handle slip upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_slip'])) {
    member_csrf_verify();

    $interest_id = (int)($_POST['interest_id'] ?? 0);

    // Verify this interest belongs to this member
    $chk = db()->prepare("SELECT id, status FROM property_interests WHERE id = ? AND member_id = ? LIMIT 1");
    $chk->execute([$interest_id, $mid]);
    $row = $chk->fetch();

    if (!$row) {
        member_flash('error', t('ไม่พบคำสั่งซื้อ', 'Order not found'));
        header('Location: orders');
        exit;
    }

    if (empty($_FILES['slip']['tmp_name'])) {
        member_flash('error', t('กรุณาเลือกไฟล์สลิป', 'Please select a slip file'));
        header('Location: orders');
        exit;
    }

    $allowed_mime = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
    $allowed_ext  = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
    $max_size     = 5 * 1024 * 1024; // 5MB

    $file     = $_FILES['slip'];
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mime     = $finfo->file($file['tmp_name']);

    if ($file['size'] > $max_size) {
        member_flash('error', t('ไฟล์ต้องมีขนาดไม่เกิน 5MB', 'File must be under 5MB'));
        header('Location: orders');
        exit;
    }

    if (!in_array($mime, $allowed_mime, true) || !in_array($ext, $allowed_ext, true)) {
        member_flash('error', t('รองรับเฉพาะ JPG, PNG, WebP, PDF', 'Only JPG, PNG, WebP, PDF allowed'));
        header('Location: orders');
        exit;
    }

    $dir = realpath(__DIR__ . '/../assets/uploads/slips');
    $filename = $interest_id . '_' . time() . '.' . $ext;
    $dest = $dir . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        member_flash('error', t('อัปโหลดไม่สำเร็จ กรุณาลองใหม่', 'Upload failed, please try again'));
        header('Location: orders');
        exit;
    }

    // Remove old slip file if exists
    $old = db()->prepare("SELECT slip_filename FROM property_interests WHERE id = ? AND member_id = ?");
    $old->execute([$interest_id, $mid]);
    $old_file = $old->fetchColumn();
    if ($old_file && file_exists($dir . DIRECTORY_SEPARATOR . $old_file)) {
        unlink($dir . DIRECTORY_SEPARATOR . $old_file);
    }

    db()->prepare("UPDATE property_interests SET slip_filename = ? WHERE id = ?")->execute([$filename, $interest_id]);
    member_flash('success', t('แนบสลิปเรียบร้อยแล้ว', 'Slip uploaded successfully'));
    header('Location: orders');
    exit;
}

// Pagination
$per_page = 10;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $per_page;

$cnt = db()->prepare("SELECT COUNT(*) FROM property_interests WHERE member_id = ?");
$cnt->execute([$mid]);
$total = (int)$cnt->fetchColumn();

$stmt = db()->prepare("
    SELECT pi.*, p.title, p.title_en, p.token
    FROM property_interests pi
    JOIN properties p ON p.id = pi.property_id
    WHERE pi.member_id = ?
    ORDER BY pi.created_at DESC
    LIMIT {$per_page} OFFSET {$offset}
");
$stmt->execute([$mid]);
$orders = $stmt->fetchAll();

$status_label = [
    'pending'   => [t('รอยืนยัน', 'Pending'),   'bg-amber-100 text-amber-700'],
    'confirmed' => [t('ยืนยันแล้ว', 'Confirmed'), 'bg-green-100 text-green-700'],
    'contracted' => [t('เซ็นสัญญาแล้ว', 'Contract Signed'), 'bg-indigo-100 text-indigo-700'],
    'rejected'  => [t('ไม่อนุมัติ', 'Rejected'),  'bg-red-100 text-red-700'],
    'completed' => [t('สำเร็จ', 'Completed'),     'bg-blue-100 text-blue-700'],
];

include('_header.php');
?>

<?php if ($total === 0): ?>
<div class="text-center py-16 text-[#9d8f82]">
    <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    <p class="text-sm"><?= t('ยังไม่มีคำสั่งซื้อ', 'No orders yet') ?></p>
    <a href="/properties" class="inline-block mt-3 text-xs text-[#c9a96e] hover:text-[#b8965e] font-medium"><?= t('ดูทรัพย์สินทั้งหมด →', 'Browse properties →') ?></a>
</div>
<?php else: ?>
<div class="space-y-3">
<?php foreach ($orders as $o):
    $title    = (function_exists('lang') && lang() === 'en' && !empty($o['title_en'])) ? $o['title_en'] : $o['title'];
    [$sl, $sc] = $status_label[$o['status']] ?? [$o['status'], 'bg-gray-100 text-gray-600'];
    $has_slip = !empty($o['slip_filename']);
?>
<div class="bg-white rounded-xl border border-[#e8e4df] p-4 sm:p-5">

    <!-- Header row -->
    <div class="flex items-start justify-between gap-3 mb-3">
        <div class="min-w-0">
            <p class="text-[11px] text-[#9d8f82] mb-0.5"><?= date('d M Y', strtotime($o['created_at'])) ?></p>
            <p class="font-medium text-[#1a1714] text-sm leading-snug"><?= htmlspecialchars($title) ?></p>
            <?php if (!empty($o['transaction_id'])): ?>
            <p class="text-[10px] font-mono text-[#b8aa9e] mt-1 break-all select-all leading-relaxed"><?= htmlspecialchars($o['transaction_id']) ?></p>
            <?php endif; ?>
        </div>
        <span class="text-[11px] font-medium px-2.5 py-1 rounded-full whitespace-nowrap flex-shrink-0 <?= $sc ?>"><?= $sl ?></span>
    </div>

    <!-- Info grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-3 text-xs border-t border-[#f0ece7] pt-3">
        <div>
            <p class="text-[#9d8f82] mb-0.5"><?= t('ยอดชำระ', 'Amount') ?></p>
            <p class="font-semibold text-[#1a1714]"><?= number_format($o['amount_value']) ?> <span class="font-normal text-[#9d8f82]">(<?= $o['amount_percent'] ?>%)</span></p>
        </div>
        <div>
            <p class="text-[#9d8f82] mb-0.5"><?= t('ธนาคาร', 'Bank') ?></p>
            <p class="font-medium uppercase text-[#1a1714]"><?= htmlspecialchars($o['bank']) ?></p>
        </div>
        <div class="col-span-2 sm:col-span-1">
            <p class="text-[#9d8f82] mb-0.5"><?= t('สลิป', 'Slip') ?></p>
            <?php if ($has_slip): ?>
            <a href="slip?f=<?= urlencode($o['slip_filename']) ?>"
               target="_blank"
               class="inline-flex items-center gap-1 text-[#c9a96e] hover:text-[#b8965e] font-medium">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <?= t('ดูสลิป', 'View slip') ?>
            </a>
            <?php else: ?>
            <p class="text-[#b8aa9e]"><?= t('ยังไม่มีสลิป', 'Not uploaded') ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upload form -->
    <?php if (in_array($o['status'], ['pending', 'confirmed']) && !$has_slip): ?>
    <div class="border-t border-[#f0ece7] pt-3 mt-3">
        <p class="text-xs font-medium text-[#1a1714] mb-2"><?= t('แนบสลิปการโอนเงิน', 'Upload payment slip') ?></p>
        <form method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="csrf_token" value="<?= member_csrf_token() ?>">
            <input type="hidden" name="upload_slip" value="1">
            <input type="hidden" name="interest_id" value="<?= $o['id'] ?>">
            <label class="flex items-center gap-2 border border-[#e0dbd4] rounded-lg px-3 py-2 text-xs cursor-pointer hover:border-[#c9a96e] transition-colors bg-white">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <span id="fn-<?= $o['id'] ?>"><?= t('เลือกไฟล์', 'Choose file') ?></span>
                <input type="file" name="slip" accept=".jpg,.jpeg,.png,.webp,.pdf" class="hidden"
                       onchange="document.getElementById('fn-<?= $o['id'] ?>').textContent = this.files[0]?.name || '<?= t('เลือกไฟล์','Choose file') ?>'">
            </label>
            <button type="submit" class="bg-[#c9a96e] hover:bg-[#b8965e] text-white text-xs font-medium px-4 py-2 rounded-lg transition-colors">
                <?= t('ส่งสลิป', 'Submit slip') ?>
            </button>
            <p class="text-[10px] text-[#9d8f82] w-full">JPG, PNG, WebP, PDF · <?= t('ไม่เกิน 5MB', 'max 5MB') ?></p>
        </form>
    </div>
    <?php endif; ?>

</div>
<?php endforeach; ?>
</div>

<?= member_pagination($total, $per_page, $page, 'orders') ?>

<?php endif; ?>

<?php include('_footer.php'); ?>
