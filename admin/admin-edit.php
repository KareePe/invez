<?php
require_once('auth.php');
require_once('../config/db.php');

$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_new = $id === 0;
$page_title = $is_new ? 'เพิ่มแอดมิน' : 'แก้ไขแอดมิน';

$admin  = [];
$errors = [];

if (!$is_new) {
    $admin = db()->prepare('SELECT id, username, name FROM admins WHERE id = ?');
    $admin->execute([$id]);
    $admin = $admin->fetch();
    if (!$admin) {
        flash('error', 'ไม่พบแอดมิน');
        header('Location: admins');
        exit;
    }
}

if (!$is_new
    && $admin['username'] === 'invez_test'
    && ($_SESSION['admin_username'] ?? '') !== 'invez_test'
) {
    flash('error', 'ไม่มีสิทธิ์แก้ไขบัญชีนี้');
    header('Location: admins');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $username = trim($_POST['username'] ?? '');
    $name     = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if ($username === '') $errors[] = 'กรุณากรอก username';
    if ($name === '')     $errors[] = 'กรุณากรอกชื่อ';

    if ($is_new && $password === '') {
        $errors[] = 'กรุณากรอก password';
    }
    if ($password !== '' && strlen($password) < 8) {
        $errors[] = 'Password ต้องมีอย่างน้อย 8 ตัวอักษร';
    }
    if ($password !== '' && $password !== $confirm) {
        $errors[] = 'Password ไม่ตรงกัน';
    }

    // Check duplicate username
    if (empty($errors)) {
        $dup = db()->prepare('SELECT id FROM admins WHERE username = ? AND id != ?');
        $dup->execute([$username, $id]);
        if ($dup->fetch()) $errors[] = 'Username นี้ถูกใช้แล้ว';
    }

    if (empty($errors)) {
        if ($is_new) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            db()->prepare('INSERT INTO admins (username, password, name) VALUES (?,?,?)')->execute([$username, $hash, $name]);
        } else {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                db()->prepare('UPDATE admins SET username=?, name=?, password=? WHERE id=?')->execute([$username, $name, $hash, $id]);
            } else {
                db()->prepare('UPDATE admins SET username=?, name=? WHERE id=?')->execute([$username, $name, $id]);
            }
            // Update session name if editing self
            if ($id === (int)$_SESSION['admin_id']) {
                $_SESSION['admin_name'] = $name;
            }
        }
        $saved_id = $is_new ? (int)db()->lastInsertId() : $id;
        log_admin_activity($is_new ? 'create' : 'update', 'admin', $saved_id, $name);
        flash('success', $is_new ? 'เพิ่มแอดมินสำเร็จ' : 'บันทึกแอดมินสำเร็จ');
        header('Location: admins');
        exit;
    }

    $admin = ['username' => $username, 'name' => $name];
}

include('_header.php');
?>

<?php if (!empty($errors)): ?>
<div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-5">
    <?php foreach ($errors as $e): ?>
    <div>• <?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<form method="POST" class="space-y-5 max-w-md">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Username *</label>
            <input type="text" name="username" value="<?= htmlspecialchars($admin['username'] ?? '') ?>"
                   autocomplete="off"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">ชื่อที่แสดง *</label>
            <input type="text" name="name" value="<?= htmlspecialchars($admin['name'] ?? '') ?>"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]" required>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">
                Password <?= $is_new ? '*' : '(เว้นว่างถ้าไม่เปลี่ยน)' ?>
            </label>
            <input type="password" name="password" autocomplete="new-password"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]"
                   <?= $is_new ? 'required' : '' ?>>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">ยืนยัน Password</label>
            <input type="password" name="confirm" autocomplete="new-password"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#c9a96e]">
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="bg-[#c9a96e] hover:bg-[#b8965e] text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition-colors">
            บันทึก
        </button>
        <a href="admins" class="text-sm text-gray-500 hover:text-gray-700">ยกเลิก</a>
    </div>
</form>

<?php include('_footer.php'); ?>
