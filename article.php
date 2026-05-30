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
        <div class="max-w-3xl mx-auto px-6 py-10 md:py-14">

            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-[#9d8f82] mb-6" aria-label="breadcrumb">
                <a href="/content" class="hover:text-[#1a1714] transition-colors">คอนเทนท์</a>
                <span>/</span>
                <span class="text-[#1a1714]"><?= htmlspecialchars($article['category']) ?></span>
            </nav>

            <div class="fade-up">
                <h1 class="text-2xl md:text-3xl font-semibold text-[#1a1714] leading-snug mb-3">
                    <?= htmlspecialchars($article['title']) ?>
                </h1>
                <?php if (!empty($article['excerpt'])): ?>
                <p class="text-[#6b5f52] text-sm leading-6"><?= htmlspecialchars($article['excerpt']) ?></p>
                <?php endif; ?>
            </div>

        </div>
    </section>

    <!-- Article Body -->
    <section class="py-10 md:py-14 px-6 bg-white">
        <div class="max-w-3xl mx-auto">

            <!-- Intro -->
            <?php if (!empty($article['intro'])): ?>
            <div class="mb-10 pb-10 border-b border-[#e8e4df]">
                <p class="text-[#5a4e42] text-base leading-8">
                    <?= htmlspecialchars($article['intro']) ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- Key Points -->
            <?php if (!empty($article['points'])): ?>
            <div class="space-y-4">
                <?php foreach ($article['points'] as $i => $point): ?>
                <div class="flex gap-4 items-start py-5 border-b border-[#f0ebe3] last:border-0">
                    <span class="w-6 h-6 rounded-full bg-[#c9a96e] text-white flex items-center justify-center text-[11px] font-semibold flex-shrink-0 mt-0.5"><?= $i + 1 ?></span>
                    <div class="flex-1 min-w-0">
                        <h2 class="font-medium text-[#1a1714] mb-1.5 text-sm">
                            <?= htmlspecialchars($point['label']) ?>
                        </h2>
                        <p class="text-[#6b5f52] leading-7 text-sm">
                            <?= htmlspecialchars($point['detail']) ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
    </section>

    <!-- Prev / Next Navigation -->
    <section class="py-6 px-6 bg-[#fafaf8] border-t border-[#e8e4df]">
        <div class="max-w-3xl mx-auto flex items-center justify-between gap-4">

            <?php if ($prev): ?>
            <a href="/article/<?= $prev['id'] ?>"
                class="flex items-center gap-1.5 text-[#6b5f52] hover:text-[#1a1714] transition-colors duration-150 text-sm max-w-[45%]">
                <i data-feather="arrow-left" style="width:14px;height:14px;" class="flex-shrink-0"></i>
                <span class="line-clamp-1"><?= htmlspecialchars($prev['category']) ?></span>
            </a>
            <?php else: ?>
            <div></div>
            <?php endif; ?>

            <a href="/content"
                class="flex-shrink-0 text-xs text-[#9d8f82] hover:text-[#1a1714] transition-colors duration-150 border border-[#e8e4df] hover:border-[#1a1714] px-4 py-1.5 rounded">
                ดูทั้งหมด
            </a>

            <?php if ($next): ?>
            <a href="/article/<?= $next['id'] ?>"
                class="flex items-center gap-1.5 text-[#6b5f52] hover:text-[#1a1714] transition-colors duration-150 text-sm max-w-[45%] justify-end text-right">
                <span class="line-clamp-1"><?= htmlspecialchars($next['category']) ?></span>
                <i data-feather="arrow-right" style="width:14px;height:14px;" class="flex-shrink-0"></i>
            </a>
            <?php else: ?>
            <div></div>
            <?php endif; ?>

        </div>
    </section>

    <!-- Related articles -->
    <section class="py-10 md:py-14 px-6 bg-white border-t border-[#e8e4df]">
        <div class="max-w-3xl mx-auto">
            <h2 class="text-sm font-semibold text-[#1a1714] mb-5">บทความที่เกี่ยวข้อง</h2>
            <div class="grid sm:grid-cols-3 gap-3">
                <?php foreach ($related as $r): ?>
                <a href="/article/<?= $r['id'] ?>"
                    class="border border-[#e8e4df] rounded-lg p-4 hover:border-[#c9a96e] transition-colors duration-150 flex flex-col gap-2">
                    <div class="flex items-center gap-2">
                        <i data-feather="<?= htmlspecialchars($r['icon']) ?>" style="width:13px;height:13px;color:#c9a96e;flex-shrink:0;"></i>
                        <span class="text-[10px] text-[#9d8f82] truncate">
                            <?= htmlspecialchars($r['category']) ?>
                        </span>
                    </div>
                    <h3 class="text-xs font-medium text-[#1a1714] leading-5 line-clamp-3">
                        <?= htmlspecialchars($r['title']) ?>
                    </h3>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-14 px-6 bg-[#1a1714]">
        <div class="max-w-2xl mx-auto text-center">
            <h2 class="text-lg font-semibold text-white mb-2">สนใจทรัพย์สินประเภทนี้?</h2>
            <p class="text-[#9d8f82] text-sm leading-6 mb-6">ทีมงาน INVEZ พร้อมให้คำปรึกษาและจับคู่ดีลที่ตรงโจทย์ให้คุณ</p>
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
