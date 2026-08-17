<?php
require_once('_auth.php');
require_once('../config/lang.php');
require_once('../config/db.php');

$page_title = t('ตั้งค่าผู้ใช้งาน','User Settings');
$errors  = [];
$success = false;

$member = db()->prepare('SELECT * FROM members WHERE id = ?');
$member->execute([$_SESSION['member_id']]);
$member = $member->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    member_csrf_verify();

    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name']  ?? '');
    $phone      = trim($_POST['phone']      ?? '');
    $email      = trim($_POST['email']      ?? '');

    if ($first_name === '') $errors[] = t('กรุณากรอกชื่อ','Please enter first name');
    if ($last_name === '')  $errors[] = t('กรุณากรอกนามสกุล','Please enter last name');
    if ($email === '') {
        $errors[] = t('กรุณากรอกอีเมล','Please enter email');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = t('รูปแบบอีเมลไม่ถูกต้อง','Invalid email format');
    } else {
        $chk = db()->prepare('SELECT id FROM members WHERE email = ? AND id != ?');
        $chk->execute([$email, $_SESSION['member_id']]);
        if ($chk->fetch()) $errors[] = t('อีเมลนี้ถูกใช้งานแล้ว','Email already in use');
    }

    // Passwords are not changed here — that goes through the emailed reset link
    // instead, so a hijacked session cannot silently take over the account.

    if (empty($errors)) {
        db()->prepare('UPDATE members SET first_name=?,last_name=?,phone=?,email=? WHERE id=?')
           ->execute([$first_name, $last_name, $phone ?: null, $email, $_SESSION['member_id']]);

        $_SESSION['member_name'] = $first_name . ' ' . $last_name;
        $member = array_merge($member, ['first_name'=>$first_name,'last_name'=>$last_name,'phone'=>$phone,'email'=>$email]);
        $success = true;
    }
}

include('_header.php');
?>

<?php if ($success): ?>
<div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg mb-5"><?= t('บันทึกเรียบร้อย','Saved successfully') ?></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
<div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-5 space-y-1">
    <?php foreach ($errors as $e): ?><div>• <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
</div>
<?php endif; ?>

<div class="max-w-lg space-y-5">

    <form method="POST" class="bg-white rounded-xl border border-[#e8e4df] p-6 space-y-4">
        <input type="hidden" name="csrf_token" value="<?= member_csrf_token() ?>">
        <h3 class="font-semibold text-[#1a1714] text-sm"><?= t('ข้อมูลส่วนตัว','Personal Info') ?></h3>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('ชื่อ','First Name') ?> *</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($member['first_name']) ?>"
                       class="w-full border border-[#e0dbd4] rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('นามสกุล','Last Name') ?> *</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($member['last_name']) ?>"
                       class="w-full border border-[#e0dbd4] rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('เบอร์โทรศัพท์','Phone') ?></label>
            <input type="tel" name="phone" value="<?= htmlspecialchars($member['phone'] ?? '') ?>"
                   class="w-full border border-[#e0dbd4] rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
        </div>

        <div>
            <label class="block text-xs font-medium text-[#6b5f52] mb-1.5"><?= t('อีเมล','Email') ?> *</label>
            <input type="email" name="email" value="<?= htmlspecialchars($member['email']) ?>"
                   class="w-full border border-[#e0dbd4] rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
        </div>

        <div class="pt-1">
            <p class="text-xs text-[#9d8f82] mb-1"><?= t('ชื่อผู้ใช้งาน','Username') ?></p>
            <p class="text-sm text-[#1a1714] font-mono"><?= htmlspecialchars($member['username']) ?></p>
        </div>

        <button type="submit" class="bg-[#c9a96e] hover:bg-[#b8965e] text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition-colors">
            <?= t('บันทึก','Save') ?>
        </button>
    </form>

    <div class="bg-white rounded-xl border border-[#e8e4df] p-6">
        <h3 class="font-semibold text-[#1a1714] text-sm mb-1.5"><?= t('รหัสผ่าน','Password') ?></h3>
        <p class="text-xs text-[#9d8f82] mb-4 leading-5">
            <?= t('เพื่อความปลอดภัย การเปลี่ยนรหัสผ่านจะทำผ่านลิงก์ที่ส่งไปยังอีเมลของคุณ','For your security, changing your password is done through a link sent to your email') ?>
        </p>
        <a href="/forgot-password"
           class="inline-block border border-[#c9a96e] text-[#c9a96e] hover:bg-[#c9a96e] hover:text-white font-medium px-5 py-2.5 rounded-lg text-sm transition-colors">
            <?= t('เปลี่ยนรหัสผ่าน','Change Password') ?>
        </a>
    </div>

</div>

<?php include('_footer.php'); ?>
