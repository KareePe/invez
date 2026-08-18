<?php
$current_page = 'register';
require_once('config/lang.php');
require_once('config/db.php');

if (!empty($_SESSION['member_id'])) {
    header('Location: /');
    exit;
}

if (empty($_SESSION['_register_csrf'])) {
    $_SESSION['_register_csrf'] = bin2hex(random_bytes(32));
}

$errors  = [];
$success = false;
$old     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['_register_csrf']) || !hash_equals($_SESSION['_register_csrf'], $_POST['_register_csrf'])) {
        http_response_code(403);
        die('Invalid request');
    }

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
    if ($phone === '') {
        $errors[] = t('กรุณากรอกเบอร์โทรศัพท์', 'Please enter your phone number');
    } elseif (!preg_match('/^[0-9]+$/', $phone)) {
        $errors[] = t('เบอร์โทรศัพท์ต้องเป็นตัวเลขเท่านั้น', 'Phone number must contain digits only');
    } elseif (strlen($phone) !== 10) {
        $errors[] = t('เบอร์โทรศัพท์ต้องมี 10 หลักพอดี', 'Phone number must be exactly 10 digits');
    }
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
        // Separate checks (not elseif) so the user sees every missing requirement at once,
        // matching the live checklist rendered under the password field.
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
                    <input type="hidden" name="_register_csrf" value="<?= htmlspecialchars($_SESSION['_register_csrf']) ?>">

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
                        <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('เบอร์โทรศัพท์','Phone Number') ?> *</label>
                        <input type="tel" name="phone" inputmode="numeric" value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
                               class="w-full border border-[#e0dbd4] rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
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
                        <input type="password" name="password" id="password" data-pw-rules="pw-rules"
                               class="w-full border border-[#e0dbd4] rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
                        <ul id="pw-rules" class="text-xs text-[#9d8f82] mt-2 space-y-1">
                            <li data-pw-rule="len"><span data-pw-mark>•</span> <?= t('อย่างน้อย 8 ตัวอักษร','At least 8 characters') ?></li>
                            <li data-pw-rule="digit"><span data-pw-mark>•</span> <?= t('มีตัวเลขอย่างน้อย 1 ตัว (0-9)','At least one number (0-9)') ?></li>
                            <li data-pw-rule="lower"><span data-pw-mark>•</span> <?= t('มีตัวพิมพ์เล็กอย่างน้อย 1 ตัว (a-z)','At least one lowercase letter (a-z)') ?></li>
                            <li data-pw-rule="upper"><span data-pw-mark>•</span> <?= t('มีตัวพิมพ์ใหญ่อย่างน้อย 1 ตัว (A-Z)','At least one uppercase letter (A-Z)') ?></li>
                        </ul>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('ยืนยันรหัสผ่าน','Confirm Password') ?> *</label>
                        <input type="password" name="confirm_password" id="confirm_password" data-pw-match="password"
                               class="w-full border border-[#e0dbd4] rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
                    </div>

                    <button type="submit"
                            class="w-full bg-[#c9a96e] hover:bg-[#b8965e] text-white font-semibold py-2.5 rounded-lg text-sm transition-colors mt-2">
                        <?= t('สมัครสมาชิก','Create Account') ?>
                    </button>

                    <div class="mt-4 text-xs text-red-600 leading-relaxed space-y-1">
                        <p><span class="font-semibold">*</span> <?= t('หมายเหตุ: เมื่อเกิดการซื้อขายทรัพย์สินผ่านเว็บไซต์ ผู้ลงประกาศตกลงชำระค่าคอมมิชชั่นให้แก่เจ้าของเว็บไซต์ในอัตราร้อยละ 3 ของราคาซื้อขาย','Note: Upon completion of a property sale arranged through this website, the lister agrees to pay the website owner a commission of 3% of the sale price.') ?></p>
                        <p><span class="font-semibold">*</span> <?= t('หมายเหตุ: กรณีเกิดการให้เช่าตามสัญญาระยะเวลา 1 ปี เจ้าของเว็บไซต์จะได้รับค่าตอบแทนเทียบเท่าค่าเช่า 1 เดือน จากค่าเช่าทั้งหมด 12 เดือน','Note: In the case of a lease with a one-year term, the website owner shall receive compensation equivalent to one month of rent out of the twelve-month term.') ?></p>
                    </div>
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
    <script>feather.replace();</script>
    <?php include('components/password-script.php'); ?>

    <script>
    /* Keep a local draft of the registration form so a visitor who leaves and comes back
       does not have to retype everything. Passwords are never stored. */
    (function () {
        var KEY     = 'invez_register_draft';
        var MAX_AGE = 24 * 60 * 60 * 1000;   /* drop drafts older than a day */
        var FIELDS  = ['first_name', 'last_name', 'phone', 'email', 'username'];

        var store;
        try {
            store = window.localStorage;
            store.setItem(KEY + '_probe', '1');
            store.removeItem(KEY + '_probe');
        } catch (e) {
            return;                          /* private mode / storage disabled */
        }

<?php if ($success): ?>
        store.removeItem(KEY);               /* registered successfully — draft no longer needed */
<?php else: ?>
        var form = document.querySelector('form[method="POST"]');
        if (!form) return;

        var inputs = [];
        for (var i = 0; i < FIELDS.length; i++) {
            var el = form.querySelector('[name="' + FIELDS[i] + '"]');
            if (el) inputs.push(el);
        }

        /* restore — only into fields the server left empty, so PHP's $old repopulation wins */
        var draft = null;
        try {
            draft = JSON.parse(store.getItem(KEY));
        } catch (e) {
            store.removeItem(KEY);
        }

        if (draft && draft.t && (Date.now() - draft.t) < MAX_AGE) {
            for (var j = 0; j < inputs.length; j++) {
                if (!inputs[j].value && draft.v[inputs[j].name]) {
                    inputs[j].value = draft.v[inputs[j].name];
                }
            }
        } else if (draft) {
            store.removeItem(KEY);
        }

        /* save */
        function save() {
            var values = {};
            for (var k = 0; k < inputs.length; k++) values[inputs[k].name] = inputs[k].value;
            store.setItem(KEY, JSON.stringify({ t: Date.now(), v: values }));
        }

        for (var m = 0; m < inputs.length; m++) {
            inputs[m].addEventListener('input', save);
        }
<?php endif; ?>
    })();

    /* Live phone validation — required, digits only, exactly 10. Mirrors the server-side rule
       in this file. Runs after the draft restore above so a restored value is checked on load. */
    (function () {
        var input = document.querySelector('[name="phone"]');
        if (!input) return;

        var REQUIRED    = <?= json_encode(t('กรุณากรอกเบอร์โทรศัพท์', 'Please enter your phone number'), JSON_UNESCAPED_UNICODE) ?>;
        var ONLY_DIGITS = <?= json_encode(t('เบอร์โทรศัพท์ต้องเป็นตัวเลขเท่านั้น', 'Phone number must contain digits only'), JSON_UNESCAPED_UNICODE) ?>;
        var BAD_LENGTH  = <?= json_encode(t('เบอร์โทรศัพท์ต้องมี 10 หลักพอดี', 'Phone number must be exactly 10 digits'), JSON_UNESCAPED_UNICODE) ?>;

        var msg = document.createElement('p');
        msg.style.cssText = 'font-size:0.75rem;line-height:1rem;margin-top:0.25rem;color:#ef4444;display:none;';
        input.parentNode.insertBefore(msg, input.nextSibling);

        /* Don't nag about an empty field before the visitor has touched it —
           the "required" error only appears once they've typed in it or left it. */
        var touched = false;

        function check() {
            var value = input.value;
            var error = '';

            if (value === '') {
                if (touched)                error = REQUIRED;
            } else if (!/^[0-9]+$/.test(value)) {
                                            error = ONLY_DIGITS;
            } else if (value.length !== 10) {
                                            error = BAD_LENGTH;
            }

            msg.textContent         = error;
            msg.style.display       = error ? 'block' : 'none';
            input.style.borderColor = error ? '#ef4444' : '';
        }

        function touch() { touched = true; check(); }

        input.addEventListener('input', touch);
        input.addEventListener('blur', touch);
        check();
    })();
    </script>

    <?php /* after the draft restore above, so a restored email is validated on load too */ ?>
    <?php include('components/email-script.php'); ?>
</body>
</html>
