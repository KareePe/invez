<?php $current_page = $current_page ?? 'home'; ?>

<nav class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-b border-[#e5d9c8] shadow-sm">
    <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">

        <a href="/" class="flex-shrink-0">
            <img src="assets/images/logo-b.png" alt="INVEZ" class="h-7">
        </a>

        <!-- Desktop nav -->
        <div class="hidden md:flex items-center gap-8 text-sm">
            <a href="/"
                class="<?= $current_page === 'home' ? 'text-[#c9a96e] font-medium' : 'text-[#6b5f52] hover:text-[#c9a96e]' ?> transition-colors duration-200">หน้าแรก</a>
            <a href="/about"
                class="<?= $current_page === 'about' ? 'text-[#c9a96e] font-medium' : 'text-[#6b5f52] hover:text-[#c9a96e]' ?> transition-colors duration-200">เกี่ยวกับเรา</a>
            <a href="/properties"
                class="<?= $current_page === 'properties' ? 'text-[#c9a96e] font-medium' : 'text-[#6b5f52] hover:text-[#c9a96e]' ?> transition-colors duration-200">ทรัพย์สิน</a>
            <a href="/content"
                class="<?= $current_page === 'content' ? 'text-[#c9a96e] font-medium' : 'text-[#6b5f52] hover:text-[#c9a96e]' ?> transition-colors duration-200">คอนเทนท์</a>
            <a href="/contact"
                class="<?= $current_page === 'contact'
                    ? 'bg-[#c9a96e] text-white'
                    : 'bg-[#c9a96e] text-white hover:bg-[#b8965e]' ?> px-5 py-2 rounded text-sm font-medium transition-colors duration-200">ติดต่อเรา</a>
        </div>

        <!-- Mobile hamburger -->
        <button id="nav-toggle" class="md:hidden text-[#1a1714] p-1" aria-label="เมนู">
            <i id="icon-menu" data-feather="menu"></i>
            <i id="icon-x" data-feather="x" class="hidden"></i>
        </button>
    </div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="hidden md:hidden border-t border-[#e5d9c8] bg-white">
        <div class="flex flex-col px-6 py-5 gap-5 text-sm">
            <a href="/" class="text-[#6b5f52] hover:text-[#c9a96e] transition-colors">หน้าแรก</a>
            <a href="/about" class="text-[#6b5f52] hover:text-[#c9a96e] transition-colors">เกี่ยวกับเรา</a>
            <a href="/properties" class="text-[#6b5f52] hover:text-[#c9a96e] transition-colors">ทรัพย์สิน</a>
            <a href="/content" class="text-[#6b5f52] hover:text-[#c9a96e] transition-colors">คอนเทนท์</a>
            <a href="/contact" class="text-[#c9a96e] font-medium">ติดต่อเรา</a>
        </div>
    </div>
</nav>

<script>
    document.getElementById('nav-toggle').addEventListener('click', () => {
        document.getElementById('mobile-menu').classList.toggle('hidden');
        document.getElementById('icon-menu').classList.toggle('hidden');
        document.getElementById('icon-x').classList.toggle('hidden');
    });
</script>
