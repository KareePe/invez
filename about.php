<?php
$current_page = 'about';
require_once('config/lang.php');
?>
<!DOCTYPE html>
<html lang="<?= lang() ?>">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title><?= t('เกี่ยวกับเรา','About Us') ?> | INVEZ <?= t('บริษัท โตโยซัพพลาย จำกัด','Toyo Supply Co., Ltd.') ?></title>
    <meta name="description" content="<?= t('ทำความรู้จักกับบริษัท โตโยซัพพลาย จำกัด ทีมงานที่ปรึกษาและตัวแทนซื้อ-ขายอสังหาริมทรัพย์ ที่เน้นความจริงใจ ดูแลครบวงจร ด้วยเครือข่ายที่หลากหลายทั่วประเทศ','Get to know Toyo Supply Co., Ltd. — real estate consultants and agents who prioritize transparency, full-service support, and a nationwide network.') ?>" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://www.invez.biz/about" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?= t('เกี่ยวกับเรา','About Us') ?> | INVEZ" />
    <meta property="og:description" content="<?= t('ทีมงานที่ปรึกษาและตัวแทนซื้อ-ขายอสังหาริมทรัพย์ เน้นความจริงใจ ดูแลครบวงจร','Real estate consultants and agents — transparent, full-service.') ?>" />
    <meta property="og:url" content="https://www.invez.biz/about" />
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
            <div class="fade-up max-w-2xl">
                <h1 class="text-2xl md:text-3xl font-semibold text-[#1a1714] leading-snug mb-3">
                    <?= t('ทำความรู้จักกับโตโยซัพพลาย','About Toyo Supply') ?>
                </h1>
                <p class="text-[#6b5f52] text-sm leading-6">
                    <?= t('ที่ปรึกษาและตัวแทนซื้อ-ขายอสังหาริมทรัพย์ ที่เน้นความเรียบง่าย จริงใจ และตรงไปตรงมา','Real estate consultants and agents who value simplicity, honesty, and straightforward dealings.') ?>
                </p>
            </div>
        </div>
    </section>

    <!-- Company Intro -->
    <section class="py-14 md:py-20 px-6 bg-white">
        <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-14 items-start">

            <div class="fade-up">
                <p class="text-xs text-[#c9a96e] font-medium mb-4"><?= t('บริษัท โตโยซัพพลาย จำกัด','Toyo Supply Co., Ltd.') ?></p>
                <h2 class="text-xl md:text-2xl font-semibold text-[#1a1714] mb-5 leading-snug">
                    <?= t('เราเริ่มต้นจากความตั้งใจ<br>ที่จะทำให้ดีลใหญ่เป็นเรื่องง่าย','We started with a vision<br>to make big deals simple.') ?>
                </h2>
                <p class="text-[#6b5f52] leading-7 mb-6 text-sm">
                    <?= t(
                        'เราคือทีมงานที่ปรึกษาและตัวแทนซื้อ-ขายอสังหาริมทรัพย์ ที่เน้นความเรียบง่าย จริงใจ และตรงไปตรงมา เราเริ่มต้นจากความตั้งใจที่อยากจะช่วยให้การซื้อขายทรัพย์สินขนาดใหญ่ ไม่ว่าจะเป็น โรงแรม โรงพยาบาล หรือโรงงาน กลายเป็นเรื่องที่จัดการได้ง่ายขึ้น',
                        'We are a team of real estate consultants and agents who believe in simplicity, honesty, and straight talk. We started with the intention of making large-scale property transactions — whether hotels, hospitals, or factories — easier to manage.'
                    ) ?>
                </p>
                <blockquote class="border-l-2 border-[#c9a96e] pl-4">
                    <p class="text-[#5a4e42] leading-7 text-sm italic">
                        "<?= t(
                            'การซื้อขายอสังหาริมทรัพย์ไม่ใช่แค่เรื่องของตัวเลข แต่คือการหา',
                            'Real estate is not just about numbers — it\'s about finding the'
                        ) ?>
                        <strong class="not-italic text-[#1a1714]"><?= t('เจ้าของที่ใช่','right owner') ?></strong>
                        <?= t('ให้กับ','for the') ?>
                        <strong class="not-italic text-[#1a1714]"><?= t('ทรัพย์สินที่มีคุณค่า','property that matters') ?></strong>"
                    </p>
                </blockquote>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-lg border border-[#e8e4df] p-5">
                    <p class="text-2xl font-semibold text-[#c9a96e] mb-1">14+</p>
                    <p class="text-[#6b5f52] text-sm leading-5"><?= t('ประเภทอสังหาฯ<br>ที่เราให้บริการ','Property types<br>we serve') ?></p>
                </div>
                <div class="rounded-lg border border-[#e8e4df] p-5">
                    <p class="text-2xl font-semibold text-[#c9a96e] mb-1">2</p>
                    <p class="text-[#6b5f52] text-sm leading-5"><?= t('สาขา<br>ทั่วประเทศ','Offices<br>nationwide') ?></p>
                </div>
                <div class="rounded-lg border border-[#e8e4df] p-5 col-span-2">
                    <div class="flex items-center gap-2 mb-1.5">
                        <i data-feather="smartphone" style="width:14px;height:14px;color:#c9a96e;"></i>
                        <p class="text-[#1a1714] font-medium text-sm">INVEZ App</p>
                    </div>
                    <p class="text-[#6b5f52] text-sm leading-5">
                        <?= t('แพลตฟอร์มอสังหาฯ ของเรา ครบจบในแอปเดียว','Our all-in-one real estate platform, right in your pocket.') ?>
                    </p>
                </div>
            </div>

        </div>
    </section>

    <!-- Services -->
    <section class="py-14 md:py-20 px-6 bg-[#fafaf8] border-t border-[#e8e4df]">
        <div class="max-w-6xl mx-auto">
            <div class="mb-10">
                <h2 class="text-xl font-semibold text-[#1a1714]"><?= t('สิ่งที่เราทำ','What We Do') ?></h2>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">

                <div class="bg-white rounded-lg border border-[#e8e4df] p-5 hover:border-[#c9a96e] transition-colors duration-150">
                    <i data-feather="home" style="width:16px;height:16px;color:#c9a96e;" class="mb-4"></i>
                    <h3 class="font-medium text-[#1a1714] text-sm mb-1.5"><?= t('ที่พักอาศัยและบริการ','Hospitality & Residential') ?></h3>
                    <p class="text-[#9d8f82] text-xs leading-5"><?= t('โรงแรม · คอนโด · อพาร์ทเมนท์','Hotel · Condo · Apartment') ?></p>
                </div>

                <div class="bg-white rounded-lg border border-[#e8e4df] p-5 hover:border-[#c9a96e] transition-colors duration-150">
                    <i data-feather="activity" style="width:16px;height:16px;color:#c9a96e;" class="mb-4"></i>
                    <h3 class="font-medium text-[#1a1714] text-sm mb-1.5"><?= t('การศึกษาและสาธารณสุข','Education & Healthcare') ?></h3>
                    <p class="text-[#9d8f82] text-xs leading-5"><?= t('โรงเรียน · โรงพยาบาล','School · Hospital') ?></p>
                </div>

                <div class="bg-white rounded-lg border border-[#e8e4df] p-5 hover:border-[#c9a96e] transition-colors duration-150">
                    <i data-feather="briefcase" style="width:16px;height:16px;color:#c9a96e;" class="mb-4"></i>
                    <h3 class="font-medium text-[#1a1714] text-sm mb-1.5"><?= t('พื้นที่ทำงานและอุตสาหกรรม','Commercial & Industrial') ?></h3>
                    <p class="text-[#9d8f82] text-xs leading-5"><?= t('โรงงาน · คลังสินค้า · สำนักงาน','Factory · Warehouse · Office') ?></p>
                </div>

                <div class="bg-white rounded-lg border border-[#e8e4df] p-5 hover:border-[#c9a96e] transition-colors duration-150">
                    <i data-feather="map" style="width:16px;height:16px;color:#c9a96e;" class="mb-4"></i>
                    <h3 class="font-medium text-[#1a1714] text-sm mb-1.5"><?= t('ที่ดินเปล่า','Vacant Land') ?></h3>
                    <p class="text-[#9d8f82] text-xs leading-5"><?= t('เพื่อการลงทุนหรือพัฒนาต่อ','For investment or development') ?></p>
                </div>

            </div>
        </div>
    </section>

    <!-- Why us -->
    <section class="py-14 md:py-20 px-6 bg-white border-t border-[#e8e4df]">
        <div class="max-w-6xl mx-auto">

            <div class="mb-10">
                <h2 class="text-xl font-semibold text-[#1a1714]"><?= t('ทำไมถึงต้องคุยกับเรา?','Why Work With Us?') ?></h2>
            </div>

            <div class="grid md:grid-cols-3 gap-8">

                <div>
                    <i data-feather="shield" style="width:18px;height:18px;color:#c9a96e;" class="mb-4"></i>
                    <h3 class="font-medium text-[#1a1714] mb-2 text-sm"><?= t('เน้นความจริงใจ','Honest & Transparent') ?></h3>
                    <p class="text-[#6b5f52] text-sm leading-6">
                        <?= t('เราให้ข้อมูลตามจริง ปรึกษาได้เหมือนเพื่อนคู่คิด ไม่มีข้อมูลปิดบัง ทุกดีลโปร่งใสตั้งแต่ต้นจนจบ','We give you real information, no hidden agenda. Every deal is transparent from start to finish.') ?>
                    </p>
                </div>

                <div>
                    <i data-feather="check-circle" style="width:18px;height:18px;color:#c9a96e;" class="mb-4"></i>
                    <h3 class="font-medium text-[#1a1714] mb-2 text-sm"><?= t('ดูแลครบวงจร','Full-Service Support') ?></h3>
                    <p class="text-[#6b5f52] text-sm leading-6">
                        <?= t('ช่วยดูแลตั้งแต่การหาทรัพย์สินที่ตรงโจทย์ ไปจนถึงขั้นตอนการเจรจา ปิดดีลอย่างมืออาชีพ','We support you from finding the right property all the way to negotiation and closing — professionally.') ?>
                    </p>
                </div>

                <div>
                    <i data-feather="users" style="width:18px;height:18px;color:#c9a96e;" class="mb-4"></i>
                    <h3 class="font-medium text-[#1a1714] mb-2 text-sm"><?= t('เครือข่ายที่หลากหลาย','Wide Network') ?></h3>
                    <p class="text-[#6b5f52] text-sm leading-6">
                        <?= t('เรามีคอนเนคชันพร้อมเชื่อมโยงผู้ซื้อและผู้ขายเข้าด้วยกันอย่างเหมาะสมในทุกทำเล','We have the connections to match buyers and sellers in every location.') ?>
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-14 px-6 bg-[#1a1714]">
        <div class="max-w-2xl mx-auto text-center">
            <h2 class="text-xl font-semibold text-white mb-3"><?= t('พร้อมให้คำปรึกษาทุกดีล','Ready to Advise on Every Deal') ?></h2>
            <p class="text-[#9d8f82] text-sm leading-6 mb-6">
                <?= t(
                    'ไม่ว่าคุณจะเป็นนักลงทุนที่กำลังมองหาทำเลศักยภาพ หรือเจ้าของธุรกิจที่ต้องการส่งต่อกิจการ เราพร้อมเป็นสะพานเชื่อมให้ทุกดีลจบลงด้วยความพึงพอใจของทุกฝ่าย',
                    'Whether you\'re an investor seeking the right location or a business owner looking to sell, we\'re here to bridge every deal to a satisfying outcome for all parties.'
                ) ?>
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
    </script>
</body>
</html>
