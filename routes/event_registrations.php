<?php
// ตรวจสอบสถานะ Session ก่อนเปิด
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Include/database.php';
require_once __DIR__ . '/../Include/view.php';
require_once __DIR__ . '/../databases/Events.php';
require_once __DIR__ . '/../databases/Registrations.php';

if (empty($_SESSION['user_id'])) {
    header("Location: /entrypj/sign_in");
    exit();
}

$event_id = $_GET['event_id'] ?? 0;
if ($event_id == 0) {
    die("ไม่พบรหัสกิจกรรม");
}

$event = getEventById($event_id);

// ป้องกันคนอื่นแอบเข้า
if ($event['organizer_id'] != $_SESSION['user_id']) {
    echo "<script>alert('คุณไม่มีสิทธิ์จัดการกิจกรรมนี้!'); window.location.href='/entrypj/home';</script>";
    exit();
}

$registrations = getRegistrationsByEvent($event_id);

// ดึงข้อความแจ้งเตือนจากการเช็คอินด้วย OTP
$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);

renderView('event_registrations', [
    'event' => $event,
    'event_id' => $event_id,
    'registrations' => $registrations,
    'success_msg' => $success_msg,
    'error_msg' => $error_msg
]);

exit();
?>
<