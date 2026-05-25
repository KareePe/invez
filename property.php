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
    <meta name="theme-color" content="#fafaf8" />
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

<body class="bg-[#fafaf8] text-[#1a1714]">

    <!-- Preloader -->
    <div id="preloader" class="fixed inset-0 bg-[#fafaf8] z-50 flex items-center justify-center">
        <div class="text-center">
            <img src="assets/images/logo-b.png" class="w-[120px] logo mb-4" alt="INVEZ">
            <div class="w-24 h-[2px] bg-[#e5d9c8] overflow-hidden mx-auto">
                <div class="loading-bar"></div>
            </div>
        </div>
    </div>

    <?php include('components/navbar.php'); ?>

    <!-- Hero -->
    <section class="pt-16 bg-[#fafaf8]">
        <div class="relative overflow-hidden">
            <div class="absolute inset-0" style="background: radial-gradient(ellipse at 70% 100%, rgba(201,169,110,0.1) 0%, transparent 55%);"></div>
            <div class="max-w-4xl mx-auto px-6 py-16 md:py-24 relative z-10">

                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-xs text-[#9d8f82] mb-8 fade-up" aria-label="breadcrumb">
                    <a href="/properties" class="hover:text-[#c9a96e] transition-colors">ทรัพย์สิน</a>
                    <i data-feather="chevron-right" style="width:13px;height:13px;"></i>
                    <a href="/properties?cat=<?= htmlspecialchars($p['category']) ?>" class="hover:text-[#c9a96e] transition-colors">
                        <?= htmlspecialchars($cat_label) ?>
                    </a>
                    <i data-feather="chevron-right" style="width:13px;height:13px;"></i>
                    <span class="text-[#c9a96e]"><?= htmlspecialchars($p['title']) ?></span>
                </nav>

                <div class="fade-up">
                    <span class="inline-block text-[10px] font-medium tracking-wider uppercase text-[#c9a96e] bg-[#fdf6e8] px-3 py-1.5 rounded-full border border-[#e5d9c8] mb-5">
                        <?= htmlspecialchars($cat_label) ?>
                    </span>
                    <h1 class="text-3xl md:text-4xl font-semibold text-[#1a1714] leading-tight mb-3">
                        <?= htmlspecialchars($p['title']) ?>
                    </h1>
                    <p class="text-[#9d8f82] text-sm mb-4"><?= htmlspecialchars($p['subtitle']) ?></p>
                    <div class="w-14 h-[2px] bg-[#c9a96e]"></div>
                </div>

            </div>
        </div>
        <div class="h-[3px] bg-gradient-to-r from-transparent via-[#c9a96e] to-transparent opacity-40"></div>
    </section>

    <!-- Main Content -->
    <section class="py-12 md:py-16 px-6 bg-white">
        <div class="max-w-4xl mx-auto">

            <!-- Cover image -->
            <?php if ($cover): ?>
            <div class="fade-up mb-10 rounded-xl overflow-hidden border border-[#e5d9c8]">
                <img src="<?= htmlspecialchars($cover) ?>"
                     alt="<?= htmlspecialchars($p['title']) ?>"
                     class="w-full max-h-[480px] object-cover">
            </div>
            <?php endif; ?>

            <!-- Price + Status bar -->
            <div class="fade-up flex flex-wrap items-center justify-between gap-4 bg-[#fdf6e8] border border-[#e5d9c8] rounded-xl px-6 py-5 mb-10">
                <div>
                    <p class="text-xs text-[#9d8f82] mb-1">ราคา</p>
                    <p class="text-2xl md:text-3xl font-semibold text-[#c9a96e]"><?= htmlspecialchars($p['price_display']) ?></p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-[#9d8f82] mb-1">สถานะ</p>
                    <p class="text-sm font-medium text-[#1a1714]"><?= htmlspecialchars($p['status']) ?></p>
                </div>
            </div>

            <!-- Key stats -->
            <div class="fade-up grid grid-cols-2 sm:grid-cols-4 gap-4 mb-10">
                <?php if (!empty($p['land_area'])): ?>
                <div class="bg-[#fafaf8] border border-[#e5d9c8] rounded-xl p-4 text-center">
                    <i data-feather="maximize-2" style="width:18px;height:18px;color:#c9a96e;" class="mx-auto mb-2"></i>
                    <p class="text-xs text-[#9d8f82] mb-0.5">ที่ดิน</p>
                    <p class="text-sm font-semibold text-[#1a1714]"><?= htmlspecialchars($p['land_area']) ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($p['usable_area'])): ?>
                <div class="bg-[#fafaf8] border border-[#e5d9c8] rounded-xl p-4 text-center">
                    <i data-feather="grid" style="width:18px;height:18px;color:#c9a96e;" class="mx-auto mb-2"></i>
                    <p class="text-xs text-[#9d8f82] mb-0.5">พื้นที่ใช้สอย</p>
                    <p class="text-sm font-semibold text-[#1a1714]"><?= htmlspecialchars($p['usable_area']) ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($p['floors'])): ?>
                <div class="bg-[#fafaf8] border border-[#e5d9c8] rounded-xl p-4 text-center">
                    <i data-feather="layers" style="width:18px;height:18px;color:#c9a96e;" class="mx-auto mb-2"></i>
                    <p class="text-xs text-[#9d8f82] mb-0.5">จำนวนชั้น</p>
                    <p class="text-sm font-semibold text-[#1a1714]"><?= htmlspecialchars($p['floors']) ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($p['beds'])): ?>
                <div class="bg-[#fafaf8] border border-[#e5d9c8] rounded-xl p-4 text-center">
                    <i data-feather="<?= $p['category'] === 'hospital' ? 'activity' : 'moon' ?>" style="width:18px;height:18px;color:#c9a96e;" class="mx-auto mb-2"></i>
                    <p class="text-xs text-[#9d8f82] mb-0.5"><?= $p['category'] === 'hospital' ? 'เตียง' : 'ห้องนอน' ?></p>
                    <p class="text-sm font-semibold text-[#1a1714]"><?= $p['beds'] ?> <?= $p['category'] === 'hospital' ? 'เตียง' : 'ห้อง' ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($p['bathrooms'])): ?>
                <div class="bg-[#fafaf8] border border-[#e5d9c8] rounded-xl p-4 text-center">
                    <i data-feather="droplet" style="width:18px;height:18px;color:#c9a96e;" class="mx-auto mb-2"></i>
                    <p class="text-xs text-[#9d8f82] mb-0.5">ห้องน้ำ</p>
                    <p class="text-sm font-semibold text-[#1a1714]"><?= $p['bathrooms'] ?> ห้อง</p>
                </div>
                <?php endif; ?>
                <?php if (!empty($p['parking'])): ?>
                <div class="bg-[#fafaf8] border border-[#e5d9c8] rounded-xl p-4 text-center">
                    <i data-feather="truck" style="width:18px;height:18px;color:#c9a96e;" class="mx-auto mb-2"></i>
                    <p class="text-xs text-[#9d8f82] mb-0.5">จอดรถ</p>
                    <p class="text-sm font-semibold text-[#1a1714]"><?= htmlspecialchars($p['parking']) ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($p['offices'])): ?>
                <div class="bg-[#fafaf8] border border-[#e5d9c8] rounded-xl p-4 text-center">
                    <i data-feather="briefcase" style="width:18px;height:18px;color:#c9a96e;" class="mx-auto mb-2"></i>
                    <p class="text-xs text-[#9d8f82] mb-0.5">ห้องสำนักงาน</p>
                    <p class="text-sm font-semibold text-[#1a1714]"><?= $p['offices'] ?> ห้อง</p>
                </div>
                <?php endif; ?>
                <div class="bg-[#fafaf8] border border-[#e5d9c8] rounded-xl p-4 text-center">
                    <i data-feather="map-pin" style="width:18px;height:18px;color:#c9a96e;" class="mx-auto mb-2"></i>
                    <p class="text-xs text-[#9d8f82] mb-0.5">ที่ตั้ง</p>
                    <p class="text-sm font-semibold text-[#1a1714]"><?= htmlspecialchars($p['location_short']) ?></p>
                </div>
            </div>

            <div class="h-px bg-[#f0e8d8] mb-10"></div>

            <!-- Description -->
            <div class="fade-up mb-10">
                <h2 class="text-lg font-semibold text-[#1a1714] mb-4">รายละเอียด</h2>
                <p class="text-[#5a4e42] text-base leading-9"><?= htmlspecialchars($p['description']) ?></p>
                <p class="text-sm text-[#9d8f82] mt-3 flex items-center gap-1.5">
                    <i data-feather="map-pin" style="width:13px;height:13px;"></i>
                    <?= htmlspecialchars($p['location']) ?>
                </p>
            </div>

            <!-- Highlights -->
            <?php if (!empty($p['highlights'])): ?>
            <div class="fade-up mb-10">
                <h2 class="text-lg font-semibold text-[#1a1714] mb-5">จุดเด่น</h2>
                <div class="space-y-3">
                    <?php foreach ($p['highlights'] as $i => $h): ?>
                    <div class="flex gap-4 items-start bg-[#fafaf8] border border-[#e5d9c8] rounded-xl p-4 hover:border-[#c9a96e] transition-colors group">
                        <div class="w-8 h-8 rounded-full bg-[#c9a96e] text-white flex items-center justify-center font-semibold text-sm flex-shrink-0 group-hover:bg-[#b8965e] transition-colors">
                            <?= $i + 1 ?>
                        </div>
                        <p class="text-[#5a4e42] text-sm leading-7 pt-0.5"><?= htmlspecialchars($h) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Additional images gallery -->
            <?php if (count($p['images']) > 1): ?>
            <div class="fade-up mb-10">
                <h2 class="text-lg font-semibold text-[#1a1714] mb-5">รูปภาพทั้งหมด</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <?php foreach ($p['images'] as $img): ?>
                    <div class="rounded-xl overflow-hidden border border-[#e5d9c8] aspect-[4/3]">
                        <img src="assets/images/properties/<?= $id ?>/<?= htmlspecialchars($img) ?>"
                             alt="<?= htmlspecialchars($p['title']) ?>"
                             class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </section>

    <!-- Back to listing -->
    <section class="py-8 px-6 bg-[#fafaf8] border-t border-[#e5d9c8]">
        <div class="max-w-4xl mx-auto flex items-center justify-between gap-4">
            <a href="/properties?cat=<?= htmlspecialchars($p['category']) ?>"
               class="flex items-center gap-2 text-[#6b5f52] hover:text-[#c9a96e] transition-colors text-sm">
                <i data-feather="arrow-left" style="width:15px;height:15px;"></i>
                <?= htmlspecialchars($cat_label) ?>ทั้งหมด
            </a>
            <a href="/properties"
               class="text-xs border border-[#e5d9c8] hover:border-[#c9a96e] hover:text-[#c9a96e] text-[#9d8f82] px-4 py-2 rounded-full transition-colors">
                ดูทั้งหมด
            </a>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-16 px-6 bg-[#c9a96e]">
        <div class="max-w-2xl mx-auto text-center fade-up">
            <h2 class="text-xl md:text-2xl font-semibold text-white mb-3">สนใจทรัพย์สินนี้?</h2>
            <p class="text-white/80 text-sm leading-7 mb-6">ทีมงาน INVEZ พร้อมให้ข้อมูลและเจรจาดีลที่ตรงโจทย์ให้คุณ</p>
            <a href="/contact"
               class="inline-flex items-center gap-2 bg-white text-[#c9a96e] px-7 py-3 rounded font-semibold hover:bg-[#fdf6e8] transition-colors duration-200 text-sm">
                <i data-feather="phone" style="width:15px;height:15px;"></i>
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
            { threshold: 0.1 }
        );
        document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
        window.addEventListener('load', () => {
            setTimeout(() => document.getElementById('preloader').classList.add('hide'), 500);
        });
    </script>
</body>
</html>
