<?php
/**
 * Sort dropdown (server-rendered, GET form).
 * Expects:
 *   $sort            current sort key
 *   $sort_with_price true to show price options (properties page only)
 *   $sort_hidden     assoc array of query params to preserve, e.g. ['cat' => $filter]
 */
$sort_with_price = $sort_with_price ?? false;
$sort_hidden     = $sort_hidden     ?? [];
?>
<form method="get" class="flex items-center gap-2 shrink-0">
    <?php foreach ($sort_hidden as $k => $v): ?>
    <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>">
    <?php endforeach; ?>
    <label for="sort" class="text-xs text-[#9d8f82]"><?= t('เรียงตาม','Sort by') ?></label>
    <select name="sort" id="sort" onchange="this.form.submit()"
            class="px-3 py-1.5 pr-8 rounded text-sm bg-white border border-[#e8e4df] text-[#6b5f52]
                   hover:border-[#1a1714] focus:border-[#1a1714] focus:outline-none cursor-pointer">
        <option value="newest"<?= $sort === 'newest' ? ' selected' : '' ?>><?= t('วันที่ล่าสุด','Newest first') ?></option>
        <option value="oldest"<?= $sort === 'oldest' ? ' selected' : '' ?>><?= t('เก่าที่สุด','Oldest first') ?></option>
        <?php if ($sort_with_price): ?>
        <option value="price_low"<?= $sort === 'price_low' ? ' selected' : '' ?>><?= t('ราคาน้อยที่สุด','Price: low to high') ?></option>
        <option value="price_high"<?= $sort === 'price_high' ? ' selected' : '' ?>><?= t('ราคามากที่สุด','Price: high to low') ?></option>
        <?php endif; ?>
    </select>
    <noscript><button type="submit" class="px-3 py-1.5 rounded text-sm border border-[#e8e4df] text-[#6b5f52]"><?= t('ตกลง','Go') ?></button></noscript>
</form>
