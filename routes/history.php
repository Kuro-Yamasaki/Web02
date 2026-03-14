<?php
// ตรวจสอบสถานะ Session ก่อนเปิด
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Include/database.php';
require_once __DIR__ . '/../databases/Registrations.php'; 

// ตรวจสอบการล็อกอิน
if (empty($_SESSION['user_id'])) {
    header("Location: /entrypj/sign_in");
    exit();
}

$user_id = $_SESSION['user_id'];
$history = getUserHistory($user_id);

// ดึงอีเมลของผู้ใช้เพื่อใช้แสดงในหน้าต่างขอ OTP
$conn = getConnection(); 
$stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$user_email = $userData ? $userData['email'] : '';
$stmt->close();

renderView('history', [
    'history' => $history,
    'user_email' => $user_email
]);

?>