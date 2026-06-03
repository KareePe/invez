<footer class="bg-[#1a1714] text-[#6b605a] pt-12 pb-8 px-6">
    <div class="max-w-6xl mx-auto grid md:grid-cols-3 gap-10 mb-10">

        <div>
            <img src="assets/images/logo-w.png" alt="INVEZ" class="h-6 mb-4 opacity-80">
            <p class="text-sm leading-6">
                <?= t('ศูนย์รวมอสังหาริมทรัพย์ทุกประเภท','All Types of Real Estate') ?><br>
                <?= t('ครบจบในที่เดียว','All in One Place') ?><br>
                <?= t('สำหรับอยู่อาศัย ธุรกิจ และการลงทุน','Residential, Business & Investment') ?>
            </p>
            <div class="flex gap-2.5 mt-5">
                <a href="tel:0816611286" class="w-8 h-8 rounded border border-white/10 flex items-center justify-center hover:border-white/30 transition-colors duration-150">
                    <i data-feather="phone" style="width:13px;height:13px;"></i>
                </a>
                <a href="mailto:toyoelectric@gmail.com" class="w-8 h-8 rounded border border-white/10 flex items-center justify-center hover:border-white/30 transition-colors duration-150">
                    <i data-feather="mail" style="width:13px;height:13px;"></i>
                </a>
                <a href="https://www.invez.biz" target="_blank" rel="noopener" class="w-8 h-8 rounded border border-white/10 flex items-center justify-center hover:border-white/30 transition-colors duration-150">
                    <i data-feather="globe" style="width:13px;height:13px;"></i>
                </a>
            </div>
        </div>

        <div>
            <h3 class="text-white/60 text-xs uppercase tracking-wider mb-4"><?= t('สำนักงานใหญ่','Head Office') ?></h3>
            <p class="text-sm leading-6">
                <?= t('บริษัท โตโยซัพพลาย จำกัด','Toyo Supply Co., Ltd.') ?><br>
                369/1 <?= t('ถ.รอบเมือง (เก่าน้อย)','Robmueang Rd. (Kao Noi)') ?><br>
                <?= t('ต.หมากแข้ง อ.เมือง จ.อุดรธานี 41000','Mak Khaeng, Mueang, Udon Thani 41000') ?>
            </p>
            <p class="text-sm mt-2">
                <a href="tel:042326647" class="hover:text-white/70 transition-colors">Tel. (042) 326647-48</a>
            </p>
        </div>

        <div>
            <h3 class="text-white/60 text-xs uppercase tracking-wider mb-4"><?= t('สาขาปทุมธานี','Pathum Thani Branch') ?></h3>
            <p class="text-sm leading-6">
                <?= t('บริษัท โตโยซัพพลาย จำกัด','Toyo Supply Co., Ltd.') ?><br>
                12/229 <?= t('ม.7 ต.ลาดสวาย','Moo 7, Lad Sawai') ?><br>
                <?= t('อ.ลำลูกกา จ.ปทุมธานี 12150','Lam Luk Ka, Pathum Thani 12150') ?>
            </p>
            <p class="text-sm mt-2">
                <a href="tel:0816611286" class="hover:text-white/70 transition-colors">081-6611286</a>,
                <a href="tel:0818716303" class="hover:text-white/70 transition-colors">081-8716303</a>
            </p>
        </div>

    </div>

    <div class="max-w-6xl mx-auto pt-6 border-t border-white/8 flex flex-col md:flex-row justify-between items-center gap-3 text-xs text-[#3d3530]">
        <p>© 2026 INVEZ · <?= t('บริษัท โตโยซัพพลาย จำกัด','Toyo Supply Co., Ltd.') ?> · <?= t('สงวนลิขสิทธิ์','All rights reserved') ?></p>
        <div class="flex gap-5">
            <a href="/about" class="hover:text-white/50 transition-colors"><?= t('เกี่ยวกับเรา','About') ?></a>
            <a href="/properties" class="hover:text-white/50 transition-colors"><?= t('ทรัพย์สิน','Properties') ?></a>
            <a href="/portfolio" class="hover:text-white/50 transition-colors"><?= t('ผลงาน','Portfolio') ?></a>
            <a href="/content" class="hover:text-white/50 transition-colors"><?= t('คอนเทนท์','Content') ?></a>
            <a href="/contact" class="hover:text-white/50 transition-colors"><?= t('ติดต่อเรา','Contact') ?></a>
        </div>
    </div>
</footer>
