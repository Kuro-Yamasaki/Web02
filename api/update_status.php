<?php
// ไฟล์: api/update_status.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Include/database.php';
require_once __DIR__ . '/../databases/Registrations.php';

// ตั้งค่าให้ตอบกลับเป็นไฟล์ JSON สำหรับ AJAX
header('Content-Type: application/json');

// เช็คว่าล็อกอินหรือยัง
if (empty($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณาเข้าสู่ระบบก่อน']);
    exit;
}

$registration_id = intval($_POST['registration_id'] ?? 0);
$status = $_POST['status'] ?? '';

if ($registration_id > 0 && !empty($status)) {
    // เรียกใช้ฟังก์ชันอัปเดตสถานะใน Registrations.php
    if (updateRegistrationStatus($registration_id, $status)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
}
?>