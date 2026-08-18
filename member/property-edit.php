<?php
require_once('_auth.php');
require_once('../config/lang.php');
require_once('../config/db.php');
require_once('../properties_data.php');

$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_new = $id === 0;

// Handle delete
if (isset($_GET['delete']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    member_csrf_verify();
    $del_id = (int)($_POST['id'] ?? 0);
    $check  = db()->prepare("SELECT id FROM properties WHERE id=? AND submitted_by=? AND approval_status='pending'");
    $check->execute([$del_id, $_SESSION['member_id']]);
    if ($check->fetch()) {
        $imgs = db()->prepare('SELECT filename FROM property_images WHERE property_id=?');
        $imgs->execute([$del_id]);
        foreach ($imgs->fetchAll() as $img) {
            $path = dirname(__DIR__) . '/assets/images/properties/' . $del_id . '/' . $img['filename'];
            if (file_exists($path)) unlink($path);
        }
        @rmdir(dirname(__DIR__) . '/assets/images/properties/' . $del_id);
        db()->prepare('DELETE FROM property_images WHERE property_id=?')->execute([$del_id]);
        db()->prepare('DELETE FROM property_highlights WHERE property_id=?')->execute([$del_id]);
        db()->prepare('DELETE FROM properties WHERE id=?')->execute([$del_id]);
        member_flash('success', t('ลบสำเร็จ','Deleted successfully'));
    }
    header('Location: properties');
    exit;
}

$page_title = $is_new ? t('ลงทรัพย์สิน','Submit Property') : t('แก้ไขทรัพย์สิน','Edit Property');
$prop       = [];
$highlights = [];
$images     = [];
$errors     = [];

if (!$is_new) {
    $stmt = db()->prepare("SELECT * FROM properties WHERE id=? AND submitted_by=? AND approval_status='pending'");
    $stmt->execute([$id, $_SESSION['member_id']]);
    $prop = $stmt->fetch();
    if (!$prop) {
        member_flash('error', t('ไม่พบข้อมูลหรือไม่สามารถแก้ไขได้','Not found or cannot be edited'));
        header('Location: properties');
        exit;
    }
    $highlights = db()->prepare('SELECT * FROM property_highlights WHERE property_id=? ORDER BY sort_order ASC');
    $highlights->execute([$id]);
    $highlights = $highlights->fetchAll();

    $images = db()->prepare('SELECT * FROM property_images WHERE property_id=? ORDER BY sort_order ASC');
    $images->execute([$id]);
    $images = $images->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['delete'])) {
    member_csrf_verify();

    $category    = trim($_POST['category']    ?? '');
    $title       = trim($_POST['title']       ?? '');
    $subtitle    = trim($_POST['subtitle']    ?? '');
    $price       = $_POST['price'] !== '' ? (int)$_POST['price'] : null;
    $price_display = trim($_POST['price_display'] ?? '');
    // Deposit ratio shown on the checkout page. Empty or out of range = not set,
    // and checkout falls back to its own 10% default.
    $deposit_percent = $_POST['deposit_percent'] !== '' ? (int)$_POST['deposit_percent'] : null;
    if ($deposit_percent !== null && ($deposit_percent < 0 || $deposit_percent > 100)) {
        $deposit_percent = null;
    }
    $location    = trim($_POST['location']    ?? '');
    $location_short = trim($_POST['location_short'] ?? '');
    $land_area   = trim($_POST['land_area']   ?? '');
    $usable_area = trim($_POST['usable_area'] ?? '');
    $floors      = trim($_POST['floors']      ?? '');
    $beds        = $_POST['beds']    !== '' ? (int)$_POST['beds']    : null;
    $bathrooms   = $_POST['bathrooms'] !== '' ? (int)$_POST['bathrooms'] : null;
    $parking     = trim($_POST['parking']  ?? '');
    $offices     = $_POST['offices'] !== '' ? (int)$_POST['offices'] : null;
    $status      = trim($_POST['status']   ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '')    $errors[] = t('กรุณากรอกชื่อทรัพย์สิน','Please enter property title');
    if ($category === '') $errors[] = t('กรุณาเลือกหมวดหมู่','Please select a category');

    if (empty($errors)) {
        $fields = [
            $category, $title, $subtitle ?: null, $price, $price_display ?: null, $deposit_percent,
            $location ?: null, $location_short ?: null,
            $land_area ?: null, $usable_area ?: null, $floors ?: null,
            $beds, $bathrooms, $parking ?: null, $offices,
            $status ?: null, $description ?: null,
        ];

        if ($is_new) {
            db()->prepare(
                'INSERT INTO properties
                 (category,title,subtitle,price,price_display,deposit_percent,location,location_short,
                  land_area,usable_area,floors,beds,bathrooms,parking,offices,
                  status,description,is_active,submitted_by,approval_status)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,0,?,\'pending\')'
            )->execute(array_merge($fields, [$_SESSION['member_id']]));
            $id = (int)db()->lastInsertId();
        } else {
            db()->prepare(
                'UPDATE properties SET
                 category=?,title=?,subtitle=?,price=?,price_display=?,deposit_percent=?,location=?,location_short=?,
                 land_area=?,usable_area=?,floors=?,beds=?,bathrooms=?,parking=?,offices=?,
                 status=?,description=?
                 WHERE id=? AND submitted_by=?'
            )->execute(array_merge($fields, [$id, $_SESSION['member_id']]));
        }

        // Highlights
        db()->prepare('DELETE FROM property_highlights WHERE property_id=?')->execute([$id]);
        $raw_hl  = array_filter(array_map('trim', $_POST['highlights'] ?? []), fn($v) => $v !== '');
        $hl_stmt = db()->prepare('INSERT INTO property_highlights (property_id,content,sort_order) VALUES (?,?,?)');
        foreach (array_values($raw_hl) as $i => $hl) $hl_stmt->execute([$id, $hl, $i + 1]);

        // Delete marked images
        foreach (array_map('intval', $_POST['delete_images'] ?? []) as $img_id) {
            $row = db()->prepare('SELECT filename FROM property_images WHERE id=? AND property_id=?');
            $row->execute([$img_id, $id]);
            $row = $row->fetch();
            if ($row) {
                $path = dirname(__DIR__) . '/assets/images/properties/' . $id . '/' . $row['filename'];
                if (file_exists($path)) unlink($path);
                db()->prepare('DELETE FROM property_images WHERE id=?')->execute([$img_id]);
            }
        }

        // Upload new images
        $upload_dir = dirname(__DIR__) . '/assets/images/properties/' . $id . '/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $new_files = $_FILES['new_images'] ?? [];
        if (!empty($new_files['name'][0])) {
            $allowed_mime = ['image/jpeg','image/png','image/webp'];
            $ext_map      = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
            $max_sort = db()->prepare('SELECT COALESCE(MAX(sort_order),0) FROM property_images WHERE property_id=?');
            $max_sort->execute([$id]);
            $next_sort = (int)$max_sort->fetchColumn() + 1;
            $img_stmt  = db()->prepare('INSERT INTO property_images (property_id,filename,sort_order) VALUES (?,?,?)');
            $count     = count($new_files['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($new_files['error'][$i] !== UPLOAD_ERR_OK || $new_files['size'][$i] > 5*1024*1024) continue;
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime  = $finfo->file($new_files['tmp_name'][$i]);
                if (!in_array($mime, $allowed_mime, true)) continue;
                $filename = uniqid('img_') . '.' . $ext_map[$mime];
                if (move_uploaded_file($new_files['tmp_name'][$i], $upload_dir . $filename))
                    $img_stmt->execute([$id, $filename, $next_sort++]);
            }
        }

        member_flash('success', $is_new ? t('ส่งทรัพย์สินสำเร็จ รอการยืนยัน','Property submitted, awaiting approval') : t('บันทึกเรียบร้อย','Saved'));
        header('Location: properties');
        exit;
    }

    $prop       = array_merge($_POST, ['id' => $id]);
    $highlights = array_map(fn($v) => ['content'=>$v], $_POST['highlights'] ?? []);
}

include('_header.php');
?>

<?php if (!empty($errors)): ?>
<div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-5 space-y-1">
    <?php foreach ($errors as $e): ?><div>• <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
</div>
<?php endif; ?>

<form method="POST" data-loading="button" enctype="multipart/form-data" class="space-y-5" id="property-form">
    <input type="hidden" name="csrf_token" value="<?= member_csrf_token() ?>">

    <div class="grid md:grid-cols-2 gap-5">

        <!-- Left -->
        <div class="space-y-5">
            <div class="bg-white rounded-xl border border-[#e8e4df] p-5 space-y-4">
                <h3 class="font-semibold text-[#1a1714] text-sm"><?= t('ข้อมูลพื้นฐาน','Basic Info') ?></h3>
                <div>
                    <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('หมวดหมู่','Category') ?> *</label>
                    <select name="category" class="w-full border border-[#e0dbd4] rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
                        <option value="">— <?= t('เลือกหมวดหมู่','Select category') ?> —</option>
                        <?php foreach ($property_categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($prop['category'] ?? '') === $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars(is_en() ? ($cat['en'] ?? $cat['label']) : $cat['label']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('ชื่อทรัพย์สิน','Title') ?> *</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($prop['title'] ?? '') ?>"
                           class="w-full border border-[#e0dbd4] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-[#6b5f52] mb-1.5">Subtitle</label>
                    <input type="text" name="subtitle" value="<?= htmlspecialchars($prop['subtitle'] ?? '') ?>"
                           class="w-full border border-[#e0dbd4] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('สถานะ','Status') ?></label>
                    <input type="text" name="status" value="<?= htmlspecialchars($prop['status'] ?? '') ?>"
                           placeholder="<?= t('เช่น พร้อมขาย','e.g. For Sale') ?>"
                           class="w-full border border-[#e0dbd4] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                </div>
            </div>

            <div class="bg-white rounded-xl border border-[#e8e4df] p-5 space-y-4">
                <h3 class="font-semibold text-[#1a1714] text-sm"><?= t('ราคา','Price') ?></h3>
                <div>
                    <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('ราคา (บาท)','Price (THB)') ?></label>
                    <input type="number" name="price" value="<?= $prop['price'] ?? '' ?>"
                           class="w-full border border-[#e0dbd4] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('ราคาที่แสดง','Display Price') ?></label>
                    <input type="text" name="price_display" value="<?= htmlspecialchars($prop['price_display'] ?? '') ?>"
                           placeholder="<?= t('เช่น 15 ล้านบาท','e.g. 15 million THB') ?>"
                           class="w-full border border-[#e0dbd4] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('สัดส่วนมัดจำ (%)','Deposit Percentage (%)') ?></label>
                    <input type="number" name="deposit_percent" min="0" max="100" step="1"
                           value="<?= htmlspecialchars((string)($prop['deposit_percent'] ?? '')) ?>"
                           placeholder="10"
                           class="w-full border border-[#e0dbd4] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                    <p class="text-xs text-[#9d8f82] mt-1"><?= t('ใช้เป็นค่าเริ่มต้นของสัดส่วนมัดจำในหน้าชำระเงิน หากไม่ระบุจะใช้ 10%','Used as the default deposit percentage on the checkout page. Defaults to 10% if left blank.') ?></p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-[#e8e4df] p-5 space-y-4">
                <h3 class="font-semibold text-[#1a1714] text-sm"><?= t('ที่ตั้ง','Location') ?></h3>
                <div>
                    <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('ที่อยู่เต็ม','Full Address') ?></label>
                    <textarea name="location" rows="2"
                              class="w-full border border-[#e0dbd4] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e] resize-none"><?= htmlspecialchars($prop['location'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('ที่ตั้งสั้น','Short Location') ?></label>
                    <input type="text" name="location_short" value="<?= htmlspecialchars($prop['location_short'] ?? '') ?>"
                           placeholder="<?= t('เช่น ตลิ่งชัน, กรุงเทพฯ','e.g. Bangkok') ?>"
                           class="w-full border border-[#e0dbd4] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                </div>
            </div>
        </div>

        <!-- Right -->
        <div class="space-y-5">
            <div class="bg-white rounded-xl border border-[#e8e4df] p-5">
                <h3 class="font-semibold text-[#1a1714] text-sm mb-4"><?= t('ขนาดและรายละเอียด','Specs') ?></h3>
                <div class="grid grid-cols-2 gap-3">
                    <?php
                    $spec_fields = [
                        ['land_area',    t('ที่ดิน','Land Area'),       'text',   '84 ตร.ว.'],
                        ['usable_area',  t('พื้นที่ใช้สอย','Usable Area'), 'text',   '302 ตร.ม.'],
                        ['floors',       t('จำนวนชั้น','Floors'),       'text',   '2 ชั้น'],
                        ['beds',         t('ห้องนอน','Bedrooms'),       'number', ''],
                        ['bathrooms',    t('ห้องน้ำ','Bathrooms'),       'number', ''],
                        ['offices',      t('ห้องสำนักงาน','Offices'),    'number', ''],
                        ['parking',      t('จอดรถ','Parking'),          'text',   '2 คัน'],
                    ];
                    foreach ($spec_fields as [$name, $label, $type, $ph]):
                    ?>
                    <div>
                        <label class="block text-xs font-medium text-[#6b5f52] mb-1"><?= $label ?></label>
                        <input type="<?= $type ?>" name="<?= $name ?>"
                               value="<?= htmlspecialchars((string)($prop[$name] ?? '')) ?>"
                               placeholder="<?= $ph ?>"
                               class="w-full border border-[#e0dbd4] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-[#e8e4df] p-5">
                <h3 class="font-semibold text-[#1a1714] text-sm mb-3"><?= t('รายละเอียด','Description') ?></h3>
                <textarea name="description" rows="5"
                          class="w-full border border-[#e0dbd4] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e] resize-none"><?= htmlspecialchars($prop['description'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Highlights -->
    <div class="bg-white rounded-xl border border-[#e8e4df] p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-[#1a1714] text-sm"><?= t('จุดเด่น','Highlights') ?></h3>
            <button type="button" onclick="addHighlight()"
                    class="text-xs text-[#c9a96e] border border-[#c9a96e] px-3 py-1.5 rounded-lg hover:bg-[#c9a96e]/10 transition-colors">
                + <?= t('เพิ่มจุดเด่น','Add Highlight') ?>
            </button>
        </div>
        <div id="highlights-list" class="space-y-2">
            <?php foreach ($highlights as $hl): ?>
            <div class="flex gap-2 items-center highlight-row">
                <input type="text" name="highlights[]" value="<?= htmlspecialchars($hl['content'] ?? '') ?>"
                       class="flex-1 border border-[#e0dbd4] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                <button type="button" onclick="this.closest('.highlight-row').remove()"
                        class="text-[#9d8f82] hover:text-red-500 flex-shrink-0 text-lg leading-none">&times;</button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Images -->
    <div class="bg-white rounded-xl border border-[#e8e4df] p-5">
        <h3 class="font-semibold text-[#1a1714] text-sm mb-4"><?= t('รูปภาพ','Images') ?></h3>
        <?php if (!empty($images)): ?>
        <div class="grid grid-cols-4 gap-3 mb-4">
            <?php foreach ($images as $img): ?>
            <div class="relative group">
                <img src="<?= $_member_base ?>/assets/images/properties/<?= $id ?>/<?= htmlspecialchars($img['filename']) ?>"
                     class="w-full aspect-square object-cover rounded-lg border border-[#e8e4df]" alt="">
                <label class="absolute top-1.5 right-1.5 bg-white/90 rounded-full w-6 h-6 flex items-center justify-center cursor-pointer shadow-sm border border-[#e8e4df] group-hover:border-red-300">
                    <input type="checkbox" name="delete_images[]" value="<?= $img['id'] ?>" class="sr-only peer">
                    <span class="text-[#9d8f82] peer-checked:text-red-500 text-xs font-bold">&times;</span>
                </label>
            </div>
            <?php endforeach; ?>
        </div>
        <p class="text-xs text-[#9d8f82] mb-3"><?= t('คลิก × เพื่อทำเครื่องหมายลบ','Click × to mark for deletion') ?></p>
        <?php endif; ?>
        <div>
            <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('อัปโหลดรูปใหม่ (JPG, PNG, WebP — สูงสุด 5MB/รูป)','Upload images (JPG, PNG, WebP — max 5MB each)') ?></label>
            <input type="file" name="new_images[]" multiple accept="image/jpeg,image/png,image/webp"
                   class="text-sm text-[#6b5f52] file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-[#c9a96e]/10 file:text-[#c9a96e] hover:file:bg-[#c9a96e]/20">
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="bg-[#c9a96e] hover:bg-[#b8965e] text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition-colors">
            <?= $is_new ? t('ส่งรอยืนยัน','Submit for Approval') : t('บันทึก','Save') ?>
        </button>
        <a href="properties" class="text-sm text-[#9d8f82] hover:text-[#6b5f52]"><?= t('ยกเลิก','Cancel') ?></a>
    </div>

    <div class="mt-4 text-xs text-red-600 leading-relaxed space-y-1">
        <p><span class="font-semibold">*</span> <?= t('หมายเหตุ: เมื่อเกิดการซื้อขายทรัพย์สินผ่านเว็บไซต์ ผู้ลงประกาศตกลงชำระค่าคอมมิชชั่นให้แก่เจ้าของเว็บไซต์ในอัตราร้อยละ 3 ของราคาซื้อขาย','Note: Upon completion of a property sale arranged through this website, the lister agrees to pay the website owner a commission of 3% of the sale price.') ?></p>
        <p><span class="font-semibold">*</span> <?= t('หมายเหตุ: กรณีเกิดการให้เช่าตามสัญญาระยะเวลา 1 ปี เจ้าของเว็บไซต์จะได้รับค่าตอบแทนเทียบเท่าค่าเช่า 1 เดือน จากค่าเช่าทั้งหมด 12 เดือน','Note: In the case of a lease with a one-year term, the website owner shall receive compensation equivalent to one month of rent out of the twelve-month term.') ?></p>
    </div>
</form>

<script>
function addHighlight() {
    const row = document.createElement('div');
    row.className = 'flex gap-2 items-center highlight-row';
    row.innerHTML = `<input type="text" name="highlights[]"
        class="flex-1 border border-[#e0dbd4] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
        <button type="button" onclick="this.closest('.highlight-row').remove()"
            class="text-[#9d8f82] hover:text-red-500 flex-shrink-0 text-lg leading-none">&times;</button>`;
    document.getElementById('highlights-list').appendChild(row);
    row.querySelector('input').focus();
}
</script>

<script src="<?= $_member_base ?>/assets/js/form-draft.js"></script>
<script>
    // Keep unsaved edits per property (or "new" for a fresh submission).
    initFormDraft(document.getElementById('property-form'), 'invez.property-draft.member.<?= $is_new ? 'new' : $id ?>');
</script>

<?php include('_footer.php'); ?>
