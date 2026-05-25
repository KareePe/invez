<?php
$current_page = 'properties';
require_once('config/db.php');
require_once('properties_data.php'); // for $property_categories + helpers

$filter      = isset($_GET['cat']) ? $_GET['cat'] : 'all';
$valid_cats  = array_merge(['all'], array_column($property_categories, 'id'));
if (!in_array($filter, $valid_cats, true)) $filter = 'all';

// Load properties from DB
if ($filter === 'all') {
    $stmt = db()->query(
        'SELECT p.id, p.category, p.title, p.subtitle, p.price_display, p.location_short,
                p.status, p.land_area, p.beds, p.floors,
                (SELECT filename FROM property_images WHERE property_id = p.id ORDER BY sort_order ASC LIMIT 1) AS first_image
         FROM properties p WHERE p.is_active = 1
         ORDER BY p.sort_order ASC, p.id ASC'
    );
} else {
    $stmt = db()->prepare(
        'SELECT p.id, p.category, p.title, p.subtitle, p.price_display, p.location_short,
                p.status, p.land_area, p.beds, p.floors,
                (SELECT filename FROM property_images WHERE property_id = p.id ORDER BY sort_order ASC LIMIT 1) AS first_image
         FROM properties p WHERE p.is_active = 1 AND p.category = ?
         ORDER BY p.sort_order ASC, p.id ASC'
    );
    $stmt->execute([$filter]);
}
$properties = $stmt->fetchAll();

// Counts per category
$count_rows = db()->query('SELECT category, COUNT(*) AS cnt FROM properties WHERE is_active = 1 GROUP BY category')->fetchAll();
$counts = [];
foreach ($count_rows as $row) $counts[$row['category']] = (int)$row['cnt'];
$total = array_sum($counts);

$active_cats = array_keys($counts);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>ทรัพย์สินทั้งหมด | INVEZ บริษัท โตโยซัพพลาย จำกัด</title>
    <meta name="description" content="รายการทรัพย์สินทุกประเภทจาก INVEZ โรงพยาบาล บ้านหรู คฤหาสถ์ คลังสินค้า โรงงาน และอีกมากมาย" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://www.invez.biz/properties<?= $filter !== 'all' ? '?cat='.htmlspecialchars($filter) : '' ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="ทรัพย์สินทั้งหมด | INVEZ" />
    <meta property="og:description" content="รายการทรัพย์สินทุกประเภทจาก INVEZ โรงพยาบาล บ้านหรู คลังสินค้า โรงงาน และอีกมากมาย" />
    <meta property="og:url" content="https://www.invez.biz/properties" />
    <meta property="og:site_name" content="INVEZ" />
    <meta name="theme-color" content="#fafaf8" />
    <link rel="icon" href="/favicon.ico" type="image/x-icon" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-[#fafaf8] text-[#1a1714]">

    <!-- Preloader -->
    <div id="preloader" class="fixed inset-0 bg-[#fafaf8] z-50 flex items-center justify-center">
        <div class="text-center">
            <img src="assets/images/logo-b.png" class="w-[140px] logo mb-5" alt="INVEZ">
            <div class="w-28 h-[2px] bg-[#e5d9c8] overflow-hidden mx-auto">
                <div class="loading-bar"></div>
            </div>
        </div>
    </div>

    <?php include('components/navbar.php'); ?>

    <!-- Hero -->
    <section class="pt-16 bg-[#fafaf8]">
        <div class="relative overflow-hidden">
            <div class="absolute inset-0" style="background: radial-gradient(ellipse at 70% 100%, rgba(201,169,110,0.12) 0%, transparent 60%);"></div>
            <div class="max-w-6xl mx-auto px-6 py-20 md:py-28 relative z-10">
                <div class="fade-up max-w-2xl">
                    <p class="text-[#c9a96e] text-xs font-medium tracking-[0.25em] uppercase mb-4">PROPERTY LISTINGS</p>
                    <h1 class="text-4xl md:text-5xl font-semibold text-[#1a1714] leading-tight mb-5">
                        ทรัพย์สิน<br>
                        <span class="text-[#c9a96e]">ทั้งหมด</span>
                    </h1>
                    <div class="w-14 h-[2px] bg-[#c9a96e] mb-5"></div>
                    <p class="text-[#6b5f52] text-base leading-8">
                        รายการทรัพย์สิน <?= $total ?> รายการ ครอบคลุม <?= count($active_cats) ?> ประเภท
                        จากทีมที่ปรึกษา INVEZ
                    </p>
                </div>
            </div>
        </div>
        <div class="h-[3px] bg-gradient-to-r from-transparent via-[#c9a96e] to-transparent opacity-40"></div>
    </section>

    <!-- Filter & Listings -->
    <section class="py-16 md:py-20 px-6 bg-white">
        <div class="max-w-6xl mx-auto">

            <!-- Filter tabs -->
            <div class="flex flex-wrap gap-2 mb-10">
                <a href="/properties"
                   class="px-4 py-2 rounded-full text-sm font-medium border transition-colors duration-200
                          <?= $filter === 'all' ? 'bg-[#c9a96e] text-white border-[#c9a96e]' : 'border-[#e5d9c8] text-[#6b5f52] hover:border-[#c9a96e] hover:text-[#c9a96e]' ?>">
                    ทั้งหมด <span class="<?= $filter === 'all' ? 'text-white/80' : 'text-[#c9a96e]' ?>">(<?= $total ?>)</span>
                </a>
                <?php foreach ($property_categories as $cat):
                    if (!in_array($cat['id'], $active_cats, true)) continue; ?>
                <a href="/properties?cat=<?= $cat['id'] ?>"
                   class="px-4 py-2 rounded-full text-sm font-medium border transition-colors duration-200
                          <?= $filter === $cat['id'] ? 'bg-[#c9a96e] text-white border-[#c9a96e]' : 'border-[#e5d9c8] text-[#6b5f52] hover:border-[#c9a96e] hover:text-[#c9a96e]' ?>">
                    <?= htmlspecialchars($cat['label']) ?> <span class="<?= $filter === $cat['id'] ? 'text-white/80' : 'text-[#c9a96e]' ?>">(<?= $counts[$cat['id']] ?>)</span>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Property grid -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php foreach ($properties as $p):
                    $cover     = $p['first_image'] ? 'assets/images/properties/'.$p['id'].'/'.$p['first_image'] : null;
                    $cat_label = get_category_label($property_categories, $p['category']);
                ?>
                <a href="/property/<?= $p['id'] ?>"
                   class="fade-up bg-white rounded-xl border border-[#e5d9c8] hover:border-[#c9a96e] hover:shadow-md transition-all duration-300 flex flex-col overflow-hidden group">

                    <!-- Cover image -->
                    <div class="relative h-48 bg-[#f5efe4] overflow-hidden flex-shrink-0">
                        <?php if ($cover): ?>
                        <img src="<?= htmlspecialchars($cover) ?>"
                             alt="<?= htmlspecialchars($p['title']) ?>"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center">
                            <i data-feather="<?= htmlspecialchars(get_category_icon($property_categories, $p['category'])) ?>"
                               style="width:40px;height:40px;color:#c9a96e;opacity:0.4;"></i>
                        </div>
                        <?php endif; ?>
                        <span class="absolute top-3 left-3 text-[10px] font-medium tracking-wider uppercase text-[#c9a96e] bg-white/90 backdrop-blur-sm px-2.5 py-1 rounded-full">
                            <?= htmlspecialchars($cat_label) ?>
                        </span>
                        <span class="absolute top-3 right-3 text-[10px] font-medium text-[#6b5f52] bg-white/90 backdrop-blur-sm px-2.5 py-1 rounded-full">
                            <?= htmlspecialchars($p['status'] ?? '') ?>
                        </span>
                    </div>

                    <!-- Card body -->
                    <div class="p-5 flex-1 flex flex-col">
                        <h2 class="font-semibold text-[#1a1714] text-sm leading-6 mb-1">
                            <?= htmlspecialchars($p['title']) ?>
                        </h2>
                        <p class="text-[#9d8f82] text-xs mb-3 flex items-center gap-1">
                            <i data-feather="map-pin" style="width:11px;height:11px;flex-shrink:0;"></i>
                            <?= htmlspecialchars($p['location_short'] ?? '') ?>
                        </p>

                        <!-- Stats -->
                        <div class="flex items-center gap-3 text-xs text-[#6b5f52] mb-4">
                            <?php if (!empty($p['land_area'])): ?>
                            <span class="flex items-center gap-1">
                                <i data-feather="maximize-2" style="width:11px;height:11px;"></i>
                                <?= htmlspecialchars($p['land_area']) ?>
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($p['beds'])): ?>
                            <span class="flex items-center gap-1">
                                <i data-feather="<?= $p['category'] === 'hospital' ? 'activity' : 'moon' ?>" style="width:11px;height:11px;"></i>
                                <?= $p['beds'] ?> <?= $p['category'] === 'hospital' ? 'เตียง' : 'ห้องนอน' ?>
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($p['floors'])): ?>
                            <span class="flex items-center gap-1">
                                <i data-feather="layers" style="width:11px;height:11px;"></i>
                                <?= htmlspecialchars($p['floors']) ?>
                            </span>
                            <?php endif; ?>
                        </div>

                        <div class="mt-auto pt-3 border-t border-[#f0e8d8] flex items-center justify-between">
                            <span class="text-[#c9a96e] font-semibold text-sm">
                                <?= htmlspecialchars($p['price_display'] ?? '') ?>
                            </span>
                            <span class="text-[#c9a96e] text-xs font-medium flex items-center gap-1 group-hover:text-[#b8965e] transition-colors">
                                ดูรายละเอียด
                                <i data-feather="arrow-right" style="width:12px;height:12px;"></i>
                            </span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

        </div>
    </section>

    <!-- All 27 Categories -->
    <section class="py-16 md:py-20 px-6 bg-[#fafaf8] border-t border-[#e5d9c8]">
        <div class="max-w-6xl mx-auto">
            <div class="mb-10 fade-up">
                <p class="text-[#c9a96e] text-xs font-medium tracking-[0.25em] uppercase mb-3">PROPERTY TYPES</p>
                <h2 class="text-2xl font-semibold text-[#1a1714]">27 หมวดหมู่ที่เราดูแล</h2>
                <p class="text-[#9d8f82] text-sm mt-2">ทรัพย์สินที่ไม่มีในรายการ สามารถสอบถามได้โดยตรง</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                <?php foreach ($property_categories as $cat):
                    $has_listings = in_array($cat['id'], $active_cats, true);
                ?>
                <?php if ($has_listings): ?>
                <a href="/properties?cat=<?= $cat['id'] ?>"
                   class="fade-up flex flex-col items-center gap-2 p-4 rounded-xl border border-[#e5d9c8] bg-white hover:border-[#c9a96e] hover:shadow-sm transition-all duration-200 group text-center">
                    <div class="w-9 h-9 rounded-lg bg-[#fdf6e8] flex items-center justify-center group-hover:bg-[#c9a96e]/15 transition-colors">
                        <i data-feather="<?= $cat['icon'] ?>" style="width:16px;height:16px;color:#c9a96e;"></i>
                    </div>
                    <span class="text-xs font-medium text-[#1a1714] leading-4"><?= htmlspecialchars($cat['label']) ?></span>
                    <span class="text-[10px] text-[#c9a96e]"><?= $counts[$cat['id']] ?> รายการ</span>
                </a>
                <?php else: ?>
                <div class="fade-up flex flex-col items-center gap-2 p-4 rounded-xl border border-[#e5d9c8]/60 bg-white/50 text-center">
                    <div class="w-9 h-9 rounded-lg bg-[#fafaf8] flex items-center justify-center">
                        <i data-feather="<?= $cat['icon'] ?>" style="width:16px;height:16px;color:#b8a898;"></i>
                    </div>
                    <span class="text-xs font-medium text-[#9d8f82] leading-4"><?= htmlspecialchars($cat['label']) ?></span>
                    <span class="text-[10px] text-[#b8a898]">สอบถามได้</span>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-20 px-6 bg-[#c9a96e]">
        <div class="max-w-2xl mx-auto text-center fade-up">
            <h2 class="text-2xl md:text-3xl font-semibold text-white mb-4">ไม่เจอทรัพย์สินที่ต้องการ?</h2>
            <p class="text-white/80 text-sm md:text-base leading-7 mb-8">
                INVEZ มีเครือข่ายกว่า 27 ประเภทอสังหาริมทรัพย์ ติดต่อเราเพื่อหาทรัพย์ที่ตรงโจทย์
            </p>
            <a href="/contact"
               class="inline-flex items-center gap-2 bg-white text-[#c9a96e] px-8 py-3 rounded font-semibold hover:bg-[#fdf6e8] transition-colors duration-200 text-sm">
                <i data-feather="message-circle" style="width:16px;height:16px;"></i>
                ปรึกษาผู้เชี่ยวชาญ
            </a>
        </div>
    </section>

    <?php include('components/footer.php'); ?>

    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        feather.replace();
        const observer = new IntersectionObserver(
            (entries) => entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('show'); }),
            { threshold: 0.1 }
        );
        document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
        window.addEventListener('load', () => {
            setTimeout(() => document.getElementById('preloader').classList.add('hide'), 500);
        });
    </script>
</body>
</html>
