<?php
$current_page = $current_page ?? 'home';
$navbar_dark  = $navbar_dark ?? false;
require_once(__DIR__ . '/../config/lang.php');
?>

<nav id="main-nav" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300
    <?= $navbar_dark
        ? 'border-transparent'
        : 'bg-white/96 backdrop-blur-sm border-b border-[#e8e4df]' ?>"
    <?= $navbar_dark ? 'style="background-color:rgba(0,0,0,0.35);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);"' : '' ?>>
    <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">

        <a href="/" class="flex-shrink-0">
            <?php if ($navbar_dark): ?>
            <img id="nav-logo" src="assets/images/logo-w.png" alt="INVEZ" class="h-6 transition-opacity duration-300">
            <?php else: ?>
            <img src="assets/images/logo-b.png" alt="INVEZ" class="h-6">
            <?php endif; ?>
        </a>

        <div class="flex items-center gap-3">
            <!-- Desktop nav -->
            <div class="hidden md:flex items-center gap-7 text-sm">
                <?php
                $link_base   = $navbar_dark ? 'text-white/70 hover:text-white' : 'text-[#6b5f52] hover:text-[#1a1714]';
                $link_active = $navbar_dark ? 'text-white font-medium'          : 'text-[#1a1714] font-medium';
                $btn_class   = $navbar_dark
                    ? 'border border-white/40 text-white hover:bg-white hover:text-[#1a1714]'
                    : 'bg-[#1a1714] text-white hover:bg-[#2d2520]';
                ?>
                <a href="/" class="nav-link <?= $current_page === 'home'       ? $link_active : $link_base ?> transition-colors duration-150"><?= t('หน้าแรก','Home') ?></a>
                <a href="/about" class="nav-link <?= $current_page === 'about'  ? $link_active : $link_base ?> transition-colors duration-150"><?= t('เกี่ยวกับเรา','About') ?></a>
                <a href="/properties" class="nav-link <?= $current_page === 'properties' ? $link_active : $link_base ?> transition-colors duration-150"><?= t('ทรัพย์สิน','Properties') ?></a>
                <a href="/content" class="nav-link <?= $current_page === 'content' ? $link_active : $link_base ?> transition-colors duration-150"><?= t('คอนเทนท์','Content') ?></a>
                <!-- Language toggle desktop -->
                <div class="lang-toggle flex items-center text-[10px] font-semibold tracking-wider gap-0.5">
                    <a href="/lang?set=th" data-lang="th"
                       class="px-1.5 py-0.5 rounded transition-colors duration-150
                              <?= !is_en()
                                ? ($navbar_dark ? 'text-white' : 'text-[#1a1714]')
                                : ($navbar_dark ? 'text-white/35 hover:text-white/60' : 'text-[#9d8f82] hover:text-[#6b5f52]') ?>">TH</a>
                    <span class="<?= $navbar_dark ? 'text-white/20' : 'text-[#d4cdc5]' ?>">|</span>
                    <a href="/lang?set=en" data-lang="en"
                       class="px-1.5 py-0.5 rounded transition-colors duration-150
                              <?= is_en()
                                ? ($navbar_dark ? 'text-white' : 'text-[#1a1714]')
                                : ($navbar_dark ? 'text-white/35 hover:text-white/60' : 'text-[#9d8f82] hover:text-[#6b5f52]') ?>">EN</a>
                </div>
                <a href="/contact" class="nav-btn px-4 py-1.5 rounded text-sm transition-colors duration-150 <?= $btn_class ?>"><?= t('ติดต่อเรา','Contact') ?></a>
            </div>

            <!-- Mobile hamburger -->
            <button id="nav-toggle" class="md:hidden p-1 <?= $navbar_dark ? 'text-white' : 'text-[#1a1714]' ?>" aria-label="เมนู">
                <i id="icon-menu" data-feather="menu"></i>
                <i id="icon-x" data-feather="x" class="hidden"></i>
            </button>
        </div>
    </div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="hidden md:hidden border-t <?= $navbar_dark ? 'border-white/10 bg-black/80 backdrop-blur-md' : 'border-[#e8e4df] bg-white' ?>">
        <div class="flex flex-col px-6 py-4 gap-4 text-sm <?= $navbar_dark ? 'text-white/70' : 'text-[#6b5f52]' ?>">
            <a href="/" class="hover:text-<?= $navbar_dark ? 'white' : '[#1a1714]' ?> transition-colors"><?= t('หน้าแรก','Home') ?></a>
            <a href="/about" class="hover:text-<?= $navbar_dark ? 'white' : '[#1a1714]' ?> transition-colors"><?= t('เกี่ยวกับเรา','About') ?></a>
            <a href="/properties" class="hover:text-<?= $navbar_dark ? 'white' : '[#1a1714]' ?> transition-colors"><?= t('ทรัพย์สิน','Properties') ?></a>
            <a href="/content" class="hover:text-<?= $navbar_dark ? 'white' : '[#1a1714]' ?> transition-colors"><?= t('คอนเทนท์','Content') ?></a>
            <a href="/contact" class="<?= $navbar_dark ? 'text-white font-medium' : 'text-[#1a1714] font-medium' ?>"><?= t('ติดต่อเรา','Contact') ?></a>
            <!-- Language toggle mobile -->
            <div class="flex items-center gap-1 text-[10px] font-semibold tracking-wider pt-2 border-t <?= $navbar_dark ? 'border-white/10' : 'border-[#e8e4df]' ?>">
                <a href="/lang?set=th"
                   class="px-2 py-1 rounded <?= !is_en()
                     ? ($navbar_dark ? 'text-white bg-white/10' : 'text-[#1a1714] bg-[#f5f3f0]')
                     : ($navbar_dark ? 'text-white/40' : 'text-[#9d8f82]') ?>">TH</a>
                <span class="<?= $navbar_dark ? 'text-white/20' : 'text-[#d4cdc5]' ?>">|</span>
                <a href="/lang?set=en"
                   class="px-2 py-1 rounded <?= is_en()
                     ? ($navbar_dark ? 'text-white bg-white/10' : 'text-[#1a1714] bg-[#f5f3f0]')
                     : ($navbar_dark ? 'text-white/40' : 'text-[#9d8f82]') ?>">EN</a>
            </div>
        </div>
    </div>
</nav>

<?php if ($navbar_dark): ?>
<script>
(function () {
    const nav             = document.getElementById('main-nav');
    const logo            = document.getElementById('nav-logo');
    const links           = nav.querySelectorAll('.nav-link');
    const btn             = nav.querySelector('.nav-btn');
    const langLinks       = nav.querySelectorAll('.lang-toggle a');
    const langSep         = nav.querySelector('.lang-toggle span');
    const currentLang     = '<?= lang() ?>';
    const hero            = document.querySelector('section');
    const threshold       = hero ? hero.offsetHeight - 56 : window.innerHeight - 56;

    function update() {
        const solid = window.scrollY > threshold;

        nav.style.backgroundColor     = solid ? 'rgba(255,255,255,0.96)' : 'rgba(0,0,0,0.35)';
        nav.style.borderBottomColor    = solid ? '#e8e4df' : 'transparent';
        nav.style.backdropFilter       = solid ? 'blur(8px)' : 'blur(4px)';
        nav.style.webkitBackdropFilter = solid ? 'blur(8px)' : 'blur(4px)';

        if (logo) {
            logo.src = solid
                ? logo.src.replace('logo-w.png', 'logo-b.png')
                : logo.src.replace('logo-b.png', 'logo-w.png');
        }

        links.forEach(l => { l.style.color = solid ? '#6b5f52' : 'rgba(255,255,255,0.75)'; });

        if (btn) {
            btn.style.backgroundColor = solid ? '#1a1714'     : 'transparent';
            btn.style.color           = '#ffffff';
            btn.style.borderColor     = solid ? 'transparent' : 'rgba(255,255,255,0.4)';
        }

        langLinks.forEach(a => {
            const active = a.dataset.lang === currentLang;
            a.style.color = solid
                ? (active ? '#1a1714' : '#9d8f82')
                : (active ? 'rgba(255,255,255,1)' : 'rgba(255,255,255,0.35)');
        });
        if (langSep) langSep.style.color = solid ? 'rgba(26,23,20,0.2)' : 'rgba(255,255,255,0.2)';
    }

    window.addEventListener('scroll', update, { passive: true });
}());
</script>
<?php endif; ?>

<script>
    document.getElementById('nav-toggle').addEventListener('click', () => {
        document.getElementById('mobile-menu').classList.toggle('hidden');
        document.getElementById('icon-menu').classList.toggle('hidden');
        document.getElementById('icon-x').classList.toggle('hidden');
    });
</script>
