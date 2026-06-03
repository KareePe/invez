<?php
$current_page = 'portfolio';
require_once('config/lang.php');
require_once('config/db.php');

$stmt = db()->query(
    "SELECT p.id, p.category, p.category_en, p.title, p.title_en,
            p.description, p.description_en,
            (SELECT GROUP_CONCAT(filename ORDER BY sort_order ASC SEPARATOR '|')
             FROM portfolio_images WHERE portfolio_id = p.id) AS images_csv
     FROM portfolios p
     WHERE p.is_active = 1
     ORDER BY p.sort_order ASC, p.id ASC"
);
$portfolios = $stmt->fetchAll();
$total = count($portfolios);
?>
<!DOCTYPE html>
<html lang="<?= lang() ?>">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title><?= t('ผลงาน','Portfolio') ?> | INVEZ <?= t('บริษัท โตโยซัพพลาย จำกัด','Toyo Supply Co., Ltd.') ?></title>
    <meta name="description" content="<?= t('ผลงานและโครงการที่ผ่านมาของ INVEZ บริษัท โตโยซัพพลาย จำกัด','Past projects and portfolio of INVEZ — Toyo Supply Co., Ltd.') ?>" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://www.invez.biz/portfolio" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?= t('ผลงาน','Portfolio') ?> | INVEZ" />
    <meta property="og:description" content="<?= t('ผลงานและโครงการที่ผ่านมาของทีม INVEZ','Past projects and portfolio from the INVEZ team') ?>" />
    <meta property="og:url" content="https://www.invez.biz/portfolio" />
    <meta property="og:site_name" content="INVEZ" />
    <meta name="theme-color" content="#ffffff" />
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
                    <?= t('ผลงานของเรา','Our Portfolio') ?>
                </h1>
                <p class="text-[#6b5f52] text-sm leading-6">
                    <?= $total ?> <?= t('ผลงาน จากทีม INVEZ','projects from the INVEZ team') ?>
                </p>
            </div>
        </div>
    </section>

    <!-- Portfolio Grid -->
    <section class="py-12 md:py-16 px-6 bg-white">
        <div class="max-w-6xl mx-auto">

            <?php if (empty($portfolios)): ?>
            <div class="text-center py-20 text-[#9d8f82] text-sm">
                <?= t('ยังไม่มีผลงาน','No portfolio items yet') ?>
            </div>
            <?php else: ?>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($portfolios as $p):
                    $images    = !empty($p['images_csv']) ? array_values(array_filter(explode('|', $p['images_csv']))) : [];
                    $img_count = count($images);
                    $cat       = tf($p, 'category');
                    $title     = tf($p, 'title');
                    $desc      = tf($p, 'description');
                ?>
                <div class="bg-white rounded-lg border border-[#e8e4df] hover:border-[#c9a96e] transition-colors duration-150 flex flex-col overflow-hidden group">

                    <!-- Image / slider -->
                    <div class="relative h-52 bg-[#f5f3f0] overflow-hidden flex-shrink-0 touch-manipulation"<?= $img_count > 1 ? ' data-slider' : '' ?>>
                        <?php if (!empty($images)): ?>
                            <?php foreach ($images as $idx => $img): ?>
                            <img src="assets/images/portfolios/<?= $p['id'] ?>/<?= htmlspecialchars($img) ?>"
                                 alt="<?= htmlspecialchars($title) ?>"
                                 class="<?= $img_count > 1
                                     ? ('absolute inset-0 w-full h-full object-cover transition-opacity duration-300' . ($idx !== 0 ? ' opacity-0' : ''))
                                     : 'w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-500' ?>"
                                 <?= $img_count > 1 ? 'data-slide="'.$idx.'"' : '' ?>>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#c9a96e" stroke-width="1.5" opacity="0.35"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($cat)): ?>
                        <span class="absolute top-3 left-3 text-[10px] font-medium text-[#6b5f52] bg-white/95 px-2 py-1 rounded z-10">
                            <?= htmlspecialchars($cat) ?>
                        </span>
                        <?php endif; ?>

                        <?php if ($img_count > 1): ?>
                        <button type="button" class="absolute left-1.5 top-1/2 -translate-y-1/2 w-6 h-6 bg-black/30 hover:bg-black/50 rounded-full text-white flex items-center justify-center z-10 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity" data-prev aria-label="<?= t('ก่อนหน้า','Previous') ?>">‹</button>
                        <button type="button" class="absolute right-1.5 top-1/2 -translate-y-1/2 w-6 h-6 bg-black/30 hover:bg-black/50 rounded-full text-white flex items-center justify-center z-10 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity" data-next aria-label="<?= t('ถัดไป','Next') ?>">›</button>
                        <span class="absolute bottom-2 right-2 text-[9px] text-white bg-black/40 px-1.5 py-0.5 rounded z-10" data-counter>1 / <?= $img_count ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Card body -->
                    <div class="p-4 flex-1 flex flex-col">
                        <h2 class="font-medium text-[#1a1714] text-sm leading-5 mb-2">
                            <?= htmlspecialchars($title) ?>
                        </h2>
                        <?php if (!empty($desc)): ?>
                        <p class="text-[#9d8f82] text-xs leading-5 line-clamp-3 flex-1">
                            <?= htmlspecialchars($desc) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
    </section>

    <!-- CTA -->
    <section class="py-14 px-6 bg-[#1a1714]">
        <div class="max-w-2xl mx-auto text-center">
            <h2 class="text-xl font-semibold text-white mb-3"><?= t('สนใจทำงานร่วมกับเรา?','Interested in Working With Us?') ?></h2>
            <p class="text-[#9d8f82] text-sm leading-6 mb-6">
                <?= t('ทีมงาน INVEZ พร้อมให้คำปรึกษาและดูแลทุกโครงการด้วยความใส่ใจ','The INVEZ team is ready to advise and handle every project with care.') ?>
            </p>
            <a href="/contact"
               class="inline-flex items-center gap-2 bg-[#c9a96e] text-white px-6 py-2.5 rounded text-sm font-medium hover:bg-[#b8965e] transition-colors duration-150">
                <?= t('ติดต่อเรา','Contact Us') ?>
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

        // Image slider (same as properties page)
        document.querySelectorAll('[data-slider]').forEach(slider => {
            const slides  = slider.querySelectorAll('[data-slide]');
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
            slider.addEventListener('touchstart', e => { txStart = e.touches[0].clientX; tyStart = e.touches[0].clientY; swiped = false; }, { passive: true });
            slider.addEventListener('touchend', e => {
                const dx = txStart - e.changedTouches[0].clientX;
                const dy = tyStart - e.changedTouches[0].clientY;
                if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) { swiped = true; dx > 0 ? go(cur + 1) : go(cur - 1); }
            }, { passive: true });
        });
    </script>
</body>
</html>
