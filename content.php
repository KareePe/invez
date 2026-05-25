<?php
$current_page = 'content';
require_once('config/db.php');

$rows = db()->query('SELECT id, icon, category, title, excerpt FROM articles WHERE is_active = 1 ORDER BY id ASC')->fetchAll();
$articles = [];
foreach ($rows as $row) {
    $articles[$row['id']] = $row;
}
?>
<!DOCTYPE html>
<html lang="th">

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
                    <p class="text-[#c9a96e] text-xs font-medium tracking-[0.25em] uppercase mb-4">KNOWLEDGE BASE</p>
                    <h1 class="text-4xl md:text-5xl font-semibold text-[#1a1714] leading-tight mb-5">
                        คอนเทนท์<br>
                        <span class="text-[#c9a96e]">ความรู้อสังหาฯ</span>
                    </h1>
                    <div class="w-14 h-[2px] bg-[#c9a96e] mb-5"></div>
                    <p class="text-[#6b5f52] text-base leading-8">
                        บทความความรู้อสังหาริมทรัพย์ทุกประเภท <?= count($articles) ?> บทความ จากทีมผู้เชี่ยวชาญ INVEZ
                    </p>
                </div>
            </div>
        </div>
        <div class="h-[3px] bg-gradient-to-r from-transparent via-[#c9a96e] to-transparent opacity-40"></div>
    </section>

    <!-- Articles Grid -->
    <section class="py-16 md:py-20 px-6 bg-white">
        <div class="max-w-6xl mx-auto">

            <div class="mb-10">
                <p class="text-[#9d8f82] text-sm">
                    ทั้งหมด <span class="text-[#c9a96e] font-semibold"><?= count($articles) ?> บทความ</span>
                    ครอบคลุมทุกประเภทอสังหาริมทรัพย์
                </p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php foreach ($articles as $id => $article): ?>
                <a href="/article/<?= $id ?>"
                    class="fade-up bg-white rounded-xl border border-[#e5d9c8] hover:border-[#c9a96e] hover:shadow-md transition-all duration-300 flex flex-col overflow-hidden group">

                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-start justify-between mb-5">
                            <div class="w-10 h-10 rounded-lg bg-[#fdf6e8] flex items-center justify-center flex-shrink-0 group-hover:bg-[#c9a96e]/15 transition-colors">
                                <i data-feather="<?= htmlspecialchars($article['icon']) ?>" style="width:17px;height:17px;color:#c9a96e;"></i>
                            </div>
                            <span class="text-[10px] font-medium tracking-wider uppercase text-[#c9a96e] bg-[#fdf6e8] px-2.5 py-1 rounded-full border border-[#e5d9c8]">
                                <?= htmlspecialchars($article['category']) ?>
                            </span>
                        </div>

                        <h2 class="font-semibold text-[#1a1714] text-sm leading-6 mb-3 flex-1">
                            <?= htmlspecialchars($article['title']) ?>
                        </h2>

                        <p class="text-[#9d8f82] text-sm leading-6 line-clamp-2">
                            <?= htmlspecialchars($article['excerpt']) ?>
                        </p>
                    </div>

                    <div class="px-6 pb-5 border-t border-[#f0e8d8] pt-4">
                        <span class="text-[#c9a96e] text-xs font-medium group-hover:text-[#b8965e] transition-colors duration-200 flex items-center gap-1.5">
                            อ่านบทความ
                            <i data-feather="arrow-right" style="width:13px;height:13px;"></i>
                        </span>
                    </div>

                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-20 px-6 bg-[#c9a96e]">
        <div class="max-w-2xl mx-auto text-center fade-up">
            <h2 class="text-2xl md:text-3xl font-semibold text-white mb-4">สนใจทรัพย์สินประเภทไหน?</h2>
            <p class="text-white/80 text-sm md:text-base leading-7 mb-8">
                ทีมงาน INVEZ พร้อมให้คำปรึกษาและหาทรัพย์สินที่ตรงโจทย์ให้คุณ
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
            { threshold: 0.08 }
        );
        document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
        window.addEventListener('load', () => {
            setTimeout(() => document.getElementById('preloader').classList.add('hide'), 600);
        });
    </script>
</body>
</html>
