<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once('../config/db.php');

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'กรุณากรอก username และ password';
    } else {
        $stmt = db()->prepare('SELECT id, name, password FROM admins WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            header('Location: index.php');
            exit;
        }
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

            <form method="POST">
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
</body>
</html>
