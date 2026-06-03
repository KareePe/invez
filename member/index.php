<?php
require_once('_auth.php');
require_once('../config/lang.php');
require_once('../config/db.php');

$page_title = t('ภาพรวม','Overview');

$prop_counts = db()->prepare("SELECT approval_status, COUNT(*) as cnt FROM properties WHERE submitted_by=? GROUP BY approval_status");
$prop_counts->execute([$_SESSION['member_id']]);
$pc = array_column($prop_counts->fetchAll(PDO::FETCH_ASSOC), 'cnt', 'approval_status');

$art_counts = db()->prepare("SELECT approval_status, COUNT(*) as cnt FROM articles WHERE submitted_by=? GROUP BY approval_status");
$art_counts->execute([$_SESSION['member_id']]);
$ac = array_column($art_counts->fetchAll(PDO::FETCH_ASSOC), 'cnt', 'approval_status');

include('_header.php');
?>

<div class="mb-6">
    <p class="text-[#6b5f52] text-sm"><?= t('ยินดีต้อนรับ,','Welcome,') ?> <strong class="text-[#1a1714]"><?= htmlspecialchars($_SESSION['member_name']) ?></strong></p>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    <!-- Properties -->
    <div class="bg-white rounded-xl border border-[#e8e4df] p-5">
        <p class="text-xs text-[#9d8f82] mb-3"><?= t('ทรัพย์สินของคุณ','Your Properties') ?></p>
        <div class="flex gap-4 text-sm">
            <div><p class="text-xl font-semibold text-amber-500"><?= $pc['pending']  ?? 0 ?></p><p class="text-xs text-[#9d8f82]"><?= t('รอยืนยัน','Pending') ?></p></div>
            <div><p class="text-xl font-semibold text-green-600"><?= $pc['approved'] ?? 0 ?></p><p class="text-xs text-[#9d8f82]"><?= t('อนุมัติ','Approved') ?></p></div>
            <div><p class="text-xl font-semibold text-red-500"><?= $pc['rejected']  ?? 0 ?></p><p class="text-xs text-[#9d8f82]"><?= t('ปฏิเสธ','Rejected') ?></p></div>
        </div>
        <a href="properties" class="inline-block mt-4 text-xs text-[#c9a96e] hover:text-[#b8965e] font-medium"><?= t('จัดการทรัพย์สิน →','Manage Properties →') ?></a>
    </div>

    <!-- Articles -->
    <div class="bg-white rounded-xl border border-[#e8e4df] p-5">
        <p class="text-xs text-[#9d8f82] mb-3"><?= t('คอนเท้นของคุณ','Your Content') ?></p>
        <div class="flex gap-4 text-sm">
            <div><p class="text-xl font-semibold text-amber-500"><?= $ac['pending']  ?? 0 ?></p><p class="text-xs text-[#9d8f82]"><?= t('รอยืนยัน','Pending') ?></p></div>
            <div><p class="text-xl font-semibold text-green-600"><?= $ac['approved'] ?? 0 ?></p><p class="text-xs text-[#9d8f82]"><?= t('อนุมัติ','Approved') ?></p></div>
            <div><p class="text-xl font-semibold text-red-500"><?= $ac['rejected']  ?? 0 ?></p><p class="text-xs text-[#9d8f82]"><?= t('ปฏิเสธ','Rejected') ?></p></div>
        </div>
        <a href="articles" class="inline-block mt-4 text-xs text-[#c9a96e] hover:text-[#b8965e] font-medium"><?= t('จัดการคอนเท้น →','Manage Content →') ?></a>
    </div>

    <!-- Quick actions -->
    <div class="bg-white rounded-xl border border-[#e8e4df] p-5 flex flex-col gap-3">
        <p class="text-xs text-[#9d8f82]"><?= t('เพิ่มข้อมูลใหม่','Quick Add') ?></p>
        <a href="property-edit" class="flex items-center gap-2 text-sm text-[#1a1714] hover:text-[#c9a96e] transition-colors">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <?= t('+ ลงทรัพย์สิน','+ Submit Property') ?>
        </a>
        <a href="article-edit" class="flex items-center gap-2 text-sm text-[#1a1714] hover:text-[#c9a96e] transition-colors">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <?= t('+ ลงคอนเท้น','+ Submit Content') ?>
        </a>
    </div>
</div>

<?php include('_footer.php'); ?>
