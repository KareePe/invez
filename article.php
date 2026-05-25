<?php
require_once('config/db.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = db()->prepare('SELECT * FROM articles WHERE id = ? AND is_active = 1');
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: /content');
    exit;
}

$article['points'] = json_decode($article['points'], true) ?? [];

$prev_stmt = db()->prepare('SELECT id, category FROM articles WHERE id < ? AND is_active = 1 ORDER BY id DESC LIMIT 1');
$prev_stmt->execute([$id]);
$prev = $prev_stmt->fetch() ?: null;

$next_stmt = db()->prepare('SELECT id, category FROM articles WHERE id > ? AND is_active = 1 ORDER BY id ASC LIMIT 1');
$next_stmt->execute([$id]);
$next = $next_stmt->fetch() ?: null;

$rel_stmt = db()->prepare('SELECT id, icon, category, title FROM articles WHERE id != ? AND is_active = 1 ORDER BY RAND() LIMIT 3');
$rel_stmt->execute([$id]);
$related = $rel_stmt->fetchAll();

$current_page = 'content';

$meta_title = htmlspecialchars($article['title']) . ' | INVEZ';
$meta_desc  = htmlspecialchars($article['excerpt']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title><?= $meta_title ?></title>
    <meta name="description" content="<?= $meta_desc ?>" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://www.invez.biz/article/<?= $id ?>" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="<?= $meta_title ?>" />
    <meta property="og:description" content="<?= $meta_desc ?>" />
    <meta property="og:url" content="https://www.invez.biz/article/<?= $id ?>" />
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
                    <a href="/content" class="hover:text-[#c9a96e] transition-colors">คอนเทนท์</a>
                    <i data-feather="chevron-right" style="width:13px;height:13px;"></i>
                    <span class="text-[#c9a96e]"><?= htmlspecialchars($article['category']) ?></span>
                </nav>

                <div class="fade-up">
                    <span class="inline-block text-[10px] font-medium tracking-wider uppercase text-[#c9a96e] bg-[#fdf6e8] px-3 py-1.5 rounded-full border border-[#e5d9c8] mb-5">
                        <?= htmlspecialchars($article['category']) ?>
                    </span>
                    <h1 class="text-3xl md:text-4xl font-semibold text-[#1a1714] leading-tight mb-5">
                        <?= htmlspecialchars($article['title']) ?>
                    </h1>
                    <div class="w-14 h-[2px] bg-[#c9a96e]"></div>
                </div>

            </div>
        </div>
        <div class="h-[3px] bg-gradient-to-r from-transparent via-[#c9a96e] to-transparent opacity-40"></div>
    </section>

    <!-- Article Body -->
    <section class="py-16 md:py-20 px-6 bg-white">
        <div class="max-w-4xl mx-auto">

            <!-- Intro -->
            <div class="fade-up mb-12">
                <div class="flex gap-4 items-start">
                    <div class="w-12 h-12 rounded-xl bg-[#fdf6e8] border border-[#e5d9c8] flex items-center justify-center flex-shrink-0">
                        <i data-feather="<?= htmlspecialchars($article['icon']) ?>" style="width:20px;height:20px;color:#c9a96e;"></i>
                    </div>
                    <p class="text-[#5a4e42] text-base md:text-lg leading-9 pt-1">
                        <?= htmlspecialchars($article['intro']) ?>
                    </p>
                </div>
            </div>

            <!-- Divider -->
            <div class="h-px bg-[#f0e8d8] mb-12"></div>

            <!-- Key Points -->
            <div class="space-y-6">
                <?php foreach ($article['points'] as $i => $point): ?>
                <div class="fade-up bg-[#fafaf8] border border-[#e5d9c8] rounded-xl p-6 md:p-7 flex gap-5 items-start hover:border-[#c9a96e] transition-colors duration-300 group">

                    <div class="w-9 h-9 rounded-full bg-[#c9a96e] text-white flex items-center justify-center font-semibold text-sm flex-shrink-0 mt-0.5 group-hover:bg-[#b8965e] transition-colors">
                        <?= $i + 1 ?>
                    </div>

                    <div class="flex-1 min-w-0">
                        <h2 class="font-semibold text-[#1a1714] mb-2 text-base">
                            <?= htmlspecialchars($point['label']) ?>
                        </h2>
                        <p class="text-[#6b5f52] leading-7 text-sm md:text-base">
                            <?= htmlspecialchars($point['detail']) ?>
                        </p>
                    </div>

                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </section>

    <!-- Prev / Next Navigation -->
    <section class="py-10 px-6 bg-[#fafaf8] border-t border-[#e5d9c8]">
        <div class="max-w-4xl mx-auto flex items-center justify-between gap-4">

            <?php if ($prev): ?>
            <a href="/article/<?= $prev['id'] ?>"
                class="flex items-center gap-2 text-[#6b5f52] hover:text-[#c9a96e] transition-colors duration-200 text-sm group max-w-[45%]">
                <i data-feather="arrow-left" style="width:16px;height:16px;" class="flex-shrink-0"></i>
                <span class="line-clamp-1"><?= htmlspecialchars($prev['category']) ?></span>
            </a>
            <?php else: ?>
            <div></div>
            <?php endif; ?>

            <a href="/content"
                class="flex-shrink-0 text-[#9d8f82] hover:text-[#c9a96e] transition-colors duration-200 text-xs border border-[#e5d9c8] hover:border-[#c9a96e] px-4 py-2 rounded-full">
                ดูทั้งหมด
            </a>

            <?php if ($next): ?>
            <a href="/article/<?= $next['id'] ?>"
                class="flex items-center gap-2 text-[#6b5f52] hover:text-[#c9a96e] transition-colors duration-200 text-sm group max-w-[45%] justify-end text-right">
                <span class="line-clamp-1"><?= htmlspecialchars($next['category']) ?></span>
                <i data-feather="arrow-right" style="width:16px;height:16px;" class="flex-shrink-0"></i>
            </a>
            <?php else: ?>
            <div></div>
            <?php endif; ?>

        </div>
    </section>

    <!-- Related articles -->
    <section class="py-16 md:py-20 px-6 bg-white border-t border-[#e5d9c8]">
        <div class="max-w-4xl mx-auto">
            <p class="text-[#c9a96e] text-xs font-medium tracking-[0.2em] uppercase mb-3">อ่านต่อ</p>
            <h2 class="text-xl font-semibold text-[#1a1714] mb-8">บทความที่เกี่ยวข้อง</h2>
            <div class="grid sm:grid-cols-3 gap-4">
                <?php foreach ($related as $r): ?>
                <a href="/article/<?= $r['id'] ?>"
                    class="fade-up bg-[#fafaf8] border border-[#e5d9c8] rounded-xl p-5 hover:border-[#c9a96e] hover:shadow-md transition-all duration-300 group flex flex-col">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-[#fdf6e8] flex items-center justify-center group-hover:bg-[#c9a96e]/15 transition-colors flex-shrink-0">
                            <i data-feather="<?= htmlspecialchars($r['icon']) ?>" style="width:15px;height:15px;color:#c9a96e;"></i>
                        </div>
                        <span class="text-[10px] text-[#c9a96e] font-medium tracking-wider uppercase truncate">
                            <?= htmlspecialchars($r['category']) ?>
                        </span>
                    </div>
                    <h3 class="text-sm font-medium text-[#1a1714] leading-5 line-clamp-3 flex-1">
                        <?= htmlspecialchars($r['title']) ?>
                    </h3>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-16 px-6 bg-[#c9a96e]">
        <div class="max-w-2xl mx-auto text-center fade-up">
            <h2 class="text-xl md:text-2xl font-semibold text-white mb-3">สนใจทรัพย์สินประเภทนี้?</h2>
            <p class="text-white/80 text-sm leading-7 mb-6">ทีมงาน INVEZ พร้อมให้คำปรึกษาและจับคู่ดีลที่ตรงโจทย์ให้คุณ</p>
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
