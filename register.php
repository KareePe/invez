<?php
$current_page = 'register';
require_once('config/lang.php');
require_once('config/db.php');

if (!empty($_SESSION['member_id'])) {
    header('Location: /');
    exit;
}

$errors  = [];
$success = false;
$old     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name']  ?? '');
    $phone      = trim($_POST['phone']      ?? '');
    $email      = trim($_POST['email']      ?? '');
    $username   = trim($_POST['username']   ?? '');
    $password   = $_POST['password']         ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';
    $old        = $_POST;

    if ($first_name === '') $errors[] = t('กรุณากรอกชื่อ', 'Please enter first name');
    if ($last_name  === '') $errors[] = t('กรุณากรอกนามสกุล', 'Please enter last name');
    if ($email === '') {
        $errors[] = t('กรุณากรอกอีเมล', 'Please enter email');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = t('รูปแบบอีเมลไม่ถูกต้อง', 'Invalid email format');
    }
    if ($username === '') {
        $errors[] = t('กรุณากรอกชื่อผู้ใช้งาน', 'Please enter a username');
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
        $errors[] = t('ชื่อผู้ใช้งานต้องเป็นตัวอักษร ตัวเลข หรือ _ (3–50 ตัว)', 'Username: 3–50 letters, numbers or underscore only');
    }
    if ($password === '') {
        $errors[] = t('กรุณากรอกรหัสผ่าน', 'Please enter a password');
    } else {
        if (strlen($password) < 8) {
            $errors[] = t('รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร', 'Password must be at least 8 characters');
        } elseif (!preg_match('/[a-zA-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = t('รหัสผ่านต้องมีทั้งตัวอักษรและตัวเลข', 'Password must contain both letters and numbers');
        }
    }
    if ($confirm === '') {
        $errors[] = t('กรุณายืนยันรหัสผ่าน', 'Please confirm your password');
    } elseif ($confirm !== $password) {
        $errors[] = t('รหัสผ่านไม่ตรงกัน', 'Passwords do not match');
    }

    if (empty($errors)) {
        $chk = db()->prepare('SELECT id, email, username FROM members WHERE email = ? OR username = ?');
        $chk->execute([$email, $username]);
        foreach ($chk->fetchAll() as $row) {
            if ($row['email']    === $email)    $errors[] = t('อีเมลนี้ถูกใช้งานแล้ว', 'This email is already registered');
            if ($row['username'] === $username) $errors[] = t('ชื่อผู้ใช้งานนี้ถูกใช้งานแล้ว', 'This username is already taken');
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        db()->prepare(
            'INSERT INTO members (first_name, last_name, phone, email, username, password_hash)
             VALUES (?,?,?,?,?,?)'
        )->execute([$first_name, $last_name, $phone ?: null, $email, $username, $hash]);
        $success = true;
        $old     = [];
    }
}
?>
<!DOCTYPE html>
<html lang="<?= lang() ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= t('สมัครสมาชิก','Register') ?> | INVEZ</title>
    <meta name="robots" content="noindex, nofollow" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-[#fafaf8] text-[#1a1714] min-h-screen flex flex-col">

    <?php include('components/navbar.php'); ?>

    <div class="flex-1 flex items-center justify-center px-6 py-20">
        <div class="w-full max-w-lg">

            <div class="text-center mb-8">
                <h1 class="text-2xl font-semibold text-[#1a1714] mb-1"><?= t('สมัครสมาชิก','Create an Account') ?></h1>
                <p class="text-sm text-[#9d8f82]"><?= t('กรอกข้อมูลเพื่อสมัครสมาชิก INVEZ','Fill in your details to join INVEZ') ?></p>
            </div>

            <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl p-6 text-center">
                <svg class="w-10 h-10 text-green-500 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-semibold text-base mb-1"><?= t('สมัครสมาชิกสำเร็จ!','Registration Successful!') ?></p>
                <p class="text-sm text-green-700"><?= t('บัญชีของคุณกำลังรอการยืนยันจากแอดมิน กรุณารอการแจ้งเตือน','Your account is pending admin approval. Please wait for confirmation.') ?></p>
                <a href="/" class="inline-block mt-4 text-sm text-green-700 underline hover:text-green-900"><?= t('กลับหน้าหลัก','Back to Home') ?></a>
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

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('ชื่อ','First Name') ?> *</label>
                            <input type="text" name="first_name" value="<?= htmlspecialchars($old['first_name'] ?? '') ?>"
                                   class="w-full border border-[#e0dbd4] rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('นามสกุล','Last Name') ?> *</label>
                            <input type="text" name="last_name" value="<?= htmlspecialchars($old['last_name'] ?? '') ?>"
                                   class="w-full border border-[#e0dbd4] rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('เบอร์โทรศัพท์','Phone Number') ?></label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
                               class="w-full border border-[#e0dbd4] rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('อีเมล','Email') ?> *</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                               class="w-full border border-[#e0dbd4] rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('ชื่อผู้ใช้งาน','Username') ?> *</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($old['username'] ?? '') ?>"
                               placeholder="<?= t('ตัวอักษร ตัวเลข หรือ _ เท่านั้น','Letters, numbers or _ only') ?>"
                               class="w-full border border-[#e0dbd4] rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('รหัสผ่าน','Password') ?> *</label>
                        <input type="password" name="password" id="password"
                               placeholder="<?= t('อย่างน้อย 8 ตัว มีตัวอักษรและตัวเลข','Min 8 chars with letters and numbers') ?>"
                               class="w-full border border-[#e0dbd4] rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('ยืนยันรหัสผ่าน','Confirm Password') ?> *</label>
                        <input type="password" name="confirm_password" id="confirm_password"
                               class="w-full border border-[#e0dbd4] rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
                        <p id="pw-match-msg" class="text-xs mt-1 hidden"></p>
                    </div>

                    <button type="submit"
                            class="w-full bg-[#c9a96e] hover:bg-[#b8965e] text-white font-semibold py-2.5 rounded-lg text-sm transition-colors mt-2">
                        <?= t('สมัครสมาชิก','Create Account') ?>
                    </button>
                </form>

                <p class="text-center text-xs text-[#9d8f82] mt-5">
                    <?= t('มีบัญชีแล้ว?','Already have an account?') ?>
                    <a href="/login" class="text-[#c9a96e] hover:text-[#b8965e] font-medium"><?= t('เข้าสู่ระบบ','Login') ?></a>
                </p>
            </div>

            <?php endif; ?>

        </div>
    </div>

    <?php include('components/footer.php'); ?>

    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        feather.replace();
        // Live confirm password check
        const pwField      = document.getElementById('password');
        const confirmField = document.getElementById('confirm_password');
        const matchMsg     = document.getElementById('pw-match-msg');
        function checkMatch() {
            if (!confirmField.value) { matchMsg.classList.add('hidden'); return; }
            const ok = pwField.value === confirmField.value;
            matchMsg.classList.remove('hidden', 'text-red-500', 'text-green-600');
            matchMsg.classList.add(ok ? 'text-green-600' : 'text-red-500');
            matchMsg.textContent = ok
                ? '<?= t('รหัสผ่านตรงกัน','Passwords match') ?>'
                : '<?= t('รหัสผ่านไม่ตรงกัน','Passwords do not match') ?>';
        }
        confirmField?.addEventListener('input', checkMatch);
        pwField?.addEventListener('input', checkMatch);
    </script>
</body>
</html>
