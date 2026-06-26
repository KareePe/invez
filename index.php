<?php
$current_page = 'home';
require_once('config/lang.php');
?>
<!DOCTYPE html>
<html lang="<?= lang() ?>">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <?php include('components/seo.php'); ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preload" href="assets/images/S__24510480.jpg" as="image">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-[#0e0c0a]">

    <!-- Preloader -->
    <div id="preloader" class="fixed inset-0 bg-[#0e0c0a] z-50 flex items-center justify-center">
        <img src="assets/images/logo-w.png" class="w-[140px] logo" alt="INVEZ">
    </div>

    <!-- ================= NAVBAR ================= -->
    <?php $navbar_dark = true; include('components/navbar.php'); ?>

    <!-- ================= HERO ================= -->
    <section
        class="h-screen parallax bg-cover bg-center relative"
        style="background-image: url('assets/images/S__24510480.jpg');">
        <div class="absolute inset-0 bg-black/65"></div>

        <div class="relative z-10 h-full flex flex-col justify-center items-center text-center text-white px-6">

            <div class="fade-up flex w-full flex-col justify-center items-center">
                <img src="assets/images/logo-w.png" class="w-[160px] mb-8 opacity-95" alt="INVEZ">

                <p class="text-sm md:text-base text-white/70 mb-4 font-light tracking-wide">
                    <?= t('ศูนย์รวมอสังหาริมทรัพย์ ครบจบในที่เดียว','Real Estate Center — All in One Place') ?>
                </p>

                <h1 class="max-w-2xl text-base md:text-lg text-white/60 font-light leading-7 mb-10">
                    <?= t(
                        'แพลตฟอร์ม INVEZ เป็นศูนย์รวมอสังหาริมทรัพย์ทุกประเภท ครบจบในที่เดียว สำหรับการอยู่อาศัย ธุรกิจ และการลงทุน INVEZ เชื่อมต่อผู้ซื้อ ผู้ขาย และนักลงทุน เข้ากับโอกาสที่ดีที่สุดในทุกทำเล',
                        'INVEZ is a comprehensive real estate platform covering all property types — residential, commercial, and investment. We connect buyers, sellers, and investors with the best opportunities in every location.'
                    ) ?>
                </h1>

                <div class="w-px h-10 bg-white/20 mb-8"></div>

                <div class="text-sm text-white/60 space-y-2">

                    <p class="flex items-center justify-center gap-2">
                        <i data-feather="user" style="width:13px;height:13px;opacity:0.5;"></i>
                        <?= t('คุณเอ็ดเวิร์ด','Mr. Edward') ?> "Mr.Edward"
                    </p>

                    <p class="flex items-center justify-center gap-2">
                        <i data-feather="smartphone" style="width:13px;height:13px;opacity:0.5;"></i>
                        081-6611286, 081-8716303
                    </p>

                    <p class="flex items-center justify-center gap-2">
                        <i data-feather="mail" style="width:13px;height:13px;opacity:0.5;"></i>
                        toyoelectric@gmail.com
                    </p>

                    <p class="flex items-center justify-center gap-2">
                        <i data-feather="monitor" style="width:13px;height:13px;opacity:0.5;"></i>
                        www.invez.biz | App: Invez
                    </p>

                </div>
            </div>

        </div>
    </section>

    <!-- ================= FOOTER ================= -->
    <?php include('components/footer.php'); ?>

    <!-- ================= SCRIPTS ================= -->
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        feather.replace();

        const elements = document.querySelectorAll('.fade-up');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) entry.target.classList.add('show');
            });
        });
        elements.forEach(el => observer.observe(el));

        window.addEventListener("load", () => {
            setTimeout(() => {
                document.getElementById("preloader").classList.add("hide");
            }, 600);
        });
    </script>

</body>

</html>
