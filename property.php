<?php
require_once('config/db.php');
require_once('properties_data.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = db()->prepare('SELECT * FROM properties WHERE id = ? AND is_active = 1');
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    header('Location: /properties');
    exit;
}

$h_stmt = db()->prepare('SELECT content FROM property_highlights WHERE property_id = ? ORDER BY sort_order ASC');
$h_stmt->execute([$id]);
$p['highlights'] = $h_stmt->fetchAll(PDO::FETCH_COLUMN);

$i_stmt = db()->prepare('SELECT filename FROM property_images WHERE property_id = ? ORDER BY sort_order ASC');
$i_stmt->execute([$id]);
$p['images'] = $i_stmt->fetchAll(PDO::FETCH_COLUMN);

$current_page = 'properties';
$cat_label = get_category_label($property_categories, $p['category']);
$cat_icon  = get_category_icon($property_categories, $p['category']);
$cover     = !empty($p['images']) ? 'assets/images/properties/'.$id.'/'.$p['images'][0] : null;

$meta_title = htmlspecialchars($p['title']) . ' | INVEZ';
$meta_desc  = htmlspecialchars($p['description'] ?? $p['title']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title><?= $meta_title ?></title>
    <meta name="description" content="<?= mb_substr($meta_desc, 0, 160) ?>" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://www.invez.biz/property/<?= $id ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?= $meta_title ?>" />
    <meta property="og:description" content="<?= mb_substr($meta_desc, 0, 160) ?>" />
    <meta property="og:url" content="https://www.invez.biz/property/<?= $id ?>" />
    <?php if ($cover): ?>
    <meta property="og:image" content="https://www.invez.biz/<?= htmlspecialchars($cover) ?>" />
    <?php endif; ?>
    <meta property="og:site_name" content="INVEZ" />
    <meta name="theme-color" content="#ffffff" />
    <link rel="icon" href="/favicon.ico" type="image/x-icon" />
    <?php
    $_base = rtrim(str_replace('\\', '/', str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', realpath(__DIR__))), '/') . '/';
    ?>
    <base href="<?= htmlspecialchars($_base) ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-white text-[#1a1714]">

    <!-- Preloader -->
    <div id="preloader" class="fixed inset-0 bg-white z-50 flex items-center justify-center">
        <div class="text-center">
            <img src="assets/images/logo-b.png" class="w-[100px] logo" alt="INVEZ">
        </div>
    </div>

    <?php include('components/navbar.php'); ?>

    <!-- Hero -->
    <section class="pt-14 bg-white border-b border-[#e8e4df]">
        <div class="max-w-4xl mx-auto px-6 py-10 md:py-14">

            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-[#9d8f82] mb-6" aria-label="breadcrumb">
                <a href="/properties" class="hover:text-[#1a1714] transition-colors">ทรัพย์สิน</a>
                <span>/</span>
                <a href="/properties?cat=<?= htmlspecialchars($p['category']) ?>" class="hover:text-[#1a1714] transition-colors">
                    <?= htmlspecialchars($cat_label) ?>
                </a>
                <span>/</span>
                <span class="text-[#1a1714]"><?= htmlspecialchars($p['title']) ?></span>
            </nav>

            <div class="fade-up">
                <span class="inline-block text-xs text-[#c9a96e] mb-3">
                    <?= htmlspecialchars($cat_label) ?>
                </span>
                <h1 class="text-2xl md:text-3xl font-semibold text-[#1a1714] leading-snug mb-2">
                    <?= htmlspecialchars($p['title']) ?>
                </h1>
                <?php if (!empty($p['subtitle'])): ?>
                <p class="text-[#6b5f52] text-sm"><?= htmlspecialchars($p['subtitle']) ?></p>
                <?php endif; ?>
            </div>

        </div>
    </section>

    <!-- Main Content -->
    <section class="py-10 md:py-14 px-6 bg-white">
        <div class="max-w-4xl mx-auto">

            <!-- Cover image -->
            <?php if ($cover): ?>
            <div class="mb-8 rounded-lg overflow-hidden border border-[#e8e4df]">
                <img src="<?= htmlspecialchars($cover) ?>"
                     alt="<?= htmlspecialchars($p['title']) ?>"
                     class="w-full max-h-[440px] object-cover">
            </div>
            <?php endif; ?>

            <!-- Price + Status -->
            <div class="flex flex-wrap items-center justify-between gap-4 py-5 border-y border-[#e8e4df] mb-8">
                <div>
                    <p class="text-xs text-[#9d8f82] mb-0.5">ราคา</p>
                    <p class="text-xl md:text-2xl font-semibold text-[#c9a96e]"><?= htmlspecialchars($p['price_display']) ?></p>
                </div>
                <?php if (!empty($p['status'])): ?>
                <div class="text-right">
                    <p class="text-xs text-[#9d8f82] mb-0.5">สถานะ</p>
                    <p class="text-sm font-medium text-[#1a1714]"><?= htmlspecialchars($p['status']) ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Key stats -->
            <?php
            $stats = [];
            if (!empty($p['land_area']))   $stats[] = ['ที่ดิน', htmlspecialchars($p['land_area'])];
            if (!empty($p['usable_area'])) $stats[] = ['พื้นที่ใช้สอย', htmlspecialchars($p['usable_area'])];
            if (!empty($p['floors']))      $stats[] = ['จำนวนชั้น', htmlspecialchars($p['floors'])];
            if (!empty($p['beds']))        $stats[] = [$p['category'] === 'hospital' ? 'เตียง' : 'ห้องนอน', $p['beds'].' '.($p['category'] === 'hospital' ? 'เตียง' : 'ห้อง')];
            if (!empty($p['bathrooms']))   $stats[] = ['ห้องน้ำ', $p['bathrooms'].' ห้อง'];
            if (!empty($p['parking']))     $stats[] = ['จอดรถ', htmlspecialchars($p['parking'])];
            if (!empty($p['offices']))     $stats[] = ['ห้องสำนักงาน', $p['offices'].' ห้อง'];
            if (!empty($p['location_short'])) $stats[] = ['ที่ตั้ง', htmlspecialchars($p['location_short'])];
            ?>
            <?php if (!empty($stats)): ?>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-px bg-[#e8e4df] border border-[#e8e4df] rounded-lg overflow-hidden mb-8">
                <?php foreach ($stats as $s): ?>
                <div class="bg-white px-4 py-3">
                    <p class="text-xs text-[#9d8f82] mb-0.5"><?= $s[0] ?></p>
                    <p class="text-sm font-medium text-[#1a1714]"><?= $s[1] ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Description -->
            <?php if (!empty($p['description'])): ?>
            <div class="mb-8">
                <h2 class="text-base font-semibold text-[#1a1714] mb-3">รายละเอียด</h2>
                <p class="text-[#5a4e42] text-sm leading-7"><?= htmlspecialchars($p['description']) ?></p>
                <?php if (!empty($p['location'])): ?>
                <p class="text-xs text-[#9d8f82] mt-3 flex items-center gap-1">
                    <i data-feather="map-pin" style="width:11px;height:11px;"></i>
                    <?= htmlspecialchars($p['location']) ?>
                </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Highlights -->
            <?php if (!empty($p['highlights'])): ?>
            <div class="mb-8">
                <h2 class="text-base font-semibold text-[#1a1714] mb-4">จุดเด่น</h2>
                <div class="space-y-2">
                    <?php foreach ($p['highlights'] as $i => $h): ?>
                    <div class="flex gap-3 items-start py-3 border-b border-[#f0ebe3] last:border-0">
                        <span class="w-5 h-5 rounded-full bg-[#c9a96e] text-white flex items-center justify-center text-[10px] font-semibold flex-shrink-0 mt-0.5"><?= $i + 1 ?></span>
                        <p class="text-[#5a4e42] text-sm leading-6"><?= htmlspecialchars($h) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Gallery -->
            <?php if (count($p['images']) > 1): ?>
            <div class="mb-8">
                <h2 class="text-base font-semibold text-[#1a1714] mb-4">รูปภาพทั้งหมด</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    <?php foreach ($p['images'] as $img): ?>
                    <div class="rounded-lg overflow-hidden border border-[#e8e4df] aspect-[4/3]">
                        <img src="assets/images/properties/<?= $id ?>/<?= htmlspecialchars($img) ?>"
                             alt="<?= htmlspecialchars($p['title']) ?>"
                             class="w-full h-full object-cover">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </section>

    <!-- Back nav -->
    <section class="py-6 px-6 bg-[#fafaf8] border-t border-[#e8e4df]">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <a href="/properties?cat=<?= htmlspecialchars($p['category']) ?>"
               class="flex items-center gap-1.5 text-[#6b5f52] hover:text-[#1a1714] transition-colors text-sm">
                <i data-feather="arrow-left" style="width:14px;height:14px;"></i>
                <?= htmlspecialchars($cat_label) ?>ทั้งหมด
            </a>
            <a href="/properties"
               class="text-xs text-[#9d8f82] hover:text-[#1a1714] transition-colors">
                ดูทั้งหมด
            </a>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-14 px-6 bg-[#1a1714]">
        <div class="max-w-2xl mx-auto text-center">
            <h2 class="text-lg font-semibold text-white mb-2">สนใจทรัพย์สินนี้?</h2>
            <p class="text-[#9d8f82] text-sm leading-6 mb-6">ทีมงาน INVEZ พร้อมให้ข้อมูลและเจรจาดีลที่ตรงโจทย์ให้คุณ</p>
            <a href="/contact"
               class="inline-flex items-center gap-2 bg-[#c9a96e] text-white px-6 py-2.5 rounded text-sm font-medium hover:bg-[#b8965e] transition-colors duration-150">
                ติดต่อเรา
            </a>
        </div>
    </section>

    <?php include('components/footer.php'); ?>

    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        feather.replace();
        const observer = new IntersectionObserver(
            (entries) => entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('show'); }),
            { threshold: 0.05 }
        );
        document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
        window.addEventListener('load', () => {
            setTimeout(() => document.getElementById('preloader').classList.add('hide'), 400);
        });
    </script>
</body>
</html>
