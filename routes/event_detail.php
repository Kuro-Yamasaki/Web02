<?php
// ✅ เช็คก่อนเปิด Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Include/database.php';
require_once __DIR__ . '/../Include/view.php';
require_once __DIR__ . '/../databases/Events.php';

$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'] ?? null; 

if ($event_id <= 0) {
    echo "<script>alert('ไม่พบรหัสกิจกรรม'); window.location.href='/entrypj/home';</script>";
    exit();
}

$event = getEventById($event_id);
$images = getAllEventImages($event_id);

if (!$event) {
    echo "<script>alert('ไม่พบข้อมูลกิจกรรมนี้'); window.location.href='/entrypj/home';</script>";
    exit();
}

$conn = getConnection();

// 1. เช็คสถานะผู้ใช้งาน
$registration_status = null;
if ($user_id) {
    $stmt = $conn->prepare("SELECT status FROM registrations WHERE user_id = ? AND event_id = ?");
    $stmt->bind_param("ii", $user_id, $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $registration_status = strtolower($row['status']); 
    }
    $stmt->close();
}

// 2. นับจำนวนคนเข้าร่วมที่ได้รับการอนุมัติ
$current_joined = 0;
$stmt_count = $conn->prepare("SELECT COUNT(*) AS total_joined FROM registrations WHERE event_id = ? AND (status = 'approved' OR status = 'Approved')");
$stmt_count->bind_param("i", $event_id);
$stmt_count->execute();
$res_count = $stmt_count->get_result();
if ($row_count = $res_count->fetch_assoc()) {
    $current_joined = $row_count['total_joined'];
}
$stmt_count->close();

// 3. กำหนดเงื่อนไขคนเต็มและเวลาจบกิจกรรม
$is_full = ($event['max_participants'] > 0 && $current_joined >= $event['max_participants']);
$is_ended = (time() > strtotime($event['end_date']));

// แสดงผลด้วย template
renderView('event_detail', [
    'event' => $event,
    'images' => $images,
    'user_id' => $user_id,
    'registration_status' => $registration_status,
    'current_joined' => $current_joined,
    'is_full' => $is_full,
    'is_ended' => $is_ended,
    'event_id' => $event_id,
]);

exit();
?>