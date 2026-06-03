<?php
require_once('auth.php');
require_once('../config/db.php');

$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_new = $id === 0;
$page_title = $is_new ? 'เพิ่มบทความ' : 'แก้ไขบทความ';

$article   = [];
$points    = [];
$points_en = [];
$errors    = [];

if (!$is_new) {
    $article = db()->prepare('SELECT * FROM articles WHERE id = ?');
    $article->execute([$id]);
    $article = $article->fetch();
    if (!$article) {
        flash('error', 'ไม่พบบทความ');
        header('Location: articles');
        exit;
    }
    $points    = $article['points']    ? json_decode($article['points'],    true) : [];
    $points_en = $article['points_en'] ? json_decode($article['points_en'], true) : [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $icon       = trim($_POST['icon'] ?? 'file-text');
    $category   = trim($_POST['category'] ?? '');
    $title      = trim($_POST['title'] ?? '');
    $title_en   = trim($_POST['title_en'] ?? '');
    $excerpt    = trim($_POST['excerpt'] ?? '');
    $excerpt_en = trim($_POST['excerpt_en'] ?? '');
    $intro      = trim($_POST['intro'] ?? '');
    $intro_en   = trim($_POST['intro_en'] ?? '');
    $is_active  = isset($_POST['is_active']) ? 1 : 0;

    // Build TH points
    $p_labels  = $_POST['point_label'] ?? [];
    $p_details = $_POST['point_detail'] ?? [];
    $built_pts = [];
    for ($i = 0, $n = count($p_labels); $i < $n; $i++) {
        $lbl = trim($p_labels[$i] ?? '');
        $det = trim($p_details[$i] ?? '');
        if ($lbl !== '' || $det !== '') {
            $built_pts[] = ['label' => $lbl, 'detail' => $det];
        }
    }

    // Build EN points
    $p_labels_en  = $_POST['point_label_en'] ?? [];
    $p_details_en = $_POST['point_detail_en'] ?? [];
    $built_pts_en = [];
    for ($i = 0, $n = count($p_labels_en); $i < $n; $i++) {
        $lbl = trim($p_labels_en[$i] ?? '');
        $det = trim($p_details_en[$i] ?? '');
        if ($lbl !== '' || $det !== '') {
            $built_pts_en[] = ['label' => $lbl, 'detail' => $det];
        }
    }

    if ($title === '')    $errors[] = 'กรุณากรอกชื่อบทความ';
    if ($category === '') $errors[] = 'กรุณากรอกหมวดหมู่';

    if (empty($errors)) {
        $pts_json    = !empty($built_pts)    ? json_encode($built_pts,    JSON_UNESCAPED_UNICODE) : null;
        $pts_en_json = !empty($built_pts_en) ? json_encode($built_pts_en, JSON_UNESCAPED_UNICODE) : null;

        if ($is_new) {
            $stmt = db()->prepare(
                'INSERT INTO articles (icon, category, title, title_en, excerpt, excerpt_en, intro, intro_en, points, points_en, is_active)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?)'
            );
            $stmt->execute([$icon ?: 'file-text', $category, $title, $title_en ?: null, $excerpt ?: null, $excerpt_en ?: null, $intro ?: null, $intro_en ?: null, $pts_json, $pts_en_json, $is_active]);
        } else {
            $stmt = db()->prepare(
                'UPDATE articles SET icon=?, category=?, title=?, title_en=?, excerpt=?, excerpt_en=?, intro=?, intro_en=?, points=?, points_en=?, is_active=?
                 WHERE id=?'
            );
            $stmt->execute([$icon ?: 'file-text', $category, $title, $title_en ?: null, $excerpt ?: null, $excerpt_en ?: null, $intro ?: null, $intro_en ?: null, $pts_json, $pts_en_json, $is_active, $id]);
        }

        flash('success', $is_new ? 'เพิ่มบทความสำเร็จ' : 'บันทึกบทความสำเร็จ');
        header('Location: articles');
        exit;
    }

    // Re-populate on error
    $article  = $_POST;
    $points   = $built_pts;
    $points_en = $built_pts_en;
}

include('_header.php');
?>

<?php if (!empty($errors)): ?>
<div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-5">
    <?php foreach ($errors as $e): ?>
    <div>• <?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<form method="POST" class="space-y-5 max-w-3xl">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <!-- Language tab switcher -->
    <div class="flex items-center gap-3">
        <span class="text-xs font-medium text-gray-500">ภาษา / Language:</span>
        <div class="flex text-xs border border-gray-200 rounded-lg overflow-hidden">
            <button type="button" class="lang-tab-btn px-4 py-1.5 font-medium bg-[#c9a96e] text-white" data-lang="th">🇹🇭 TH</button>
            <button type="button" class="lang-tab-btn px-4 py-1.5 font-medium text-gray-500 hover:bg-gray-50" data-lang="en">🇬🇧 EN</button>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
        <h3 class="font-semibold text-gray-700 text-sm">ข้อมูลบทความ</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">หมวดหมู่ *</label>
                <input type="text" name="category" value="<?= htmlspecialchars($article['category'] ?? '') ?>"
                       placeholder="เช่น โรงแรม, คอนโด"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Icon (Feather icon name)</label>
                <input type="text" name="icon" value="<?= htmlspecialchars($article['icon'] ?? 'file-text') ?>"
                       placeholder="เช่น key, home, activity"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
            </div>
        </div>
        <!-- TH fields -->
        <div class="lang-panel space-y-4" data-lang="th">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">ชื่อบทความ *</label>
                <input type="text" name="title" value="<?= htmlspecialchars($article['title'] ?? '') ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">บทสรุป (Excerpt)</label>
                <textarea name="excerpt" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e] resize-none"><?= htmlspecialchars($article['excerpt'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">บทนำ (Intro)</label>
                <textarea name="intro" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e] resize-none"><?= htmlspecialchars($article['intro'] ?? '') ?></textarea>
            </div>
        </div>
        <!-- EN fields -->
        <div class="lang-panel hidden space-y-4" data-lang="en">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Article Title (EN)</label>
                <input type="text" name="title_en" value="<?= htmlspecialchars($article['title_en'] ?? '') ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Excerpt (EN)</label>
                <textarea name="excerpt_en" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e] resize-none"><?= htmlspecialchars($article['excerpt_en'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Intro (EN)</label>
                <textarea name="intro_en" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e] resize-none"><?= htmlspecialchars($article['intro_en'] ?? '') ?></textarea>
            </div>
        </div>
        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
            <input type="checkbox" name="is_active" value="1" <?= ($article['is_active'] ?? 1) ? 'checked' : '' ?>
                   class="w-4 h-4 rounded accent-[#c9a96e]">
            แสดงบนเว็บไซต์
        </label>
    </div>

    <!-- Points -->
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <!-- TH points panel -->
        <div class="lang-panel" data-lang="th">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-700 text-sm">ประเด็นหลัก (Points)</h3>
                <button type="button" onclick="addPoint('th')"
                        class="text-xs text-[#c9a96e] border border-[#c9a96e] px-3 py-1.5 rounded-lg hover:bg-[#c9a96e]/10 transition-colors">
                    + เพิ่ม
                </button>
            </div>
            <div id="points-list-th" class="space-y-4">
                <?php foreach ($points as $pt): ?>
                <div class="border border-gray-200 rounded-lg p-4 space-y-2 relative point-row">
                    <button type="button" onclick="this.closest('.point-row').remove()"
                            class="absolute top-2 right-2 text-gray-300 hover:text-red-500 text-lg leading-none">&times;</button>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">หัวข้อ</label>
                        <input type="text" name="point_label[]" value="<?= htmlspecialchars($pt['label'] ?? '') ?>"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">รายละเอียด</label>
                        <textarea name="point_detail[]" rows="2"
                                  class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e] resize-none"><?= htmlspecialchars($pt['detail'] ?? '') ?></textarea>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- EN points panel -->
        <div class="lang-panel hidden" data-lang="en">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-700 text-sm">Key Points (EN)</h3>
                <button type="button" onclick="addPoint('en')"
                        class="text-xs text-[#c9a96e] border border-[#c9a96e] px-3 py-1.5 rounded-lg hover:bg-[#c9a96e]/10 transition-colors">
                    + Add
                </button>
            </div>
            <div id="points-list-en" class="space-y-4">
                <?php $points_en_display = $points_en ?? []; foreach ($points_en_display as $pt): ?>
                <div class="border border-gray-200 rounded-lg p-4 space-y-2 relative point-row">
                    <button type="button" onclick="this.closest('.point-row').remove()"
                            class="absolute top-2 right-2 text-gray-300 hover:text-red-500 text-lg leading-none">&times;</button>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Heading</label>
                        <input type="text" name="point_label_en[]" value="<?= htmlspecialchars($pt['label'] ?? '') ?>"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Detail</label>
                        <textarea name="point_detail_en[]" rows="2"
                                  class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e] resize-none"><?= htmlspecialchars($pt['detail'] ?? '') ?></textarea>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="bg-[#c9a96e] hover:bg-[#b8965e] text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition-colors">
            บันทึก
        </button>
        <a href="articles" class="text-sm text-gray-500 hover:text-gray-700">ยกเลิก</a>
    </div>
</form>

<script>
function addPoint(lang) {
    const isEn = lang === 'en';
    const tpl = `<div class="border border-gray-200 rounded-lg p-4 space-y-2 relative point-row">
        <button type="button" onclick="this.closest('.point-row').remove()"
                class="absolute top-2 right-2 text-gray-300 hover:text-red-500 text-lg leading-none">&times;</button>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">${isEn ? 'Heading' : 'หัวข้อ'}</label>
            <input type="text" name="${isEn ? 'point_label_en[]' : 'point_label[]'}"
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">${isEn ? 'Detail' : 'รายละเอียด'}</label>
            <textarea name="${isEn ? 'point_detail_en[]' : 'point_detail[]'}" rows="2"
                      class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e] resize-none"></textarea>
        </div>
    </div>`;
    document.getElementById('points-list-' + lang).insertAdjacentHTML('beforeend', tpl);
}

// Global language tab switcher
document.querySelectorAll('.lang-tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const lang = btn.dataset.lang;
        document.querySelectorAll('.lang-tab-btn').forEach(b => {
            b.classList.toggle('bg-[#c9a96e]', b.dataset.lang === lang);
            b.classList.toggle('text-white',   b.dataset.lang === lang);
            b.classList.toggle('text-gray-500', b.dataset.lang !== lang);
        });
        document.querySelectorAll('.lang-panel').forEach(p => {
            p.classList.toggle('hidden', p.dataset.lang !== lang);
        });
    });
});
</script>

<?php include('_footer.php'); ?>
