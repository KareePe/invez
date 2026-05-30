<?php
require_once('auth.php');
require_once('../config/db.php');
require_once('../properties_data.php');

$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_new = $id === 0;
$page_title = $is_new ? 'เพิ่มทรัพย์สิน' : 'แก้ไขทรัพย์สิน';

$prop       = [];
$highlights = [];
$images     = [];
$errors     = [];

if (!$is_new) {
    $prop = db()->prepare('SELECT * FROM properties WHERE id = ?');
    $prop->execute([$id]);
    $prop = $prop->fetch();
    if (!$prop) {
        flash('error', 'ไม่พบทรัพย์สิน');
        header('Location: properties');
        exit;
    }
    $highlights = db()->prepare('SELECT * FROM property_highlights WHERE property_id = ? ORDER BY sort_order ASC');
    $highlights->execute([$id]);
    $highlights = $highlights->fetchAll();

    $images = db()->prepare('SELECT * FROM property_images WHERE property_id = ? ORDER BY sort_order ASC');
    $images->execute([$id]);
    $images = $images->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $title          = trim($_POST['title'] ?? '');
    $category       = trim($_POST['category'] ?? '');
    $subtitle       = trim($_POST['subtitle'] ?? '');
    $price          = $_POST['price'] !== '' ? (int)$_POST['price'] : null;
    $price_display  = trim($_POST['price_display'] ?? '');
    $location       = trim($_POST['location'] ?? '');
    $location_short = trim($_POST['location_short'] ?? '');
    $land_area      = trim($_POST['land_area'] ?? '');
    $usable_area    = trim($_POST['usable_area'] ?? '');
    $floors         = trim($_POST['floors'] ?? '');
    $beds           = $_POST['beds'] !== '' ? (int)$_POST['beds'] : null;
    $bathrooms      = $_POST['bathrooms'] !== '' ? (int)$_POST['bathrooms'] : null;
    $parking        = trim($_POST['parking'] ?? '');
    $offices        = $_POST['offices'] !== '' ? (int)$_POST['offices'] : null;
    $status         = trim($_POST['status'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $sort_order     = (int)($_POST['sort_order'] ?? 0);
    $is_active      = isset($_POST['is_active']) ? 1 : 0;

    if ($title === '')    $errors[] = 'กรุณากรอกชื่อทรัพย์สิน';
    if ($category === '') $errors[] = 'กรุณาเลือกหมวดหมู่';

    if (empty($errors)) {
        $fields = [
            $category, $title, $subtitle ?: null, $price, $price_display ?: null,
            $location ?: null, $location_short ?: null,
            $land_area ?: null, $usable_area ?: null, $floors ?: null,
            $beds, $bathrooms, $parking ?: null, $offices,
            $status ?: null, $description ?: null,
            $sort_order, $is_active,
        ];

        if ($is_new) {
            $stmt = db()->prepare(
                'INSERT INTO properties
                 (category,title,subtitle,price,price_display,location,location_short,
                  land_area,usable_area,floors,beds,bathrooms,parking,offices,
                  status,description,sort_order,is_active)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            );
            $stmt->execute($fields);
            $id = (int)db()->lastInsertId();
        } else {
            $stmt = db()->prepare(
                'UPDATE properties SET
                 category=?,title=?,subtitle=?,price=?,price_display=?,location=?,location_short=?,
                 land_area=?,usable_area=?,floors=?,beds=?,bathrooms=?,parking=?,offices=?,
                 status=?,description=?,sort_order=?,is_active=?
                 WHERE id=?'
            );
            $stmt->execute(array_merge($fields, [$id]));
        }

        // Highlights: delete all and re-insert
        db()->prepare('DELETE FROM property_highlights WHERE property_id = ?')->execute([$id]);
        $raw_hl = array_filter(array_map('trim', $_POST['highlights'] ?? []), fn($v) => $v !== '');
        $hl_stmt = db()->prepare('INSERT INTO property_highlights (property_id, content, sort_order) VALUES (?,?,?)');
        foreach (array_values($raw_hl) as $i => $hl) {
            $hl_stmt->execute([$id, $hl, $i + 1]);
        }

        // Delete marked images
        $to_delete = array_map('intval', $_POST['delete_images'] ?? []);
        foreach ($to_delete as $img_id) {
            $row = db()->prepare('SELECT filename FROM property_images WHERE id = ? AND property_id = ?');
            $row->execute([$img_id, $id]);
            $row = $row->fetch();
            if ($row) {
                $path = dirname(__DIR__) . '/assets/images/properties/' . $id . '/' . $row['filename'];
                if (file_exists($path)) unlink($path);
                db()->prepare('DELETE FROM property_images WHERE id = ?')->execute([$img_id]);
            }
        }

        // Upload new images
        $upload_dir = dirname(__DIR__) . '/assets/images/properties/' . $id . '/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $new_files = $_FILES['new_images'] ?? [];
        if (!empty($new_files['name'][0])) {
            $allowed_mime = ['image/jpeg', 'image/png', 'image/webp'];
            $ext_map      = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $max_sort     = db()->prepare('SELECT COALESCE(MAX(sort_order),0) FROM property_images WHERE property_id = ?');
            $max_sort->execute([$id]);
            $next_sort = (int)$max_sort->fetchColumn() + 1;

            $img_stmt = db()->prepare('INSERT INTO property_images (property_id, filename, sort_order) VALUES (?,?,?)');
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

        flash('success', $is_new ? 'เพิ่มทรัพย์สินสำเร็จ' : 'บันทึกทรัพย์สินสำเร็จ');
        header('Location: properties');
        exit;
    }

    // Re-populate $prop from POST on error
    $prop = array_merge($_POST, ['id' => $id]);
    $highlights = array_map(fn($v) => ['content' => $v], $_POST['highlights'] ?? []);
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

<form method="POST" enctype="multipart/form-data" class="space-y-6">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <div class="grid grid-cols-2 gap-6">

        <!-- Left column -->
        <div class="space-y-5">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 text-sm mb-4">ข้อมูลพื้นฐาน</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">หมวดหมู่ *</label>
                        <select name="category" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
                            <option value="">— เลือกหมวดหมู่ —</option>
                            <?php foreach ($property_categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($prop['category'] ?? '') === $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['label']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">ชื่อทรัพย์สิน *</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($prop['title'] ?? '') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Subtitle</label>
                        <input type="text" name="subtitle" value="<?= htmlspecialchars($prop['subtitle'] ?? '') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">สถานะ</label>
                        <input type="text" name="status" value="<?= htmlspecialchars($prop['status'] ?? '') ?>"
                               placeholder="เช่น พร้อมขาย, ดำเนินกิจการอยู่"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 text-sm mb-4">ราคา</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">ราคา (ตัวเลข, บาท)</label>
                        <input type="number" name="price" value="<?= $prop['price'] ?? '' ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">ราคาที่แสดง</label>
                        <input type="text" name="price_display" value="<?= htmlspecialchars($prop['price_display'] ?? '') ?>"
                               placeholder="เช่น 15 ล้านบาท"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 text-sm mb-4">ที่ตั้ง</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">ที่อยู่เต็ม</label>
                        <textarea name="location" rows="2"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e] resize-none"><?= htmlspecialchars($prop['location'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">ที่ตั้งสั้น</label>
                        <input type="text" name="location_short" value="<?= htmlspecialchars($prop['location_short'] ?? '') ?>"
                               placeholder="เช่น ตลิ่งชัน, กรุงเทพฯ"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                    </div>
                </div>
            </div>
        </div>

        <!-- Right column -->
        <div class="space-y-5">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 text-sm mb-4">ขนาดและรายละเอียด</h3>
                <div class="grid grid-cols-2 gap-3">
                    <?php
                    $spec_fields = [
                        ['land_area',    'ที่ดิน (ตร.ว.)',     'text',   '84 ตร.ว.'],
                        ['usable_area',  'พื้นที่ใช้สอย',       'text',   '302 ตร.ม.'],
                        ['floors',       'จำนวนชั้น',           'text',   '2 ชั้น'],
                        ['beds',         'ห้องนอน / เตียง',    'number', ''],
                        ['bathrooms',    'ห้องน้ำ',             'number', ''],
                        ['offices',      'ห้องสำนักงาน',        'number', ''],
                        ['parking',      'จอดรถ',               'text',   '2 คัน'],
                    ];
                    foreach ($spec_fields as [$name, $label, $type, $ph]):
                    ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1"><?= $label ?></label>
                        <input type="<?= $type ?>" name="<?= $name ?>"
                               value="<?= htmlspecialchars((string)($prop[$name] ?? '')) ?>"
                               placeholder="<?= $ph ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 text-sm mb-4">รายละเอียด</h3>
                <textarea name="description" rows="5"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e] resize-none"><?= htmlspecialchars($prop['description'] ?? '') ?></textarea>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 text-sm mb-1">การแสดงผล</h3>
                <div class="flex items-center gap-3 mt-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">ลำดับการแสดง</label>
                        <input type="number" name="sort_order" value="<?= (int)($prop['sort_order'] ?? 0) ?>"
                               class="w-24 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-700 mt-5 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" <?= ($prop['is_active'] ?? 1) ? 'checked' : '' ?>
                               class="w-4 h-4 rounded accent-[#c9a96e]">
                        แสดงบนเว็บไซต์
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Highlights -->
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-700 text-sm">จุดเด่น</h3>
            <button type="button" onclick="addHighlight()"
                    class="text-xs text-[#c9a96e] border border-[#c9a96e] px-3 py-1.5 rounded-lg hover:bg-[#c9a96e]/10 transition-colors">
                + เพิ่มจุดเด่น
            </button>
        </div>
        <div id="highlights-list" class="space-y-2">
            <?php foreach ($highlights as $hl): ?>
            <div class="flex gap-2 items-center">
                <input type="text" name="highlights[]" value="<?= htmlspecialchars($hl['content']) ?>"
                       class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                <button type="button" onclick="this.parentElement.remove()"
                        class="text-gray-400 hover:text-red-500 flex-shrink-0 text-lg leading-none">&times;</button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Images -->
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-700 text-sm mb-4">รูปภาพ</h3>

        <?php if (!empty($images)): ?>
        <div class="grid grid-cols-4 gap-3 mb-4">
            <?php foreach ($images as $img): ?>
            <div class="relative group">
                <img src="<?= $_base_path ?>/assets/images/properties/<?= $id ?>/<?= htmlspecialchars($img['filename']) ?>"
                     class="w-full aspect-square object-cover rounded-lg border border-gray-200" alt="">
                <label class="absolute top-1.5 right-1.5 bg-white/90 rounded-full w-6 h-6 flex items-center justify-center cursor-pointer shadow-sm border border-gray-200 group-hover:border-red-300"
                       title="ลบรูปนี้">
                    <input type="checkbox" name="delete_images[]" value="<?= $img['id'] ?>"
                           class="sr-only peer">
                    <span class="text-gray-400 peer-checked:text-red-500 text-xs leading-none font-bold">&times;</span>
                </label>
                <div class="absolute inset-0 rounded-lg border-2 border-transparent peer-checked:border-red-400 pointer-events-none hidden"></div>
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
        <a href="properties" class="text-sm text-gray-500 hover:text-gray-700">ยกเลิก</a>
    </div>
</form>

<script>
function addHighlight() {
    const row = document.createElement('div');
    row.className = 'flex gap-2 items-center';
    row.innerHTML = `<input type="text" name="highlights[]"
        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
        <button type="button" onclick="this.parentElement.remove()"
            class="text-gray-400 hover:text-red-500 flex-shrink-0 text-lg leading-none">&times;</button>`;
    document.getElementById('highlights-list').appendChild(row);
    row.querySelector('input').focus();
}
</script>

<?php include('_footer.php'); ?>
