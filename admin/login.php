<?php
require_once(__DIR__ . '/../config/session.php');

require_once('../config/db.php');
require_once('../config/login-throttle.php');

if (!empty($_SESSION['admin_id'])) {
    header('Location: index');
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

    if (login_is_blocked('admin')) {
        // Checked before any password work so a blocked IP cannot keep probing.
        $error = 'พยายามเข้าสู่ระบบผิดหลายครั้งเกินไป กรุณาลองใหม่อีกครั้งใน 24 ชั่วโมง';
    } elseif ($username === '' || $password === '') {
        $error = 'กรุณากรอก username และ password';
    } else {
        $stmt = db()->prepare('SELECT id, name, password FROM admins WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            login_clear_failures('admin');
            session_regenerate_id(true);
            $_SESSION['admin_id']       = $admin['id'];
            $_SESSION['admin_name']     = $admin['name'];
            $_SESSION['admin_username'] = $username;
            header('Location: index');
            exit;
        }
        login_record_failure('admin');
        $error = 'username หรือ password ไม่ถูกต้อง';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ — INVEZ Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="text-[#c9a96e] font-semibold text-2xl tracking-wider mb-1">INVEZ</div>
            <div class="text-gray-400 text-sm">ระบบจัดการหลังบ้าน</div>
        </div>

        <div class="bg-white rounded-xl shadow-xl p-8">
            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-5">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" data-loading="button">
                <input type="hidden" name="_login_csrf" value="<?= htmlspecialchars($_SESSION['_login_csrf']) ?>">
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Username</label>
                    <input type="text" name="username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#c9a96e]/40 focus:border-[#c9a96e]"
                           autocomplete="username" autofocus required>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <input type="password" name="password"
                           class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#c9a96e]/40 focus:border-[#c9a96e]"
                           autocomplete="current-password" required>
                </div>
                <button type="submit"
                        class="w-full bg-[#c9a96e] hover:bg-[#b8965e] text-white font-semibold py-2.5 rounded-lg text-sm transition-colors">
                    เข้าสู่ระบบ
                </button>
            </form>
        </div>
    </div>
<?php include('../components/loading-script.php'); ?>
<?php include('../components/password-script.php'); ?>
</body>
</html>
