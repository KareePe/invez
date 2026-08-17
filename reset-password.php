<?php
$current_page = 'reset-password';
require_once('config/lang.php');
require_once('config/db.php');

if (!empty($_SESSION['member_id'])) {
    header('Location: /member');
    exit;
}

if (empty($_SESSION['_reset_csrf'])) {
    $_SESSION['_reset_csrf'] = bin2hex(random_bytes(32));
}

// The token travels in the URL on first visit, then in a hidden field on submit.
$token = trim($_GET['token'] ?? $_POST['token'] ?? '');

$errors  = [];
$success = false;
$reset   = null;

// Look the token up first — everything below depends on it being usable.
if ($token !== '' && preg_match('/^[a-f0-9]{64}$/', $token)) {
    $stmt = db()->prepare(
        'SELECT id, member_id FROM password_resets
         WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW()
         LIMIT 1'
    );
    $stmt->execute([hash('sha256', $token)]);
    $reset = $stmt->fetch();
}

if ($reset && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['_reset_csrf']) || !hash_equals($_SESSION['_reset_csrf'], $_POST['_reset_csrf'])) {
        http_response_code(403);
        die('Invalid request');
    }

    $password = $_POST['password']         ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Same rules as register.php — separate checks so every missing requirement shows at once.
    if ($password === '') {
        $errors[] = t('กรุณากรอกรหัสผ่าน', 'Please enter a password');
    } else {
        if (mb_strlen($password) < 8) {
            $errors[] = t('รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร', 'Password must be at least 8 characters');
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = t('รหัสผ่านต้องมีตัวเลขอย่างน้อย 1 ตัว', 'Password must contain at least one number');
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = t('รหัสผ่านต้องมีตัวพิมพ์เล็กอย่างน้อย 1 ตัว', 'Password must contain at least one lowercase letter');
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = t('รหัสผ่านต้องมีตัวพิมพ์ใหญ่อย่างน้อย 1 ตัว', 'Password must contain at least one uppercase letter');
        }
    }

    if ($confirm === '') {
        $errors[] = t('กรุณายืนยันรหัสผ่าน', 'Please confirm your password');
    } elseif ($confirm !== $password) {
        $errors[] = t('รหัสผ่านไม่ตรงกัน', 'Passwords do not match');
    }

    if (empty($errors)) {
        $pdo = db();
        $pdo->beginTransaction();

        $pdo->prepare('UPDATE members SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($password, PASSWORD_DEFAULT), $reset['member_id']]);

        // Burn this token and any other outstanding one for the member.
        $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE member_id = ? AND used_at IS NULL')
            ->execute([$reset['member_id']]);

        $pdo->commit();

        $success = true;
        $reset   = null;   // stop rendering the form
    }
}
?>
<!DOCTYPE html>
<html lang="<?= lang() ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= t('ตั้งรหัสผ่านใหม่','Reset Password') ?> | INVEZ</title>
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
                <h1 class="text-2xl font-semibold text-[#1a1714] mb-1"><?= t('ตั้งรหัสผ่านใหม่','Reset Password') ?></h1>
                <p class="text-sm text-[#9d8f82]"><?= t('กรอกรหัสผ่านใหม่ที่ต้องการใช้งาน','Choose a new password for your account') ?></p>
            </div>

            <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl p-6 text-center">
                <svg class="w-10 h-10 text-green-500 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-semibold text-base mb-1"><?= t('ตั้งรหัสผ่านใหม่เรียบร้อย','Password updated') ?></p>
                <p class="text-sm text-green-700"><?= t('คุณสามารถเข้าสู่ระบบด้วยรหัสผ่านใหม่ได้ทันที','You can now log in with your new password.') ?></p>
                <a href="/login" class="inline-block mt-4 text-sm text-green-700 underline hover:text-green-900"><?= t('ไปหน้าเข้าสู่ระบบ','Go to Login') ?></a>
            </div>

            <?php elseif (!$reset): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-6 text-center">
                <svg class="w-10 h-10 text-red-400 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                <p class="font-semibold text-base mb-1"><?= t('ลิงก์ใช้งานไม่ได้','This link is not valid') ?></p>
                <p class="text-sm text-red-700"><?= t('ลิงก์นี้หมดอายุ ถูกใช้ไปแล้ว หรือไม่ถูกต้อง กรุณาขอลิงก์ใหม่อีกครั้ง','This link has expired, was already used, or is incorrect. Please request a new one.') ?></p>
                <a href="/forgot-password" class="inline-block mt-4 text-sm text-red-700 underline hover:text-red-900"><?= t('ขอลิงก์ใหม่','Request a new link') ?></a>
            </div>

            <?php else: ?>

            <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-5 space-y-1">
                <?php foreach ($errors as $e): ?>
                <div>• <?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl border border-[#e8e4df] p-8">
                <form method="POST" novalidate class="space-y-4">
                    <input type="hidden" name="_reset_csrf" value="<?= htmlspecialchars($_SESSION['_reset_csrf']) ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <div>
                        <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('รหัสผ่านใหม่','New Password') ?> *</label>
                        <input type="password" name="password" id="password" data-pw-rules="pw-rules"
                               class="w-full border border-[#e0dbd4] rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]"
                               autocomplete="new-password" autofocus required>
                        <ul id="pw-rules" class="text-xs text-[#9d8f82] mt-2 space-y-1">
                            <li data-pw-rule="len"><span data-pw-mark>•</span> <?= t('อย่างน้อย 8 ตัวอักษร','At least 8 characters') ?></li>
                            <li data-pw-rule="digit"><span data-pw-mark>•</span> <?= t('มีตัวเลขอย่างน้อย 1 ตัว (0-9)','At least one number (0-9)') ?></li>
                            <li data-pw-rule="lower"><span data-pw-mark>•</span> <?= t('มีตัวพิมพ์เล็กอย่างน้อย 1 ตัว (a-z)','At least one lowercase letter (a-z)') ?></li>
                            <li data-pw-rule="upper"><span data-pw-mark>•</span> <?= t('มีตัวพิมพ์ใหญ่อย่างน้อย 1 ตัว (A-Z)','At least one uppercase letter (A-Z)') ?></li>
                        </ul>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('ยืนยันรหัสผ่านใหม่','Confirm New Password') ?> *</label>
                        <input type="password" name="confirm_password" id="confirm_password" data-pw-match="password"
                               class="w-full border border-[#e0dbd4] rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]"
                               autocomplete="new-password" required>
                    </div>

                    <button type="submit"
                            class="w-full bg-[#c9a96e] hover:bg-[#b8965e] text-white font-semibold py-2.5 rounded-lg text-sm transition-colors mt-2">
                        <?= t('บันทึกรหัสผ่านใหม่','Save New Password') ?>
                    </button>
                </form>
            </div>

            <?php endif; ?>

        </div>
    </div>

    <?php include('components/footer.php'); ?>

    <script src="https://unpkg.com/feather-icons"></script>
    <script>feather.replace();</script>
    <?php include('components/password-script.php'); ?>
</body>
</html>
