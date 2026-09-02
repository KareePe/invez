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

    $title             = trim($_POST['title'] ?? '');
    $title_en          = trim($_POST['title_en'] ?? '');
    $category          = trim($_POST['category'] ?? '');
    $subtitle          = trim($_POST['subtitle'] ?? '');
    $subtitle_en       = trim($_POST['subtitle_en'] ?? '');
    // The price field posts grouped digits ("15,000,000") from the formatted input.
    $price_raw         = preg_replace('/\D/', '', (string)($_POST['price'] ?? ''));
    $price             = $price_raw !== '' ? (int)$price_raw : null;
    $price_display     = trim($_POST['price_display'] ?? '');
    $price_display_en  = trim($_POST['price_display_en'] ?? '');
    // Deposit ratio shown on the checkout page. Empty or out of range = not set,
    // and checkout falls back to its own 10% default.
    $deposit_percent   = $_POST['deposit_percent'] !== '' ? (int)$_POST['deposit_percent'] : null;
    if ($deposit_percent !== null && ($deposit_percent < 0 || $deposit_percent > 100)) {
        $deposit_percent = null;
    }
    $location          = trim($_POST['location'] ?? '');
    $location_en       = trim($_POST['location_en'] ?? '');
    $location_short    = trim($_POST['location_short'] ?? '');
    $location_short_en = trim($_POST['location_short_en'] ?? '');
    $land_area         = trim($_POST['land_area'] ?? '');
    $land_area_en      = trim($_POST['land_area_en'] ?? '');
    $usable_area       = trim($_POST['usable_area'] ?? '');
    $usable_area_en    = trim($_POST['usable_area_en'] ?? '');
    $floors            = trim($_POST['floors'] ?? '');
    $floors_en         = trim($_POST['floors_en'] ?? '');
    $beds              = $_POST['beds'] !== '' ? (int)$_POST['beds'] : null;
    $bathrooms         = $_POST['bathrooms'] !== '' ? (int)$_POST['bathrooms'] : null;
    $parking           = trim($_POST['parking'] ?? '');
    $parking_en        = trim($_POST['parking_en'] ?? '');
    $offices           = $_POST['offices'] !== '' ? (int)$_POST['offices'] : null;
    $status            = trim($_POST['status'] ?? '');
    $status_en         = trim($_POST['status_en'] ?? '');
    $description       = trim($_POST['description'] ?? '');
    $description_en    = trim($_POST['description_en'] ?? '');
    $sort_order        = (int)($_POST['sort_order'] ?? 0);
    $is_active         = isset($_POST['is_active']) ? 1 : 0;
    $is_contracted     = isset($_POST['is_contracted']) ? 1 : 0;

    if ($title === '')    $errors[] = 'กรุณากรอกชื่อทรัพย์สิน';
    if ($category === '') $errors[] = 'กรุณาเลือกหมวดหมู่';

    if (empty($errors)) {
        $fields = [
            $category,
            $title, $title_en ?: null,
            $subtitle ?: null, $subtitle_en ?: null,
            $price, $price_display ?: null, $price_display_en ?: null, $deposit_percent,
            $location ?: null, $location_en ?: null,
            $location_short ?: null, $location_short_en ?: null,
            $land_area ?: null, $land_area_en ?: null,
            $usable_area ?: null, $usable_area_en ?: null,
            $floors ?: null, $floors_en ?: null,
            $beds, $bathrooms, $parking ?: null, $parking_en ?: null, $offices,
            $status ?: null, $status_en ?: null,
            $description ?: null, $description_en ?: null,
            $sort_order, $is_active, $is_contracted,
        ];

        if ($is_new) {
            $stmt = db()->prepare(
                'INSERT INTO properties
                 (category,title,title_en,subtitle,subtitle_en,price,price_display,price_display_en,deposit_percent,
                  location,location_en,location_short,location_short_en,
                  land_area,land_area_en,usable_area,usable_area_en,floors,floors_en,
                  beds,bathrooms,parking,parking_en,offices,
                  status,status_en,description,description_en,sort_order,is_active,is_contracted)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            );
            $stmt->execute($fields);
            $id = (int)db()->lastInsertId();
            db()->prepare("UPDATE properties SET token = ? WHERE id = ? AND token IS NULL")
                ->execute([bin2hex(random_bytes(16)), $id]);
        } else {
            $stmt = db()->prepare(
                'UPDATE properties SET
                 category=?,title=?,title_en=?,subtitle=?,subtitle_en=?,price=?,price_display=?,price_display_en=?,deposit_percent=?,
                 location=?,location_en=?,location_short=?,location_short_en=?,
                 land_area=?,land_area_en=?,usable_area=?,usable_area_en=?,floors=?,floors_en=?,
                 beds=?,bathrooms=?,parking=?,parking_en=?,offices=?,
                 status=?,status_en=?,description=?,description_en=?,sort_order=?,is_active=?,is_contracted=?
                 WHERE id=?'
            );
            $stmt->execute(array_merge($fields, [$id]));
        }

        // Highlights: delete all and re-insert with paired EN values
        db()->prepare('DELETE FROM property_highlights WHERE property_id = ?')->execute([$id]);
        $raw_hl    = array_map('trim', $_POST['highlights'] ?? []);
        $raw_hl_en = array_map('trim', $_POST['highlights_en'] ?? []);
        $hl_stmt   = db()->prepare('INSERT INTO property_highlights (property_id, content, content_en, sort_order) VALUES (?,?,?,?)');
        $count = max(count($raw_hl), count($raw_hl_en));
        $sort  = 1;
        for ($i = 0; $i < $count; $i++) {
            $th = $raw_hl[$i] ?? '';
            $en = $raw_hl_en[$i] ?? '';
            if ($th !== '' || $en !== '') {
                $hl_stmt->execute([$id, $th ?: null, $en ?: null, $sort++]);
            }
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

        log_admin_activity($is_new ? 'create' : 'update', 'property', $id, $title);
        flash('success', $is_new ? 'เพิ่มทรัพย์สินสำเร็จ' : 'บันทึกทรัพย์สินสำเร็จ');
        header('Location: properties');
        exit;
    }

    // Re-populate $prop from POST on error
    $prop = array_merge($_POST, ['id' => $id]);
    $raw_hl_err    = $_POST['highlights'] ?? [];
    $raw_hl_en_err = $_POST['highlights_en'] ?? [];
    $count_err = max(count($raw_hl_err), count($raw_hl_en_err));
    $highlights = [];
    for ($i = 0; $i < $count_err; $i++) {
        $highlights[] = ['content' => $raw_hl_err[$i] ?? '', 'content_en' => $raw_hl_en_err[$i] ?? ''];
    }
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

<form method="POST" data-loading="button" enctype="multipart/form-data" class="space-y-6" id="property-form">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <!-- Language tab switcher -->
    <div class="flex items-center gap-3">
        <span class="text-xs font-medium text-gray-500">ภาษา / Language:</span>
        <div class="flex text-xs border border-gray-200 rounded-lg overflow-hidden">
            <button type="button" class="lang-tab-btn px-4 py-1.5 font-medium bg-[#c9a96e] text-white" data-lang="th">TH</button>
            <button type="button" class="lang-tab-btn px-4 py-1.5 font-medium text-gray-500 hover:bg-gray-50" data-lang="en">EN</button>
        </div>
    </div>

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
                    <!-- TH fields -->
                    <div class="lang-panel" data-lang="th">
                        <div class="space-y-4">
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
                    <!-- EN fields -->
                    <div class="lang-panel hidden" data-lang="en">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Property Name (EN)</label>
                                <input type="text" name="title_en" value="<?= htmlspecialchars($prop['title_en'] ?? '') ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Subtitle (EN)</label>
                                <input type="text" name="subtitle_en" value="<?= htmlspecialchars($prop['subtitle_en'] ?? '') ?>"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Status (EN)</label>
                                <input type="text" name="status_en" value="<?= htmlspecialchars($prop['status_en'] ?? '') ?>"
                                       placeholder="e.g. For Sale, In Operation"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 text-sm mb-4">ราคา</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">ราคา (ตัวเลข, บาท)</label>
                        <?php $price_value = preg_replace('/\D/', '', (string)($prop['price'] ?? '')); ?>
                        <input type="text" name="price" inputmode="numeric" autocomplete="off"
                               value="<?= $price_value !== '' ? number_format((int)$price_value) : '' ?>"
                               id="price-input"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                    </div>
                    <!-- TH display price -->
                    <div class="lang-panel" data-lang="th">
                        <label class="block text-xs font-medium text-gray-600 mb-1">ราคาที่แสดง</label>
                        <input type="text" name="price_display" value="<?= htmlspecialchars($prop['price_display'] ?? '') ?>"
                               placeholder="เช่น 15 ล้านบาท"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                    </div>
                    <!-- EN display price -->
                    <div class="lang-panel hidden" data-lang="en">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Display Price (EN)</label>
                        <input type="text" name="price_display_en" value="<?= htmlspecialchars($prop['price_display_en'] ?? '') ?>"
                               placeholder="e.g. THB 15 Million"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                        <p class="text-xs text-gray-500 mt-1">ถ้าเว้นว่าง หน้าเว็บภาษาอังกฤษจะใช้ราคาที่แสดงภาษาไทย</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">สัดส่วนมัดจำ (%)</label>
                        <input type="number" name="deposit_percent" min="0" max="100" step="1"
                               value="<?= htmlspecialchars((string)($prop['deposit_percent'] ?? '')) ?>"
                               placeholder="10"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                        <p class="text-xs text-gray-500 mt-1">ใช้เป็นค่าเริ่มต้นของสัดส่วนมัดจำในหน้าชำระเงิน หากไม่ระบุจะใช้ 10%</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 text-sm mb-4">ที่ตั้ง</h3>
                <!-- TH location -->
                <div class="lang-panel space-y-4" data-lang="th">
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
                <!-- EN location -->
                <div class="lang-panel hidden space-y-4" data-lang="en">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Full Address (EN)</label>
                        <textarea name="location_en" rows="2"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e] resize-none"><?= htmlspecialchars($prop['location_en'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Short Location (EN)</label>
                        <input type="text" name="location_short_en" value="<?= htmlspecialchars($prop['location_short_en'] ?? '') ?>"
                               placeholder="e.g. Bangkok, Thailand"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                    </div>
                </div>
            </div>
        </div>

        <!-- Right column -->
        <div class="space-y-5">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 text-sm mb-4">ขนาดและรายละเอียด</h3>
                <?php
                // Text specs carry unit words ("84 ตร.ว.", "2 ชั้น") so each has an EN pair.
                // Layout: [field, TH label, TH placeholder, EN label, EN placeholder]
                $spec_text = [
                    ['land_area',   'ที่ดิน (ตร.ว.)', '84 ตร.ว.',  'Land Area (EN)',   'e.g. 84 sq.wah'],
                    ['usable_area', 'พื้นที่ใช้สอย',   '302 ตร.ม.', 'Usable Area (EN)', 'e.g. 302 sq.m.'],
                    ['floors',      'จำนวนชั้น',       '2 ชั้น',    'Floors (EN)',      'e.g. 2 Floors'],
                    ['parking',     'จอดรถ',           '2 คัน',     'Parking (EN)',     'e.g. 2 Cars'],
                ];
                // Counts are language-neutral, so they stay outside the language panels.
                $spec_num = [
                    ['beds',      'ห้องนอน / เตียง'],
                    ['bathrooms', 'ห้องน้ำ'],
                    ['offices',   'ห้องสำนักงาน'],
                ];
                $spec_input_class = 'w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]';
                ?>
                <!-- TH specs -->
                <div class="lang-panel" data-lang="th">
                    <div class="grid grid-cols-2 gap-3">
                        <?php foreach ($spec_text as [$name, $label, $ph, , ]): ?>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1"><?= $label ?></label>
                            <input type="text" name="<?= $name ?>"
                                   value="<?= htmlspecialchars((string)($prop[$name] ?? '')) ?>"
                                   placeholder="<?= $ph ?>"
                                   class="<?= $spec_input_class ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- EN specs -->
                <div class="lang-panel hidden" data-lang="en">
                    <div class="grid grid-cols-2 gap-3">
                        <?php foreach ($spec_text as [$name, , , $label_en, $ph_en]): ?>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1"><?= $label_en ?></label>
                            <input type="text" name="<?= $name ?>_en"
                                   value="<?= htmlspecialchars((string)($prop[$name . '_en'] ?? '')) ?>"
                                   placeholder="<?= $ph_en ?>"
                                   class="<?= $spec_input_class ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">ถ้าเว้นว่าง หน้าเว็บภาษาอังกฤษจะใช้ค่าภาษาไทย</p>
                </div>
                <div class="grid grid-cols-2 gap-3 mt-3">
                    <?php foreach ($spec_num as [$name, $label]): ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1"><?= $label ?></label>
                        <input type="number" name="<?= $name ?>"
                               value="<?= htmlspecialchars((string)($prop[$name] ?? '')) ?>"
                               class="<?= $spec_input_class ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 text-sm mb-4">รายละเอียด</h3>
                <div class="lang-panel" data-lang="th">
                    <textarea name="description" rows="5"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e] resize-none"><?= htmlspecialchars($prop['description'] ?? '') ?></textarea>
                </div>
                <div class="lang-panel hidden" data-lang="en">
                    <textarea name="description_en" rows="5"
                              placeholder="Description in English"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e] resize-none"><?= htmlspecialchars($prop['description_en'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 text-sm mb-1">การแสดงผล</h3>
                <div class="flex items-center gap-3 mt-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">ลำดับการแสดง</label>
                        <input type="number" name="sort_order" value="<?= (int)($prop['sort_order'] ?? 0) ?>"
                               class="w-24 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                    </div>
                    <div class="mt-5 space-y-2">
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" <?= ($prop['is_active'] ?? 1) ? 'checked' : '' ?>
                                   class="w-4 h-4 rounded accent-[#c9a96e]">
                            แสดงบนเว็บไซต์
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" name="is_contracted" value="1" <?= !empty($prop['is_contracted']) ? 'checked' : '' ?>
                                   class="w-4 h-4 rounded accent-[#c9a96e]">
                            เซ็นสัญญาแล้ว
                        </label>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-3">เมื่อติ๊ก &ldquo;เซ็นสัญญาแล้ว&rdquo; หน้ารายละเอียดทรัพย์สินจะแสดงสถานะนี้ และปุ่ม &ldquo;สนใจทรัพย์สินนี้&rdquo; จะถูกปิดการใช้งาน</p>
            </div>
        </div>
    </div>

    <!-- Highlights -->
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-1">
            <h3 class="font-semibold text-gray-700 text-sm">จุดเด่น</h3>
            <button type="button" onclick="addHighlight()"
                    class="text-xs text-[#c9a96e] border border-[#c9a96e] px-3 py-1.5 rounded-lg hover:bg-[#c9a96e]/10 transition-colors">
                + เพิ่มจุดเด่น
            </button>
        </div>
        <p class="text-[10px] text-gray-400 mb-3">กรอกทั้ง TH และ EN ในแต่ละแถว (EN ไม่บังคับ)</p>
        <div class="grid grid-cols-2 gap-2 text-[10px] font-medium text-gray-400 mb-1 px-1">
            <span>ภาษาไทย</span><span>English</span>
        </div>
        <div id="highlights-list" class="space-y-2">
            <?php foreach ($highlights as $hl): ?>
            <div class="grid grid-cols-2 gap-2 items-center highlight-row">
                <input type="text" name="highlights[]" value="<?= htmlspecialchars($hl['content'] ?? '') ?>"
                       placeholder="ภาษาไทย"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                <div class="flex gap-1">
                    <input type="text" name="highlights_en[]" value="<?= htmlspecialchars($hl['content_en'] ?? '') ?>"
                           placeholder="English"
                           class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                    <button type="button" onclick="this.closest('.highlight-row').remove()"
                            class="text-gray-400 hover:text-red-500 flex-shrink-0 text-lg leading-none px-1">&times;</button>
                </div>
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
    row.className = 'grid grid-cols-2 gap-2 items-center highlight-row';
    row.innerHTML = `
        <input type="text" name="highlights[]" placeholder="ภาษาไทย"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
        <div class="flex gap-1">
            <input type="text" name="highlights_en[]" placeholder="English"
                class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
            <button type="button" onclick="this.closest('.highlight-row').remove()"
                class="text-gray-400 hover:text-red-500 flex-shrink-0 text-lg leading-none px-1">&times;</button>
        </div>`;
    document.getElementById('highlights-list').appendChild(row);
    row.querySelector('input').focus();
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

<script src="<?= $_base_path ?>/assets/js/form-draft.js"></script>
<script>
    // Keep unsaved edits per property (or "new" for a fresh record).
    initFormDraft(document.getElementById('property-form'), 'invez.property-draft.admin.<?= $is_new ? 'new' : $id ?>');
</script>

<script src="<?= $_base_path ?>/assets/js/price-input.js"></script>
<script>
    // After the draft restore above, so a restored price is grouped too.
    initPriceInput(document.getElementById('price-input'));
</script>

<?php include('_footer.php'); ?>
