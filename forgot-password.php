<?php
$current_page = 'forgot-password';
require_once('config/lang.php');
require_once('config/db.php');
require_once('config/mail.php');

// Logged-in members reach this page from Settings to change their password, so
// they are not redirected away — their address is prefilled below instead.
$prefill_email = '';
if (!empty($_SESSION['member_id'])) {
    $me = db()->prepare('SELECT email FROM members WHERE id = ? LIMIT 1');
    $me->execute([$_SESSION['member_id']]);
    $prefill_email = (string)($me->fetchColumn() ?: '');
}

if (empty($_SESSION['_forgot_csrf'])) {
    $_SESSION['_forgot_csrf'] = bin2hex(random_bytes(32));
}

// Rate limits. Per-IP stops broad spamming; per-email stops one address being
// bombed with reset mail. Both are checked before any member lookup happens.
const RESET_MAX_PER_IP      = 5;    // within RESET_IP_WINDOW_MIN
const RESET_IP_WINDOW_MIN   = 15;
const RESET_MAX_PER_EMAIL   = 3;    // within RESET_MAIL_WINDOW_MIN
const RESET_MAIL_WINDOW_MIN = 60;

$error = '';
$sent  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['_forgot_csrf']) || !hash_equals($_SESSION['_forgot_csrf'], $_POST['_forgot_csrf'])) {
        http_response_code(403);
        die('Invalid request');
    }

    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $error = t('กรุณากรอกอีเมล', 'Please enter your email');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = t('รูปแบบอีเมลไม่ถูกต้อง', 'Invalid email format');
    }

    if ($error === '') {
        $ip         = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $email_hash = hash('sha256', mb_strtolower($email));

        // Keep the table from growing forever — cheap with the indexes on it.
        db()->exec('DELETE FROM password_reset_attempts WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)');

        $by_ip = db()->prepare(
            'SELECT COUNT(*) FROM password_reset_attempts
             WHERE ip = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)'
        );
        $by_ip->execute([$ip, RESET_IP_WINDOW_MIN]);

        $by_email = db()->prepare(
            'SELECT COUNT(*) FROM password_reset_attempts
             WHERE email_hash = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)'
        );
        $by_email->execute([$email_hash, RESET_MAIL_WINDOW_MIN]);

        if ($by_ip->fetchColumn() >= RESET_MAX_PER_IP || $by_email->fetchColumn() >= RESET_MAX_PER_EMAIL) {
            // Same message for both limits — which one was hit is not the visitor's business.
            $error = t('คุณขอลิงก์บ่อยเกินไป กรุณารอสักครู่แล้วลองใหม่อีกครั้ง',
                       'Too many reset requests. Please wait a while and try again.');
        } else {
            // Recorded before the lookup, so requests for addresses that are not
            // members still count towards the rate limit.
            db()->prepare('INSERT INTO password_reset_attempts (ip, email_hash) VALUES (?, ?)')
               ->execute([$ip, $email_hash]);

            // Drop reset rows that can no longer be used, keeping one day of spent
            // ones as a short trail. Opportunistic, so no cron job is needed.
            db()->exec(
                'DELETE FROM password_resets
                 WHERE (used_at IS NOT NULL OR expires_at < NOW())
                   AND created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)'
            );

            $stmt = db()->prepare('SELECT id, email FROM members WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $member = $stmt->fetch();

            if (!$member) {
                $error = t('ไม่พบที่อยู่อีเมลนี้ในระบบ', 'This email address was not found in our system');
            } else {
                // Invalidate any earlier link for this member so only the newest one works.
                db()->prepare('UPDATE password_resets SET used_at = NOW() WHERE member_id = ? AND used_at IS NULL')
                   ->execute([$member['id']]);

                $token = bin2hex(random_bytes(32));

                db()->prepare(
                    'INSERT INTO password_resets (member_id, token_hash, expires_at)
                     VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))'
                )->execute([$member['id'], hash('sha256', $token)]);

                // APP_URL comes from .env, never from the Host header — otherwise a
                // spoofed Host would send the victim a link pointing at another site.
                $reset_link = APP_URL . '/reset-password?token=' . $token;

                if (send_mail(
                        $member['email'],
                        'ตั้งรหัสผ่านใหม่ | INVEZ',
                        password_reset_email_body($reset_link)
                    )) {
                    $sent = true;
                } else {
                    // send_mail() already logged the SMTP reason; the visitor only
                    // needs to know it did not go out so they can retry.
                    $error = t('ส่งอีเมลไม่สำเร็จ กรุณาลองใหม่อีกครั้งหรือติดต่อผู้ดูแลระบบ',
                               'Could not send the email. Please try again or contact support.');
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= lang() ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= t('ลืมรหัสผ่าน','Forgot Password') ?> | INVEZ</title>
    <meta name="robots" content="noindex, nofollow" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-[#fafaf8] text-[#1a1714] min-h-screen flex flex-col">

    <?php include('components/navbar.php'); ?>

    <div class="flex-1 flex items-center justify-center px-6 py-20">
        <div class="w-full max-w-md">

            <div class="text-center mb-8">
                <h1 class="text-2xl font-semibold text-[#1a1714] mb-1"><?= t('ลืมรหัสผ่าน','Forgot Password') ?></h1>
                <p class="text-sm text-[#9d8f82]"><?= t('กรอกอีเมลที่ใช้สมัครสมาชิก เราจะส่งลิงก์สำหรับตั้งรหัสผ่านใหม่ไปให้','Enter the email you registered with and we will send you a reset link') ?></p>
            </div>

            <?php if ($sent): ?>
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl p-6 text-center">
                <svg class="w-10 h-10 text-green-500 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                <p class="font-semibold text-base mb-1"><?= t('ตรวจสอบอีเมลของคุณ','Check your email') ?></p>
                <p class="text-sm text-green-700"><?= t('เราได้ส่งลิงก์สำหรับตั้งรหัสผ่านใหม่ไปที่อีเมลของคุณแล้ว ลิงก์ใช้ได้ 1 ชั่วโมง','We have sent a password reset link to your email. The link is valid for 1 hour.') ?></p>
                <a href="/login" class="inline-block mt-4 text-sm text-green-700 underline hover:text-green-900"><?= t('กลับไปหน้าเข้าสู่ระบบ','Back to Login') ?></a>
            </div>

            <?php else: ?>

            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-5">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl border border-[#e8e4df] p-8">
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="_forgot_csrf" value="<?= htmlspecialchars($_SESSION['_forgot_csrf']) ?>">

                    <div>
                        <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('อีเมล','Email') ?> *</label>
                        <input type="email" name="email"
                               value="<?= htmlspecialchars($_POST['email'] ?? $prefill_email) ?>"
                               class="w-full border border-[#e0dbd4] rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]"
                               autocomplete="email" autofocus required>
                    </div>

                    <button type="submit"
                            class="w-full bg-[#c9a96e] hover:bg-[#b8965e] text-white font-semibold py-2.5 rounded-lg text-sm transition-colors">
                        <?= t('ส่งลิงก์ตั้งรหัสผ่านใหม่','Send Reset Link') ?>
                    </button>
                </form>

                <p class="text-center text-xs text-[#9d8f82] mt-5">
                    <?= t('จำรหัสผ่านได้แล้ว?','Remembered your password?') ?>
                    <a href="/login" class="text-[#c9a96e] hover:text-[#b8965e] font-medium"><?= t('เข้าสู่ระบบ','Login') ?></a>
                </p>
            </div>

            <?php endif; ?>

        </div>
    </div>

    <?php include('components/footer.php'); ?>

    <script src="https://unpkg.com/feather-icons"></script>
    <script>feather.replace();</script>
    <?php include('components/email-script.php'); ?>
</body>
</html>
