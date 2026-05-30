<?php
$current_page = $current_page ?? 'home';
$navbar_dark  = $navbar_dark ?? false;
?>

<nav id="main-nav" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300
    <?= $navbar_dark
        ? 'border-transparent'
        : 'bg-white/96 backdrop-blur-sm border-b border-[#e8e4df]' ?>"
    <?= $navbar_dark ? 'style="background-color:rgba(0,0,0,0.35);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);"' : '' ?>">
    <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">

        <a href="/" class="flex-shrink-0">
            <?php if ($navbar_dark): ?>
            <img id="nav-logo" src="assets/images/logo-w.png" alt="INVEZ" class="h-6 transition-opacity duration-300">
            <?php else: ?>
            <img src="assets/images/logo-b.png" alt="INVEZ" class="h-6">
            <?php endif; ?>
        </a>

        <!-- Desktop nav -->
        <div class="hidden md:flex items-center gap-7 text-sm">
            <?php
            $link_base   = $navbar_dark ? 'text-white/70 hover:text-white' : 'text-[#6b5f52] hover:text-[#1a1714]';
            $link_active = $navbar_dark ? 'text-white font-medium'          : 'text-[#1a1714] font-medium';
            $btn_class   = $navbar_dark
                ? 'border border-white/40 text-white hover:bg-white hover:text-[#1a1714]'
                : 'bg-[#1a1714] text-white hover:bg-[#2d2520]';
            ?>
            <a href="/" class="nav-link <?= $current_page === 'home'       ? $link_active : $link_base ?> transition-colors duration-150">หน้าแรก</a>
            <a href="/about" class="nav-link <?= $current_page === 'about'  ? $link_active : $link_base ?> transition-colors duration-150">เกี่ยวกับเรา</a>
            <a href="/properties" class="nav-link <?= $current_page === 'properties' ? $link_active : $link_base ?> transition-colors duration-150">ทรัพย์สิน</a>
            <a href="/content" class="nav-link <?= $current_page === 'content' ? $link_active : $link_base ?> transition-colors duration-150">คอนเทนท์</a>
            <a href="/contact" class="nav-btn px-4 py-1.5 rounded text-sm transition-colors duration-150 <?= $btn_class ?>">ติดต่อเรา</a>
        </div>

        <!-- Mobile hamburger -->
        <button id="nav-toggle" class="md:hidden p-1 <?= $navbar_dark ? 'text-white' : 'text-[#1a1714]' ?>" aria-label="เมนู">
            <i id="icon-menu" data-feather="menu"></i>
            <i id="icon-x" data-feather="x" class="hidden"></i>
        </button>
    </div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="hidden md:hidden border-t <?= $navbar_dark ? 'border-white/10 bg-black/80 backdrop-blur-md' : 'border-[#e8e4df] bg-white' ?>">
        <div class="flex flex-col px-6 py-4 gap-4 text-sm <?= $navbar_dark ? 'text-white/70' : 'text-[#6b5f52]' ?>">
            <a href="/" class="hover:text-<?= $navbar_dark ? 'white' : '[#1a1714]' ?> transition-colors">หน้าแรก</a>
            <a href="/about" class="hover:text-<?= $navbar_dark ? 'white' : '[#1a1714]' ?> transition-colors">เกี่ยวกับเรา</a>
            <a href="/properties" class="hover:text-<?= $navbar_dark ? 'white' : '[#1a1714]' ?> transition-colors">ทรัพย์สิน</a>
            <a href="/content" class="hover:text-<?= $navbar_dark ? 'white' : '[#1a1714]' ?> transition-colors">คอนเทนท์</a>
            <a href="/contact" class="<?= $navbar_dark ? 'text-white font-medium' : 'text-[#1a1714] font-medium' ?>">ติดต่อเรา</a>
        </div>
    </div>
</nav>

<?php if ($navbar_dark): ?>
<script>
(function () {
    const nav   = document.getElementById('main-nav');
    const logo  = document.getElementById('nav-logo');
    const links = nav.querySelectorAll('.nav-link');
    const btn   = nav.querySelector('.nav-btn');
    const hero  = document.querySelector('section');
    const threshold = hero ? hero.offsetHeight - 56 : window.innerHeight - 56;

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
