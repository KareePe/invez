<?php
$current_page = 'properties';
require_once('config/db.php');
require_once('properties_data.php');

$filter      = isset($_GET['cat']) ? $_GET['cat'] : 'all';
$valid_cats  = array_merge(['all'], array_column($property_categories, 'id'));
if (!in_array($filter, $valid_cats, true)) $filter = 'all';

if ($filter === 'all') {
    $stmt = db()->query(
        "SELECT p.id, p.category, p.title, p.subtitle, p.price_display, p.location_short,
                p.status, p.land_area, p.beds, p.floors,
                (SELECT GROUP_CONCAT(filename ORDER BY sort_order ASC SEPARATOR '|') FROM property_images WHERE property_id = p.id) AS images_csv
         FROM properties p WHERE p.is_active = 1
         ORDER BY p.sort_order ASC, p.id ASC"
    );
} else {
    $stmt = db()->prepare(
        "SELECT p.id, p.category, p.title, p.subtitle, p.price_display, p.location_short,
                p.status, p.land_area, p.beds, p.floors,
                (SELECT GROUP_CONCAT(filename ORDER BY sort_order ASC SEPARATOR '|') FROM property_images WHERE property_id = p.id) AS images_csv
         FROM properties p WHERE p.is_active = 1 AND p.category = ?
         ORDER BY p.sort_order ASC, p.id ASC"
    );
    $stmt->execute([$filter]);
}
$properties = $stmt->fetchAll();

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

<body class="bg-white text-[#1a1714]">

    <!-- Preloader -->
    <div id="preloader" class="fixed inset-0 bg-white z-50 flex items-center justify-center">
        <div class="text-center">
            <img src="assets/images/logo-b.png" class="w-[120px] logo" alt="INVEZ">
        </div>
    </div>

    <?php include('components/navbar.php'); ?>

    <!-- Hero -->
    <section class="pt-14 bg-white border-b border-[#e8e4df]">
        <div class="max-w-6xl mx-auto px-6 py-12 md:py-16">
            <div class="fade-up max-w-xl">
                <h1 class="text-2xl md:text-3xl font-semibold text-[#1a1714] leading-snug mb-3">
                    ทรัพย์สินทั้งหมด
                </h1>
                <p class="text-[#6b5f52] text-sm leading-6">
                    <?= $total ?> รายการ · <?= count($active_cats) ?> ประเภท จากทีมที่ปรึกษา INVEZ
                </p>
            </div>
        </div>
    </section>

    <!-- Filter & Listings -->
    <section class="py-12 md:py-16 px-6 bg-white">
        <div class="max-w-6xl mx-auto">

            <!-- Filter tabs -->
            <div class="flex flex-wrap gap-2 mb-10">
                <a href="/properties"
                   class="px-3.5 py-1.5 rounded text-sm transition-colors duration-150
                          <?= $filter === 'all' ? 'bg-[#1a1714] text-white' : 'border border-[#e8e4df] text-[#6b5f52] hover:border-[#1a1714] hover:text-[#1a1714]' ?>">
                    ทั้งหมด <span class="<?= $filter === 'all' ? 'text-white/70' : 'text-[#9d8f82]' ?>">(<?= $total ?>)</span>
                </a>
                <?php foreach ($property_categories as $cat):
                    if (!in_array($cat['id'], $active_cats, true)) continue; ?>
                <a href="/properties?cat=<?= $cat['id'] ?>"
                   class="px-3.5 py-1.5 rounded text-sm transition-colors duration-150
                          <?= $filter === $cat['id'] ? 'bg-[#1a1714] text-white' : 'border border-[#e8e4df] text-[#6b5f52] hover:border-[#1a1714] hover:text-[#1a1714]' ?>">
                    <?= htmlspecialchars($cat['label']) ?> <span class="<?= $filter === $cat['id'] ? 'text-white/70' : 'text-[#9d8f82]' ?>">(<?= $counts[$cat['id']] ?>)</span>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Property grid -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($properties as $p):
                    $images    = !empty($p['images_csv']) ? array_values(array_filter(explode('|', $p['images_csv']))) : [];
                    $cat_label = get_category_label($property_categories, $p['category']);
                ?>
                <a href="/property/<?= $p['id'] ?>"
                   class="bg-white rounded-lg border border-[#e8e4df] hover:border-[#c9a96e] transition-colors duration-150 flex flex-col overflow-hidden group">

                    <!-- Image / slider -->
                    <?php $img_count = count($images); ?>
                    <div class="relative h-44 bg-[#f5f3f0] overflow-hidden flex-shrink-0"<?= $img_count > 1 ? ' data-slider' : '' ?>>
                        <?php if (!empty($images)): ?>
                            <?php foreach ($images as $idx => $img): ?>
                            <img src="assets/images/properties/<?= $p['id'] ?>/<?= htmlspecialchars($img) ?>"
                                 alt="<?= htmlspecialchars($p['title']) ?>"
                                 class="<?= $img_count > 1
                                     ? ('absolute inset-0 w-full h-full object-cover transition-opacity duration-300' . ($idx !== 0 ? ' opacity-0' : ''))
                                     : 'w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-500' ?>"
                                 <?= $img_count > 1 ? 'data-slide="'.$idx.'"' : '' ?>>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center">
                                <i data-feather="<?= htmlspecialchars(get_category_icon($property_categories, $p['category'])) ?>"
                                   style="width:32px;height:32px;color:#c9a96e;opacity:0.35;"></i>
                            </div>
                        <?php endif; ?>
                        <span class="absolute top-3 left-3 text-[10px] font-medium text-[#6b5f52] bg-white/95 px-2 py-1 rounded z-10">
                            <?= htmlspecialchars($cat_label) ?>
                        </span>
                        <?php if (!empty($p['status'])): ?>
                        <span class="absolute top-3 right-3 text-[10px] text-[#6b5f52] bg-white/95 px-2 py-1 rounded z-10">
                            <?= htmlspecialchars($p['status']) ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($img_count > 1): ?>
                        <button type="button" class="absolute left-1.5 top-1/2 -translate-y-1/2 w-6 h-6 bg-black/30 hover:bg-black/50 rounded-full text-white flex items-center justify-center z-10 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity" data-prev aria-label="ก่อนหน้า">‹</button>
                        <button type="button" class="absolute right-1.5 top-1/2 -translate-y-1/2 w-6 h-6 bg-black/30 hover:bg-black/50 rounded-full text-white flex items-center justify-center z-10 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity" data-next aria-label="ถัดไป">›</button>
                        <span class="absolute bottom-2 right-2 text-[9px] text-white bg-black/40 px-1.5 py-0.5 rounded z-10" data-counter>1 / <?= $img_count ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Card body -->
                    <div class="p-4 flex-1 flex flex-col">
                        <h2 class="font-medium text-[#1a1714] text-sm leading-5 mb-1.5">
                            <?= htmlspecialchars($p['title']) ?>
                        </h2>
                        <?php if (!empty($p['location_short'])): ?>
                        <p class="text-[#9d8f82] text-xs mb-3 flex items-center gap-1">
                            <i data-feather="map-pin" style="width:10px;height:10px;flex-shrink:0;"></i>
                            <?= htmlspecialchars($p['location_short']) ?>
                        </p>
                        <?php endif; ?>

                        <!-- Stats -->
                        <div class="flex items-center gap-3 text-xs text-[#6b5f52] mb-3">
                            <?php if (!empty($p['land_area'])): ?>
                            <span><?= htmlspecialchars($p['land_area']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($p['beds'])): ?>
                            <span><?= $p['beds'] ?> <?= $p['category'] === 'hospital' ? 'เตียง' : 'ห้องนอน' ?></span>
                            <?php endif; ?>
                            <?php if (!empty($p['floors'])): ?>
                            <span><?= htmlspecialchars($p['floors']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="mt-auto pt-3 border-t border-[#f0ebe3] flex items-center justify-between">
                            <span class="text-[#c9a96e] font-medium text-sm">
                                <?= htmlspecialchars($p['price_display'] ?? '') ?>
                            </span>
                            <span class="text-[#9d8f82] text-xs group-hover:text-[#1a1714] transition-colors">
                                ดูรายละเอียด →
                            </span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

        </div>
    </section>

    <!-- All Categories -->
    <section class="py-12 md:py-16 px-6 bg-[#fafaf8] border-t border-[#e8e4df]">
        <div class="max-w-6xl mx-auto">
            <div class="mb-8">
                <h2 class="text-base font-semibold text-[#1a1714]">27 หมวดหมู่ที่เราดูแล</h2>
                <p class="text-[#9d8f82] text-sm mt-1">ทรัพย์สินที่ไม่มีในรายการ สามารถสอบถามได้โดยตรง</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
                <?php foreach ($property_categories as $cat):
                    $has_listings = in_array($cat['id'], $active_cats, true);
                ?>
                <?php if ($has_listings): ?>
                <a href="/properties?cat=<?= $cat['id'] ?>"
                   class="flex items-center gap-2.5 p-3 rounded-lg border border-[#e8e4df] bg-white hover:border-[#c9a96e] transition-colors duration-150">
                    <i data-feather="<?= $cat['icon'] ?>" style="width:14px;height:14px;color:#c9a96e;flex-shrink:0;"></i>
                    <span class="text-xs text-[#1a1714] leading-4 truncate"><?= htmlspecialchars($cat['label']) ?></span>
                </a>
                <?php else: ?>
                <div class="flex items-center gap-2.5 p-3 rounded-lg border border-[#f0ebe3] bg-white/60">
                    <i data-feather="<?= $cat['icon'] ?>" style="width:14px;height:14px;color:#b8a898;flex-shrink:0;"></i>
                    <span class="text-xs text-[#9d8f82] leading-4 truncate"><?= htmlspecialchars($cat['label']) ?></span>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-14 px-6 bg-[#1a1714]">
        <div class="max-w-2xl mx-auto text-center">
            <h2 class="text-xl font-semibold text-white mb-3">ไม่เจอทรัพย์สินที่ต้องการ?</h2>
            <p class="text-[#9d8f82] text-sm leading-6 mb-6">
                INVEZ มีเครือข่ายกว่า 27 ประเภทอสังหาริมทรัพย์ ติดต่อเราเพื่อหาทรัพย์ที่ตรงโจทย์
            </p>
            <a href="/contact"
               class="inline-flex items-center gap-2 bg-[#c9a96e] text-white px-6 py-2.5 rounded text-sm font-medium hover:bg-[#b8965e] transition-colors duration-150">
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
            { threshold: 0.05 }
        );
        document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
        window.addEventListener('load', () => {
            setTimeout(() => document.getElementById('preloader').classList.add('hide'), 400);
        });
        document.querySelectorAll('[data-slider]').forEach(slider => {
            const slides = slider.querySelectorAll('[data-slide]');
            const counter = slider.querySelector('[data-counter]');
            if (!slides.length) return;
            let cur = 0, txStart = 0, tyStart = 0, swiped = false;
            function go(n) {
                slides[cur].classList.add('opacity-0');
                cur = (n + slides.length) % slides.length;
                slides[cur].classList.remove('opacity-0');
                if (counter) counter.textContent = (cur + 1) + ' / ' + slides.length;
            }
            slider.querySelector('[data-prev]')?.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); go(cur - 1); });
            slider.querySelector('[data-next]')?.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); go(cur + 1); });
            slider.addEventListener('touchstart', e => {
                txStart = e.touches[0].clientX;
                tyStart = e.touches[0].clientY;
                swiped = false;
            }, { passive: true });
            slider.addEventListener('touchend', e => {
                const dx = txStart - e.changedTouches[0].clientX;
                const dy = tyStart - e.changedTouches[0].clientY;
                if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) {
                    swiped = true;
                    dx > 0 ? go(cur + 1) : go(cur - 1);
                }
            }, { passive: true });
            slider.addEventListener('click', e => { if (swiped) { e.preventDefault(); e.stopPropagation(); swiped = false; } });
        });
    </script>
</body>
</html>
