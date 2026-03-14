<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Include/database.php';
require_once __DIR__ . '/../Include/view.php';
require_once __DIR__ . '/../databases/Users.php';

if (empty($_SESSION['user_id'])) {
    header('Location: /entrypj/sign_in');
    exit();
}

$user_id = $_SESSION['user_id'];

$user_data = getUserById($user_id);
if (!$user_data) {
    echo "<script>alert('ไม่พบข้อมูลบัญชี กรุณาเข้าสู่ระบบใหม่'); window.location.href='/entrypj/routes/logout.php?action=logout';</script>";
    exit();
}

$user_name = $user_data['name'];
$email = $user_data['email'];
$province = $user_data['province'] ?? 'ไม่ระบุ';
$gender = $user_data['gender'] ?? 'ไม่ระบุ';
$birthdate = $user_data['birthdate'] ?? '';

$age = 'ไม่ระบุ';
if (!empty($birthdate)) {
    $bday = new DateTime($birthdate);
    $today = new DateTime('today');
    $age = $bday->diff($today)->y;
}

$gender_display = htmlspecialchars($gender);
if ($gender == 'male' || $gender == 'ชาย') $gender_display = 'ชาย 👨';
elseif ($gender == 'female' || $gender == 'หญิง') $gender_display = 'หญิง 👩';
elseif ($gender == 'other' || $gender == 'อื่นๆ') $gender_display = 'อื่นๆ 🏳️‍🌈';

renderView('profile', [
    'user_id' => $user_id,
    'user_name' => $user_name,
    'email' => $email,
    'province' => $province,
    'gender_display' => $gender_display,
    'age' => $age,
]);

exit();
?>