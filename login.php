<?php
$current_page = 'login';
require_once('config/lang.php');
require_once('config/db.php');
require_once('config/login-throttle.php');

if (!empty($_SESSION['member_id'])) {
    header('Location: /');
    exit;
}

if (empty($_SESSION['_login_csrf'])) {
    $_SESSION['_login_csrf'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['_login_csrf']) || !hash_equals($_SESSION['_login_csrf'], $_POST['_login_csrf'])) {
        http_response_code(403);
        die('Invalid request');
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (login_is_blocked('member')) {
        // Checked before any password work so a blocked IP cannot keep probing.
        $error = t('พยายามเข้าสู่ระบบผิดหลายครั้งเกินไป กรุณาลองใหม่อีกครั้งใน 24 ชั่วโมง',
                   'Too many failed login attempts. Please try again in 24 hours.');
    } elseif ($username === '' || $password === '') {
        $error = t('กรุณากรอกชื่อผู้ใช้งานและรหัสผ่าน', 'Please enter username and password');
    } else {
        $stmt = db()->prepare(
            'SELECT id, first_name, last_name, username, password_hash, status FROM members WHERE username = ? OR email = ? LIMIT 1'
        );
        $stmt->execute([$username, $username]);
        $member = $stmt->fetch();

        if (!$member || !password_verify($password, $member['password_hash'])) {
            login_record_failure('member');
            $error = t('ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง', 'Incorrect username or password');
        } elseif ($member['status'] === 'pending') {
            $error = t('บัญชีของคุณยังรอการยืนยันจากแอดมิน', 'Your account is awaiting admin approval');
        } elseif ($member['status'] === 'rejected') {
            $error = t('บัญชีของคุณถูกปฏิเสธ กรุณาติดต่อแอดมิน', 'Your account has been rejected. Please contact admin');
        } else {
            login_clear_failures('member');
            session_regenerate_id(true);
            $_SESSION['member_id']       = $member['id'];
            $_SESSION['member_name']     = $member['first_name'] . ' ' . $member['last_name'];
            $_SESSION['member_username'] = $member['username'];
            header('Location: /member');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= lang() ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= t('เข้าสู่ระบบ','Login') ?> | INVEZ</title>
    <meta name="robots" content="noindex, nofollow" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-[#fafaf8] text-[#1a1714] min-h-screen flex flex-col">

    <?php include('components/navbar.php'); ?>

    <div class="flex-1 flex items-center justify-center px-6 py-20">
        <div class="w-full max-w-sm">

            <div class="text-center mb-8">
                <h1 class="text-2xl font-semibold text-[#1a1714] mb-1"><?= t('เข้าสู่ระบบ','Member Login') ?></h1>
                <p class="text-sm text-[#9d8f82]">INVEZ</p>
            </div>

            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-5">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl border border-[#e8e4df] p-8">
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="_login_csrf" value="<?= htmlspecialchars($_SESSION['_login_csrf']) ?>">

                    <div>
                        <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('ชื่อผู้ใช้งาน หรือ อีเมล','Username or Email') ?></label>
                        <input type="text" name="username"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               class="w-full border border-[#e0dbd4] rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]"
                               autocomplete="username" autofocus required>
                    </div>

                    <div>
                        <div class="flex items-baseline justify-between mb-1.5">
                            <label class="block text-xs font-medium text-[#6b5f52]"><?= t('รหัสผ่าน','Password') ?></label>
                            <a href="/forgot-password" class="text-xs text-[#c9a96e] hover:text-[#b8965e] font-medium"><?= t('ลืมรหัสผ่าน?','Forgot password?') ?></a>
                        </div>
                        <input type="password" name="password"
                               class="w-full border border-[#e0dbd4] rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]"
                               autocomplete="current-password" required>
                    </div>

                    <button type="submit"
                            class="w-full bg-[#c9a96e] hover:bg-[#b8965e] text-white font-semibold py-2.5 rounded-lg text-sm transition-colors">
                        <?= t('เข้าสู่ระบบ','Login') ?>
                    </button>
                </form>

                <p class="text-center text-xs text-[#9d8f82] mt-5">
                    <?= t('ยังไม่มีบัญชี?','Don\'t have an account?') ?>
                    <a href="/register" class="text-[#c9a96e] hover:text-[#b8965e] font-medium"><?= t('สมัครสมาชิก','Register') ?></a>
                </p>
            </div>

        </div>
    </div>

    <?php include('components/footer.php'); ?>

    <script src="https://unpkg.com/feather-icons"></script>
    <script>feather.replace();</script>
    <?php include('components/password-script.php'); ?>
</body>
</html>
