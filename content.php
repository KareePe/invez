<?php
$current_page = 'content';
require_once('config/lang.php');
require_once('config/db.php');

// Sort — whitelist only, mapped to fixed ORDER BY clauses (never interpolate raw input)
$sort_map = [
    'newest' => 'created_at DESC, id DESC',
    'oldest' => 'created_at ASC, id ASC',
];
$sort = isset($_GET['sort']) && isset($sort_map[$_GET['sort']]) ? $_GET['sort'] : 'newest';

$rows = db()->query('SELECT id, icon, category, title, title_en, excerpt, excerpt_en FROM articles WHERE is_active = 1 ORDER BY ' . $sort_map[$sort])->fetchAll();
$articles = [];
foreach ($rows as $row) {
    $articles[$row['id']] = $row;
}
?>
<!DOCTYPE html>
<html lang="<?= lang() ?>">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>คอนเทนท์ | บทความอสังหาริมทรัพย์ INVEZ</title>
    <meta name="description" content="บทความความรู้อสังหาริมทรัพย์จาก INVEZ ครอบคลุมทุกประเภท ทั้งโรงแรม คอนโด ที่ดิน โรงงาน และอีกมากมาย คำแนะนำจากผู้เชี่ยวชาญสำหรับผู้ซื้อและนักลงทุน" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://www.invez.biz/content" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="คอนเทนท์ | บทความอสังหาริมทรัพย์ INVEZ" />
    <meta property="og:description" content="บทความความรู้อสังหาริมทรัพย์ครบทุกประเภท จากทีมผู้เชี่ยวชาญ INVEZ" />
    <meta property="og:url" content="https://www.invez.biz/content" />
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
                    <?= t('คอนเทนท์ความรู้อสังหาฯ','Real Estate Knowledge Center') ?>
                </h1>
                <p class="text-[#6b5f52] text-sm leading-6">
                    <?= count($articles) ?> <?= t('บทความ จากทีมผู้เชี่ยวชาญ INVEZ','articles from the INVEZ expert team') ?>
                </p>
            </div>
        </div>
    </section>

    <!-- Articles Grid -->
    <section class="py-12 md:py-16 px-6 bg-white">
        <div class="max-w-6xl mx-auto">

            <?php if (!empty($articles)): ?>
            <div class="flex justify-end mb-8">
                <?php include('components/sort-select.php'); ?>
            </div>
            <?php endif; ?>

            <div id="article-grid" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($articles as $id => $article): ?>
                <a href="/article/<?= $id ?>"
                    class="bg-white rounded-lg border border-[#e8e4df] hover:border-[#c9a96e] transition-colors duration-150 flex flex-col overflow-hidden group">

                    <div class="p-5 flex-1 flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <i data-feather="<?= htmlspecialchars($article['icon']) ?>" style="width:15px;height:15px;color:#c9a96e;"></i>
                            <span class="text-[10px] text-[#9d8f82]">
                                <?= htmlspecialchars($article['category']) ?>
                            </span>
                        </div>

                        <h2 class="font-medium text-[#1a1714] text-sm leading-6 mb-2 flex-1">
                            <?= htmlspecialchars(tf($article, 'title')) ?>
                        </h2>

                        <p class="text-[#9d8f82] text-xs leading-5 line-clamp-2">
                            <?= htmlspecialchars(tf($article, 'excerpt')) ?>
                        </p>
                    </div>

                    <div class="px-5 pb-4 pt-3 border-t border-[#f0ebe3]">
                        <span class="text-xs text-[#6b5f52] group-hover:text-[#c9a96e] transition-colors duration-150">
                            <?= t('อ่านบทความ →','Read Article →') ?>
                        </span>
                    </div>

                </a>
                <?php endforeach; ?>
            </div>

            <div class="flex justify-center gap-3 mt-8">
                <button type="button" id="load-more-articles" class="hidden px-6 py-2.5 rounded text-sm font-medium border border-[#e8e4df] text-[#1a1714] hover:border-[#c9a96e] hover:text-[#c9a96e] transition-colors duration-150">
                    <?= t('แสดงเพิ่มเติม','Load More') ?>
                </button>
                <button type="button" id="show-less-articles" class="hidden px-6 py-2.5 rounded text-sm font-medium border border-[#e8e4df] text-[#6b5f52] hover:border-[#c9a96e] hover:text-[#c9a96e] transition-colors duration-150">
                    <?= t('แสดงน้อยลง','Show Less') ?>
                </button>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-14 px-6 bg-[#1a1714]">
        <div class="max-w-2xl mx-auto text-center">
            <h2 class="text-xl font-semibold text-white mb-3"><?= t('สนใจทรัพย์สินประเภทไหน?','Interested in a Property Type?') ?></h2>
            <p class="text-[#9d8f82] text-sm leading-6 mb-6">
                <?= t('ทีมงาน INVEZ พร้อมให้คำปรึกษาและหาทรัพย์สินที่ตรงโจทย์ให้คุณ','The INVEZ team is ready to advise and find the right property for you.') ?>
            </p>
            <a href="/contact"
                class="inline-flex items-center gap-2 bg-[#c9a96e] text-white px-6 py-2.5 rounded text-sm font-medium hover:bg-[#b8965e] transition-colors duration-150">
                <?= t('ปรึกษาผู้เชี่ยวชาญ','Consult an Expert') ?>
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

        (function () {
            const PAGE_SIZE = 9, STEP = 3;
            const grid = document.getElementById('article-grid');
            const cards = Array.from(document.querySelectorAll('#article-grid > a'));
            const moreBtn = document.getElementById('load-more-articles');
            const lessBtn = document.getElementById('show-less-articles');
            let shown = PAGE_SIZE;
            function render() {
                cards.forEach((card, i) => card.classList.toggle('hidden', i >= shown));
                moreBtn.classList.toggle('hidden', shown >= cards.length);
                lessBtn.classList.toggle('hidden', shown <= PAGE_SIZE);
            }
            moreBtn?.addEventListener('click', () => { shown += STEP; render(); });
            lessBtn?.addEventListener('click', () => {
                shown = PAGE_SIZE;
                render();
                grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            render();
        })();
    </script>
</body>
</html>
