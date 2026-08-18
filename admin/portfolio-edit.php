<?php
require_once('auth.php');
require_once('../config/db.php');

$id       = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_new   = $id === 0;
$page_title = $is_new ? 'เพิ่มผลงาน' : 'แก้ไขผลงาน';

$portfolio = [];
$images    = [];
$errors    = [];

if (!$is_new) {
    $portfolio = db()->prepare('SELECT * FROM portfolios WHERE id = ?');
    $portfolio->execute([$id]);
    $portfolio = $portfolio->fetch();
    if (!$portfolio) {
        flash('error', 'ไม่พบผลงาน');
        header('Location: portfolios');
        exit;
    }
    $images = db()->prepare('SELECT * FROM portfolio_images WHERE portfolio_id = ? ORDER BY sort_order ASC');
    $images->execute([$id]);
    $images = $images->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $category       = trim($_POST['category'] ?? '');
    $category_en    = trim($_POST['category_en'] ?? '');
    $title          = trim($_POST['title'] ?? '');
    $title_en       = trim($_POST['title_en'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $description_en = trim($_POST['description_en'] ?? '');
    $sort_order     = (int)($_POST['sort_order'] ?? 0);
    $is_active      = isset($_POST['is_active']) ? 1 : 0;

    if ($title === '')    $errors[] = 'กรุณากรอกหัวข้อผลงาน';
    if ($category === '') $errors[] = 'กรุณากรอกหมวดหมู่';

    if (empty($errors)) {
        $fields = [
            $category, $category_en ?: null,
            $title, $title_en ?: null,
            $description ?: null, $description_en ?: null,
            $sort_order, $is_active,
        ];

        if ($is_new) {
            $stmt = db()->prepare(
                'INSERT INTO portfolios
                 (category, category_en, title, title_en, description, description_en, sort_order, is_active)
                 VALUES (?,?,?,?,?,?,?,?)'
            );
            $stmt->execute($fields);
            $id = (int)db()->lastInsertId();
        } else {
            $stmt = db()->prepare(
                'UPDATE portfolios SET
                 category=?, category_en=?, title=?, title_en=?, description=?, description_en=?,
                 sort_order=?, is_active=?
                 WHERE id=?'
            );
            $stmt->execute(array_merge($fields, [$id]));
        }

        // Delete marked images
        $to_delete = array_map('intval', $_POST['delete_images'] ?? []);
        foreach ($to_delete as $img_id) {
            $row = db()->prepare('SELECT filename FROM portfolio_images WHERE id = ? AND portfolio_id = ?');
            $row->execute([$img_id, $id]);
            $row = $row->fetch();
            if ($row) {
                $path = dirname(__DIR__) . '/assets/images/portfolios/' . $id . '/' . $row['filename'];
                if (file_exists($path)) unlink($path);
                db()->prepare('DELETE FROM portfolio_images WHERE id = ?')->execute([$img_id]);
            }
        }

        // Upload new images
        $upload_dir = dirname(__DIR__) . '/assets/images/portfolios/' . $id . '/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $new_files = $_FILES['new_images'] ?? [];
        if (!empty($new_files['name'][0])) {
            $allowed_mime = ['image/jpeg', 'image/png', 'image/webp'];
            $ext_map      = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $max_sort     = db()->prepare('SELECT COALESCE(MAX(sort_order),0) FROM portfolio_images WHERE portfolio_id = ?');
            $max_sort->execute([$id]);
            $next_sort = (int)$max_sort->fetchColumn() + 1;

            $img_stmt = db()->prepare('INSERT INTO portfolio_images (portfolio_id, filename, sort_order) VALUES (?,?,?)');
            $count    = count($new_files['name']);

            for ($i = 0; $i < $count; $i++) {
                if ($new_files['error'][$i] !== UPLOAD_ERR_OK) continue;
                if ($new_files['size'][$i] > 5 * 1024 * 1024) continue;

                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime  = $finfo->file($new_files['tmp_name'][$i]);
                if (!in_array($mime, $allowed_mime, true)) continue;

                $filename = uniqid('img_') . '.' . $ext_map[$mime];
                if (move_uploaded_file($new_files['tmp_name'][$i], $upload_dir . $filename)) {
                    $img_stmt->execute([$id, $filename, $next_sort++]);
                }
            }
        }

        log_admin_activity($is_new ? 'create' : 'update', 'portfolio', $id, $title);
        flash('success', $is_new ? 'เพิ่มผลงานสำเร็จ' : 'บันทึกผลงานสำเร็จ');
        header('Location: portfolios');
        exit;
    }

    $portfolio = array_merge($_POST, ['id' => $id]);
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

<form method="POST" data-loading="button" enctype="multipart/form-data" class="space-y-6 max-w-3xl">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <!-- Language tab switcher -->
    <div class="flex items-center gap-3">
        <span class="text-xs font-medium text-gray-500">ภาษา / Language:</span>
        <div class="flex text-xs border border-gray-200 rounded-lg overflow-hidden">
            <button type="button" class="lang-tab-btn px-4 py-1.5 font-medium bg-[#c9a96e] text-white" data-lang="th">TH</button>
            <button type="button" class="lang-tab-btn px-4 py-1.5 font-medium text-gray-500 hover:bg-gray-50" data-lang="en">EN</button>
        </div>
    </div>

    <!-- Basic info -->
    <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
        <h3 class="font-semibold text-gray-700 text-sm">ข้อมูลผลงาน</h3>

        <!-- TH -->
        <div class="lang-panel space-y-4" data-lang="th">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">หมวดหมู่ *</label>
                <input type="text" name="category" value="<?= htmlspecialchars($portfolio['category'] ?? '') ?>"
                       placeholder="เช่น อสังหาริมทรัพย์, โรงแรม"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">หัวข้อผลงาน *</label>
                <input type="text" name="title" value="<?= htmlspecialchars($portfolio['title'] ?? '') ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">รายละเอียดผลงาน</label>
                <textarea name="description" rows="4"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e] resize-none"><?= htmlspecialchars($portfolio['description'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- EN -->
        <div class="lang-panel hidden space-y-4" data-lang="en">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Category (EN)</label>
                <input type="text" name="category_en" value="<?= htmlspecialchars($portfolio['category_en'] ?? '') ?>"
                       placeholder="e.g. Real Estate, Hotel"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Title (EN)</label>
                <input type="text" name="title_en" value="<?= htmlspecialchars($portfolio['title_en'] ?? '') ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Description (EN)</label>
                <textarea name="description_en" rows="4"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e] resize-none"><?= htmlspecialchars($portfolio['description_en'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Display settings -->
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-700 text-sm mb-3">การแสดงผล</h3>
        <div class="flex items-center gap-6">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">ลำดับการแสดง</label>
                <input type="number" name="sort_order" value="<?= (int)($portfolio['sort_order'] ?? 0) ?>"
                       class="w-24 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-700 mt-5 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" <?= ($portfolio['is_active'] ?? 1) ? 'checked' : '' ?>
                       class="w-4 h-4 rounded accent-[#c9a96e]">
                แสดงบนเว็บไซต์
            </label>
        </div>
    </div>

    <!-- Images -->
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-700 text-sm mb-4">รูปภาพ</h3>

        <?php if (!empty($images)): ?>
        <div class="grid grid-cols-4 gap-3 mb-4">
            <?php foreach ($images as $img): ?>
            <div class="relative group">
                <img src="<?= $_base_path ?? '' ?>/assets/images/portfolios/<?= $id ?>/<?= htmlspecialchars($img['filename']) ?>"
                     class="w-full aspect-square object-cover rounded-lg border border-gray-200" alt="">
                <label class="absolute top-1.5 right-1.5 bg-white/90 rounded-full w-6 h-6 flex items-center justify-center cursor-pointer shadow-sm border border-gray-200 group-hover:border-red-300"
                       title="ลบรูปนี้">
                    <input type="checkbox" name="delete_images[]" value="<?= $img['id'] ?>"
                           class="sr-only peer">
                    <span class="text-gray-400 peer-checked:text-red-500 text-xs leading-none font-bold">&times;</span>
                </label>
            </div>
            <?php endforeach; ?>
        </div>
        <p class="text-xs text-gray-400 mb-3">คลิก × เพื่อทำเครื่องหมายลบ (จะลบเมื่อกด บันทึก)</p>
        <?php endif; ?>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">อัปโหลดรูปใหม่ (JPG, PNG, WebP — สูงสุด 5MB ต่อรูป)</label>
            <input type="file" name="new_images[]" multiple accept="image/jpeg,image/png,image/webp"
                   class="text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-[#c9a96e]/10 file:text-[#c9a96e] hover:file:bg-[#c9a96e]/20">
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="bg-[#c9a96e] hover:bg-[#b8965e] text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition-colors">
            บันทึก
        </button>
        <a href="portfolios" class="text-sm text-gray-500 hover:text-gray-700">ยกเลิก</a>
    </div>
</form>

<script>
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
