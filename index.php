<?php $current_page = 'home'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <?php
    include('components/seo.php');
    ?>

    <!-- Preload (Performance) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="assets/images/S__24510480.jpg">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CDN (dev mode) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-[#fafaf8]">

    <!-- ================= NAVBAR ================= -->
    <?php include('components/navbar.php'); ?>

    <!-- ================= PRELOADER ================= -->
    <div id="preloader" class="fixed inset-0 bg-[#fafaf8] z-50 flex items-center justify-center">
        <div class="text-center">

            <img src="assets/images/logo-b.png" class="w-[200px] logo mb-6" alt="INVEZ">

            <div class="w-40 h-[2px] bg-[#e5d9c8] overflow-hidden mx-auto">
                <div class="loading-bar"></div>
            </div>

        </div>
    </div>

    <!-- ================= HERO ================= -->
    <section
        class="h-screen parallax bg-cover bg-center relative"
        style="background-image: url('assets/images/S__24510480.jpg');">
        <div class="absolute inset-0 bg-black/70"></div>

        <div class="relative z-10 h-full flex flex-col justify-center items-center text-center text-white px-6">

            <!-- TEXT -->
            <div class="fade-up flex w-full flex-col justify-center items-center">
                <img src="assets/images/logo-w.png" class="w-[200px] mb-6" alt="INVEZ">

                <h2 class="max-w-2xl text-lg md:text-xl text-gray-200 mb-6">
                    ศูนย์รวมอสังหาริมทรัพย์ ครบจบในที่เดียว
                </h2>

                <div class="max-w-3xl text-gray-300 mb-10">
                    <h1>แพลตฟอร์ม INVEZ เป็นศูนย์รวมอสังหาริมทรัพย์ทุกประเภท ครบจบในที่เดียว</h1>
                    <p>สำหรับการอยู่อาศัย ธุรกิจ และการลงทุน INVEZ เชื่อมต่อผู้ซื้อ ผู้ขาย และนักลงทุน เข้ากับโอกาสที่ดีที่สุดในทุกทำเล INVEZ เชื่อมทุกความต้องการด้านอสังหาฯ ไว้ในมือคุณ INVEZ เป็นพื้นที่ตรงกลางที่ทำให้การซื้อ-ขาย และการจับคู่ลงทุน เกิดขึ้นได้จริงอย่างรวดเร็วและปลอดภัย</p>
                </div>
            </div>

            <!-- CTA -->
            <div class="fade-up flex-wrap gap-4 justify-center mb-10 hidden">
                <a href="https://www.invez.biz"
                    class="flex items-center gap-2 bg-white text-black px-6 py-3 rounded-lg font-semibold hover:bg-gray-200 transition">
                    <i data-feather="globe"></i>
                    เยี่ยมชมเว็บไซต์
                </a>

                <a href="tel:0816611286"
                    class="flex items-center gap-2 border border-white px-6 py-3 rounded-lg font-semibold hover:bg-white hover:text-black transition">
                    <i data-feather="phone"></i>
                    โทรหาเรา
                </a>
            </div>

            <div class="max-w-[400px] w-full h-[1px] bg-[#fff]/40 mb-6"></div>

            <!-- CONTACT -->
            <div class="fade-up text-sm md:text-base text-gray-300 space-y-2">

                <p class="flex items-center justify-center gap-2">
                    <i data-feather="user"></i>
                    คุณเอ็ดเวิร์ด “Mr.Edward”
                </p>

                <p class="flex items-center justify-center gap-2">
                    <i data-feather="smartphone"></i>
                    081-6611286, 081-8716303
                </p>

                <p class="flex items-center justify-center gap-2">
                    <i data-feather="mail"></i>
                    toyoelectric@gmail.com
                </p>

                <p class="flex items-center justify-center gap-2">
                    <i data-feather="monitor"></i>
                    www.invez.biz | App: Invez
                </p>

            </div>

        </div>
    </section>

    <!-- ================= FOOTER ================= -->
    <?php include('components/footer.php'); ?>

    <!-- ================= SCRIPTS ================= -->

    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- Animation + Preloader -->
    <script>
        feather.replace();

        // fade animation
        const elements = document.querySelectorAll('.fade-up');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('show');
                }
            });
        });
        elements.forEach(el => observer.observe(el));

        // preloader
        window.addEventListener("load", () => {
            const preloader = document.getElementById("preloader");
            setTimeout(() => {
                preloader.classList.add("hide");
            }, 800);
        });
    </script>

</body>

</html>