<?php
require_once('_auth.php');
require_once('../config/lang.php');
require_once('../config/db.php');

$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_new = $id === 0;

// Handle delete
if (isset($_GET['delete']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    member_csrf_verify();
    $del_id = (int)($_POST['id'] ?? 0);
    $check  = db()->prepare('SELECT id FROM articles WHERE id=? AND submitted_by=? AND approval_status=\'pending\'');
    $check->execute([$del_id, $_SESSION['member_id']]);
    if ($check->fetch()) {
        db()->prepare('DELETE FROM articles WHERE id=?')->execute([$del_id]);
        member_flash('success', t('ลบสำเร็จ','Deleted successfully'));
    }
    header('Location: articles');
    exit;
}

$page_title = $is_new ? t('ลงคอนเท้น','Submit Content') : t('แก้ไขคอนเท้น','Edit Content');

$article = [];
$points  = [];
$errors  = [];

if (!$is_new) {
    $stmt = db()->prepare('SELECT * FROM articles WHERE id=? AND submitted_by=? AND approval_status=\'pending\'');
    $stmt->execute([$id, $_SESSION['member_id']]);
    $article = $stmt->fetch();
    if (!$article) {
        member_flash('error', t('ไม่พบข้อมูลหรือไม่สามารถแก้ไขได้','Not found or cannot be edited'));
        header('Location: articles');
        exit;
    }
    $points = $article['points'] ? json_decode($article['points'], true) : [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['delete'])) {
    member_csrf_verify();

    $category  = trim($_POST['category'] ?? '');
    $icon      = trim($_POST['icon']     ?? 'file-text');
    $title     = trim($_POST['title']    ?? '');
    $excerpt   = trim($_POST['excerpt']  ?? '');
    $intro     = trim($_POST['intro']    ?? '');

    $p_labels  = $_POST['point_label']  ?? [];
    $p_details = $_POST['point_detail'] ?? [];
    $built_pts = [];
    for ($i = 0, $n = count($p_labels); $i < $n; $i++) {
        $lbl = trim($p_labels[$i] ?? '');
        $det = trim($p_details[$i] ?? '');
        if ($lbl !== '' || $det !== '') $built_pts[] = ['label'=>$lbl,'detail'=>$det];
    }

    if ($title === '')    $errors[] = t('กรุณากรอกชื่อบทความ','Please enter article title');
    if ($category === '') $errors[] = t('กรุณากรอกหมวดหมู่','Please enter category');

    if (empty($errors)) {
        $pts_json = !empty($built_pts) ? json_encode($built_pts, JSON_UNESCAPED_UNICODE) : null;

        if ($is_new) {
            db()->prepare(
                'INSERT INTO articles (icon,category,title,excerpt,intro,points,is_active,submitted_by,approval_status)
                 VALUES (?,?,?,?,?,?,0,?,\'pending\')'
            )->execute([$icon ?: 'file-text', $category, $title, $excerpt ?: null, $intro ?: null, $pts_json, $_SESSION['member_id']]);
        } else {
            db()->prepare(
                'UPDATE articles SET icon=?,category=?,title=?,excerpt=?,intro=?,points=? WHERE id=? AND submitted_by=?'
            )->execute([$icon ?: 'file-text', $category, $title, $excerpt ?: null, $intro ?: null, $pts_json, $id, $_SESSION['member_id']]);
        }

        member_flash('success', $is_new ? t('ส่งคอนเท้นสำเร็จ รอการยืนยัน','Content submitted, awaiting approval') : t('บันทึกเรียบร้อย','Saved'));
        header('Location: articles');
        exit;
    }

    $article = $_POST;
    $points  = $built_pts;
}

include('_header.php');
?>

<?php if (!empty($errors)): ?>
<div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-5 space-y-1">
    <?php foreach ($errors as $e): ?><div>• <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
</div>
<?php endif; ?>

<form method="POST" data-loading="button" class="space-y-5 max-w-2xl">
    <input type="hidden" name="csrf_token" value="<?= member_csrf_token() ?>">

    <div class="bg-white rounded-xl border border-[#e8e4df] p-5 space-y-4">
        <h3 class="font-semibold text-[#1a1714] text-sm"><?= t('ข้อมูลบทความ','Article Info') ?></h3>

        <?php
        $feather_icons = [
            'file-text','key','home','activity','book-open','layers','tool',
            'package','map','briefcase','star','music','smile','radio','anchor',
            'navigation','git-merge','edit-3','sun','feather','flag','truck',
            'battery-charging','zap','shopping-bag','monitor','camera','server',
            'trending-up','dollar-sign','bar-chart-2','globe','heart','shield',
            'award','users','tag','cpu','database','building',
        ];
        $cur_icon = htmlspecialchars($article['icon'] ?? 'file-text');
        ?>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('หมวดหมู่','Category') ?> *</label>
                <input type="text" name="category" value="<?= htmlspecialchars($article['category'] ?? '') ?>"
                       placeholder="<?= t('เช่น โรงแรม, คอนโด','e.g. Hotel, Condo') ?>"
                       class="w-full border border-[#e0dbd4] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
            </div>
            <div class="relative">
                <label class="block text-xs font-medium text-[#6b5f52] mb-1.5">Icon</label>
                <input type="hidden" name="icon" id="icon-value" value="<?= $cur_icon ?>">
                <button type="button" id="icon-picker-btn"
                        class="flex items-center gap-2 border border-[#e0dbd4] rounded-lg px-3 py-2 text-sm w-full bg-white hover:border-[#c9a96e] transition-colors text-left">
                    <i data-feather="<?= $cur_icon ?>" class="w-4 h-4 flex-shrink-0"></i>
                    <span id="icon-label" class="flex-1 truncate"><?= $cur_icon ?></span>
                    <svg class="w-3.5 h-3.5 text-[#9d8f82] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div id="icon-dropdown"
                     class="hidden absolute z-30 mt-1 left-0 bg-white border border-[#e8e4df] rounded-xl shadow-lg p-3"
                     style="width:320px">
                    <div class="grid grid-cols-8 gap-1">
                        <?php foreach ($feather_icons as $ic): ?>
                        <button type="button" data-icon="<?= $ic ?>"
                                title="<?= $ic ?>"
                                class="icon-opt flex items-center justify-center w-9 h-9 rounded-lg hover:bg-[#fdf6e8] transition-colors <?= $ic === ($article['icon'] ?? 'file-text') ? 'bg-[#fdf6e8] ring-1 ring-[#c9a96e]' : '' ?>">
                            <i data-feather="<?= $ic ?>" class="w-4 h-4"></i>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('ชื่อบทความ','Title') ?> *</label>
            <input type="text" name="title" value="<?= htmlspecialchars($article['title'] ?? '') ?>"
                   class="w-full border border-[#e0dbd4] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
        </div>

        <div>
            <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('บทสรุป (Excerpt)','Excerpt') ?></label>
            <textarea name="excerpt" rows="2"
                      class="w-full border border-[#e0dbd4] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e] resize-none"><?= htmlspecialchars($article['excerpt'] ?? '') ?></textarea>
        </div>

        <div>
            <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('บทนำ (Intro)','Introduction') ?></label>
            <textarea name="intro" rows="4"
                      class="w-full border border-[#e0dbd4] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e] resize-none"><?= htmlspecialchars($article['intro'] ?? '') ?></textarea>
        </div>
    </div>

    <!-- Key Points -->
    <div class="bg-white rounded-xl border border-[#e8e4df] p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-[#1a1714] text-sm"><?= t('ประเด็นหลัก','Key Points') ?></h3>
            <button type="button" onclick="addPoint()"
                    class="text-xs text-[#c9a96e] border border-[#c9a96e] px-3 py-1.5 rounded-lg hover:bg-[#c9a96e]/10 transition-colors">
                + <?= t('เพิ่ม','Add') ?>
            </button>
        </div>
        <div id="points-list" class="space-y-4">
            <?php foreach ($points as $pt): ?>
            <div class="border border-[#e8e4df] rounded-lg p-4 space-y-2 relative point-row">
                <button type="button" onclick="this.closest('.point-row').remove()"
                        class="absolute top-2 right-2 text-[#9d8f82] hover:text-red-500 text-lg leading-none">&times;</button>
                <div>
                    <label class="block text-xs font-medium text-[#9d8f82] mb-1"><?= t('หัวข้อ','Heading') ?></label>
                    <input type="text" name="point_label[]" value="<?= htmlspecialchars($pt['label'] ?? '') ?>"
                           class="w-full border border-[#e0dbd4] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[#9d8f82] mb-1"><?= t('รายละเอียด','Detail') ?></label>
                    <textarea name="point_detail[]" rows="2"
                              class="w-full border border-[#e0dbd4] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e] resize-none"><?= htmlspecialchars($pt['detail'] ?? '') ?></textarea>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="bg-[#c9a96e] hover:bg-[#b8965e] text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition-colors">
            <?= $is_new ? t('ส่งรอยืนยัน','Submit for Approval') : t('บันทึก','Save') ?>
        </button>
        <a href="articles" class="text-sm text-[#9d8f82] hover:text-[#6b5f52]"><?= t('ยกเลิก','Cancel') ?></a>
    </div>
</form>

<script>
function addPoint() {
    const tpl = `<div class="border border-[#e8e4df] rounded-lg p-4 space-y-2 relative point-row">
        <button type="button" onclick="this.closest('.point-row').remove()" class="absolute top-2 right-2 text-[#9d8f82] hover:text-red-500 text-lg leading-none">&times;</button>
        <div><label class="block text-xs font-medium text-[#9d8f82] mb-1"><?= t('หัวข้อ','Heading') ?></label>
        <input type="text" name="point_label[]" class="w-full border border-[#e0dbd4] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]"></div>
        <div><label class="block text-xs font-medium text-[#9d8f82] mb-1"><?= t('รายละเอียด','Detail') ?></label>
        <textarea name="point_detail[]" rows="2" class="w-full border border-[#e0dbd4] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e] resize-none"></textarea></div>
    </div>`;
    document.getElementById('points-list').insertAdjacentHTML('beforeend', tpl);
}
</script>

<script src="https://unpkg.com/feather-icons"></script>
<script>
feather.replace({ width: 16, height: 16 });

const btn      = document.getElementById('icon-picker-btn');
const dropdown = document.getElementById('icon-dropdown');
const hidden   = document.getElementById('icon-value');
const label    = document.getElementById('icon-label');

btn.addEventListener('click', (e) => {
    e.stopPropagation();
    dropdown.classList.toggle('hidden');
});

document.addEventListener('click', () => dropdown.classList.add('hidden'));
dropdown.addEventListener('click', (e) => e.stopPropagation());

document.querySelectorAll('.icon-opt').forEach(opt => {
    opt.addEventListener('click', () => {
        const ic = opt.dataset.icon;
        hidden.value = ic;
        label.textContent = ic;
        const iconEl = btn.querySelector('svg:first-child, i[data-feather]');
        if (iconEl) {
            iconEl.outerHTML = `<i data-feather="${ic}" class="w-4 h-4 flex-shrink-0"></i>`;
            feather.replace({ width: 16, height: 16 });
        }
        document.querySelectorAll('.icon-opt').forEach(o => {
            o.classList.remove('bg-[#fdf6e8]', 'ring-1', 'ring-[#c9a96e]');
        });
        opt.classList.add('bg-[#fdf6e8]', 'ring-1', 'ring-[#c9a96e]');
        dropdown.classList.add('hidden');
    });
});
</script>

<?php include('_footer.php'); ?>
