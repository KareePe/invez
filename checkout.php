<?php
require_once('config/lang.php');
require_once('config/db.php');
require_once('properties_data.php');

// Must be logged in
if (empty($_SESSION['member_id'])) {
    header('Location: /login');
    exit;
}

$raw_token = isset($_GET['token']) ? preg_replace('/[^a-z0-9]/', '', strtolower($_GET['token'])) : '';

if ($raw_token === '') {
    header('Location: /properties');
    exit;
}

// Check member approval status (session only stores id/name, not status)
$mstmt = db()->prepare("SELECT status FROM members WHERE id = ? LIMIT 1");
$mstmt->execute([$_SESSION['member_id']]);
$member_row = $mstmt->fetch();
$approved = ($member_row && $member_row['status'] === 'approved');

// Load property by token
$stmt = db()->prepare("SELECT * FROM properties WHERE token = ? AND is_active = 1");
$stmt->execute([$raw_token]);
$p = $stmt->fetch();

if (!$p) {
    header('Location: /properties');
    exit;
}

$id = (int)$p['id'];

// Contract already signed: no longer open for interest, even via a direct token URL
if (!empty($p['is_contracted'])) {
    header('Location: /property/' . $id);
    exit;
}

// Cover image
$img_stmt = db()->prepare("SELECT filename FROM property_images WHERE property_id = ? ORDER BY sort_order ASC LIMIT 1");
$img_stmt->execute([$id]);
$cover_file = $img_stmt->fetchColumn();
$cover = $cover_file ? 'assets/images/properties/' . $id . '/' . $cover_file : null;

$banks = [
    'scb'   => [
        'name'           => 'ธนาคารไทยพาณิชย์ (SCB)',
        'account_name'   => 'บริษัท โตโยซัพพลาย จำกัด',
        'account_number' => '510-3-05708-7',
        'logo'           => 'assets/images/scb_logo.jpeg',
    ],
    'kbank' => [
        'name'           => 'ธนาคารกสิกรไทย (KBank)',
        'account_name'   => 'บริษัท โตโยซัพพลาย จำกัด สาขาตลาดบ้านห้วย อุดรธานี',
        'account_number' => '015-8-16574-0',
        'logo'           => 'assets/images/kbank_logo.png',
    ],
];

// CSRF
if (empty($_SESSION['csrf_checkout'])) {
    $_SESSION['csrf_checkout'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_checkout'];

// Deposit ratio set on the property record; 10% when unset or out of range.
$default_percent = (int)($p['deposit_percent'] ?? 0);
if ($default_percent < 1 || $default_percent > 100) {
    $default_percent = 10;
}

$success        = false;
$error          = '';
$posted_percent = $default_percent;
$posted_bank    = '';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $approved) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_checkout'] ?? '', $_POST['csrf_token'])) {
        http_response_code(403);
        die('Invalid token');
    }

    $posted_percent = max(1, min(100, (int)($_POST['percent'] ?? 0)));
    $posted_bank    = $_POST['bank'] ?? '';

    $dup = db()->prepare("SELECT id FROM property_interests WHERE member_id = ? AND property_id = ? AND status != 'rejected' LIMIT 1");
    $dup->execute([$_SESSION['member_id'], $id]);

    if ($dup->fetch()) {
        $error = t('คุณมีคำสั่งซื้อสำหรับทรัพย์สินนี้อยู่แล้ว', 'You already have an active order for this property');
    } elseif ($posted_percent < 1 || $posted_percent > 100) {
        $error = t('กรุณาเลือกสัดส่วนยอดที่ต้องการชำระ', 'Please select a payment amount');
    } elseif (!array_key_exists($posted_bank, $banks)) {
        $error = t('กรุณาเลือกช่องทางชำระเงิน', 'Please select a payment channel');
    } else {
        $amount_value   = (int)round($p['price'] * $posted_percent / 100);
        $transaction_id = sprintf('%s-%s-%04x-%04x-%s',
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(2)),
            (hexdec(bin2hex(random_bytes(2))) & 0x0fff) | 0x4000,
            (hexdec(bin2hex(random_bytes(2))) & 0x3fff) | 0x8000,
            bin2hex(random_bytes(6))
        );
        $ins = db()->prepare(
            "INSERT INTO property_interests (transaction_id, member_id, property_id, amount_percent, amount_value, bank) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $ins->execute([$transaction_id, $_SESSION['member_id'], $id, $posted_percent, $amount_value, $posted_bank]);
        $_SESSION['csrf_checkout'] = bin2hex(random_bytes(32));
        $success = true;
    }
}

$current_page = 'properties';
$cat_label    = get_category_label($property_categories, $p['category'], lang());
$price_num    = (int)$p['price'];
$has_price    = $price_num > 0;
?>
<!DOCTYPE html>
<html lang="<?= lang() ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= t('ยืนยันความสนใจ', 'Express Interest') ?> | INVEZ</title>
    <meta name="robots" content="noindex, nofollow" />
    <link rel="icon" href="/favicon.ico" type="image/x-icon" />
    <?php
    $_base = rtrim(str_replace('\\', '/', str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', realpath(__DIR__))), '/') . '/';
    ?>
    <base href="<?= htmlspecialchars($_base) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        input[type=range] { -webkit-appearance: none; appearance: none; height: 4px; border-radius: 2px; background: #e8e4df; outline: none; }
        input[type=range]::-webkit-slider-thumb { -webkit-appearance: none; appearance: none; width: 18px; height: 18px; border-radius: 50%; background: #c9a96e; cursor: pointer; }
        input[type=range]::-moz-range-thumb { width: 18px; height: 18px; border: none; border-radius: 50%; background: #c9a96e; cursor: pointer; }
    </style>
</head>
<body class="bg-[#fafaf8] text-[#1a1714]">

    <?php include('components/navbar.php'); ?>

    <div class="pt-14">
        <div class="max-w-4xl mx-auto px-6 py-10 md:py-14">

            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-[#9d8f82] mb-8" aria-label="breadcrumb">
                <a href="/properties" class="hover:text-[#1a1714] transition-colors"><?= t('ทรัพย์สิน', 'Properties') ?></a>
                <span>/</span>
                <a href="/property/<?= $id ?>" class="hover:text-[#1a1714] transition-colors max-w-[160px] truncate block"><?= htmlspecialchars(tf($p, 'title')) ?></a>
                <span>/</span>
                <span class="text-[#1a1714]"><?= t('ยืนยันความสนใจ', 'Express Interest') ?></span>
            </nav>

            <div class="grid md:grid-cols-2 gap-8 items-start">

                <!-- Property summary card -->
                <div class="bg-white rounded-xl border border-[#e8e4df] overflow-hidden">
                    <?php if ($cover): ?>
                    <img src="<?= htmlspecialchars($cover) ?>"
                         alt="<?= htmlspecialchars(tf($p, 'title')) ?>"
                         class="w-full h-48 object-cover">
                    <?php endif; ?>
                    <div class="p-5">
                        <p class="text-xs text-[#c9a96e] mb-1"><?= htmlspecialchars($cat_label) ?></p>
                        <h1 class="text-base font-semibold text-[#1a1714] leading-snug mb-4">
                            <?= htmlspecialchars(tf($p, 'title')) ?>
                        </h1>
                        <?php if (!empty($p['subtitle'])): ?>
                        <p class="text-xs text-[#6b5f52] mb-4 -mt-2"><?= htmlspecialchars(tf($p, 'subtitle')) ?></p>
                        <?php endif; ?>
                        <div class="border-t border-[#e8e4df] pt-4">
                            <p class="text-xs text-[#9d8f82] mb-0.5"><?= t('ราคา', 'Price') ?></p>
                            <p class="text-xl font-semibold text-[#c9a96e]"><?= htmlspecialchars($p['price_display']) ?></p>
                        </div>
                        <?php if (!empty($p['location_short'])): ?>
                        <p class="text-xs text-[#9d8f82] mt-3 flex items-center gap-1">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <?= htmlspecialchars(tf($p, 'location_short')) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right panel -->
                <div>
                    <?php if (!$approved): ?>
                    <!-- Not approved -->
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-6">
                        <p class="font-semibold text-amber-800 mb-1"><?= t('บัญชียังไม่ได้รับการยืนยัน', 'Account not yet verified') ?></p>
                        <p class="text-amber-700 text-sm leading-6">
                            <?= t('กรุณารอแอดมินยืนยันบัญชีสมาชิกของคุณก่อน จึงจะสามารถแสดงความสนใจในทรัพย์สินได้', 'Please wait for admin to verify your member account before expressing interest in properties.') ?>
                        </p>
                        <a href="/property/<?= $id ?>" class="inline-block mt-4 text-xs text-amber-700 underline hover:text-amber-900">
                            <?= t('กลับหน้าทรัพย์สิน', 'Back to property') ?>
                        </a>
                    </div>

                    <?php elseif ($success): ?>
                    <!-- Success -->
                    <div class="bg-white border border-[#e8e4df] rounded-xl p-8 text-center">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="font-semibold text-[#1a1714] mb-1"><?= t('ส่งข้อมูลเรียบร้อยแล้ว!', 'Request submitted!') ?></p>
                        <p class="text-[#6b5f52] text-sm leading-6 mb-6">
                            <?= t('กรุณาโอนเงินและแนบสลิปในหน้าคำสั่งซื้อของคุณ', 'Please transfer and upload your payment slip in your orders page.') ?>
                        </p>
                        <a href="/member/orders"
                           class="inline-block bg-[#c9a96e] hover:bg-[#b8965e] text-white text-sm font-medium px-5 py-2.5 rounded transition-colors mb-3">
                            <?= t('ไปหน้าคำสั่งซื้อ', 'Go to My Orders') ?>
                        </a>
                        <br>
                        <a href="/property/<?= $id ?>" class="text-xs text-[#9d8f82] hover:text-[#6b5f52] underline">
                            <?= t('กลับหน้าทรัพย์สิน', 'Back to property') ?>
                        </a>
                    </div>

                    <?php else: ?>
                    <!-- Checkout form -->
                    <form method="POST" class="space-y-5">

                        <?php if ($error): ?>
                        <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
                            <?= htmlspecialchars($error) ?>
                        </div>
                        <?php endif; ?>

                        <!-- Payment amount -->
                        <div class="bg-white rounded-xl border border-[#e8e4df] p-5">
                            <p class="text-sm font-semibold text-[#1a1714] mb-4"><?= t('ยอดที่ต้องการชำระ', 'Payment Amount') ?></p>

                            <?php if ($has_price): ?>
                            <div class="mb-4">
                                <div class="flex items-center justify-between text-xs text-[#9d8f82] mb-3">
                                    <span><?= t('สัดส่วนมัดจำ', 'Deposit percentage') ?></span>
                                    <span id="percent-display" class="font-semibold text-[#1a1714] text-sm"><?= $posted_percent ?>%</span>
                                </div>
                                <input type="range" id="percent-slider" name="percent"
                                       min="1" max="100" value="<?= $posted_percent ?>"
                                       class="w-full">
                                <div class="flex justify-between text-[10px] text-[#9d8f82] mt-1.5">
                                    <span>1%</span>
                                    <span>50%</span>
                                    <span>100%</span>
                                </div>
                            </div>
                            <div class="bg-[#fafaf8] border border-[#e8e4df] rounded-lg px-4 py-3">
                                <p class="text-xs text-[#9d8f82] mb-0.5"><?= t('ยอดชำระ', 'Amount') ?></p>
                                <p id="amount-display" class="text-lg font-semibold text-[#c9a96e]">—</p>
                            </div>
                            <?php else: ?>
                            <p class="text-sm text-[#6b5f52] mb-3"><?= t('ราคา:', 'Price:') ?> <strong><?= htmlspecialchars($p['price_display']) ?></strong></p>
                            <p class="text-xs text-[#9d8f82]"><?= t('เลือกสัดส่วนที่ต้องการชำระ (มัดจำ 1–100%)', 'Select deposit amount (1–100%)') ?></p>
                            <div class="mt-3">
                                <label class="block text-xs text-[#6b5f52] mb-1"><?= t('สัดส่วนมัดจำ (%)', 'Deposit percentage (%)') ?></label>
                                <input type="number" name="percent" min="1" max="100"
                                       value="<?= $posted_percent ?>"
                                       class="w-32 border border-[#e0dbd4] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]"
                                       required>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Bank selection -->
                        <div class="bg-white rounded-xl border border-[#e8e4df] p-5">
                            <p class="text-sm font-semibold text-[#1a1714] mb-4"><?= t('ช่องทางชำระเงิน', 'Payment Channel') ?></p>
                            <div class="space-y-3">
                                <?php foreach ($banks as $key => $bank): ?>
                                <label class="bank-option flex items-center gap-3 p-4 rounded-lg border cursor-pointer transition-colors
                                              <?= $posted_bank === $key ? 'border-[#c9a96e] bg-[#fdf8f0]' : 'border-[#e8e4df] hover:border-[#c9a96e]' ?>">
                                    <input type="radio" name="bank" value="<?= $key ?>"
                                           <?= $posted_bank === $key ? 'checked' : '' ?>
                                           class="flex-shrink-0" required>
                                    <img src="<?= htmlspecialchars($bank['logo']) ?>"
                                         alt="<?= htmlspecialchars($bank['name']) ?>"
                                         class="w-10 h-10 object-contain flex-shrink-0">
                                    <div>
                                        <p class="text-sm font-medium text-[#1a1714]"><?= htmlspecialchars($bank['name']) ?></p>
                                        <p class="text-xs text-[#6b5f52] mt-0.5"><?= htmlspecialchars($bank['account_name']) ?></p>
                                        <p class="text-xs font-mono text-[#9d8f82] mt-0.5"><?= htmlspecialchars($bank['account_number']) ?></p>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

                        <button type="submit"
                                class="w-full bg-[#c9a96e] hover:bg-[#b8965e] text-white font-semibold py-3 rounded-lg text-sm transition-colors duration-150">
                            <?= t('ยืนยันและส่งข้อมูล', 'Confirm & Submit') ?>
                        </button>

                        <p class="text-xs text-[#9d8f82] text-center leading-5">
                            <?= t('ทีมงานจะติดต่อกลับเพื่อยืนยันรายละเอียดการโอนเงิน', 'Our team will contact you to confirm transfer details.') ?>
                        </p>
                    </form>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <?php include('components/footer.php'); ?>

    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        feather.replace();

        // Bank card highlight on selection
        document.querySelectorAll('input[name="bank"]').forEach(radio => {
            radio.addEventListener('change', () => {
                document.querySelectorAll('.bank-option').forEach(opt => {
                    opt.classList.remove('border-[#c9a96e]', 'bg-[#fdf8f0]');
                    opt.classList.add('border-[#e8e4df]');
                });
                const card = radio.closest('.bank-option');
                card.classList.add('border-[#c9a96e]', 'bg-[#fdf8f0]');
                card.classList.remove('border-[#e8e4df]');
            });
        });

        <?php if (!$success && $approved && $has_price): ?>
        // Price slider
        (function () {
            const price   = <?= $price_num ?>;
            const slider  = document.getElementById('percent-slider');
            const pctDisp = document.getElementById('percent-display');
            const amtDisp = document.getElementById('amount-display');

            function update() {
                const pct    = parseInt(slider.value);
                const amount = Math.round(price * pct / 100);
                pctDisp.textContent = pct + '%';
                amtDisp.textContent = amount.toLocaleString('th-TH') + ' บาท';
            }
            slider.addEventListener('input', update);
            update();
        }());
        <?php endif; ?>
    </script>
</body>
</html>
