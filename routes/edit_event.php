<?php
// Controller สำหรับหน้าแก้ไขกิจกรรม
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Include/database.php';
require_once __DIR__ . '/../databases/Events.php';

if (empty($_SESSION['user_id'])) {
    header('Location: /entrypj/sign_in');
    exit();
}

$event_id = 0;
if (isset($_GET['id'])) {
    $event_id = intval($_GET['id']);
} elseif (isset($_POST['event_id'])) {
    $event_id = intval($_POST['event_id']);
}

$event = getEventById($event_id);

if (!$event || $event['organizer_id'] != $_SESSION['user_id']) {
    echo "<script>alert('ไม่พบข้อมูลกิจกรรมหรือคุณไม่มีสิทธิ์แก้ไข'); window.location.href='/entrypj/manage_event';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'update') {
        $id = intval($_POST['event_id'] ?? 0);

        $data = [
            'event_name' => trim($_POST['event_name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'start_date' => $_POST['start_date'] ?? '',
            'end_date' => $_POST['end_date'] ?? '',
            'max_participants' => intval($_POST['max_participants'] ?? 0),
            'location' => trim($_POST['location'] ?? ''),
        ];

        if (updateEvent($id, $data)) {
            
            // เช็คว่ามีการเลือกรูปใหม่เข้ามาหรือไม่
            if (isset($_FILES['event_images']) && !empty($_FILES['event_images']['name'][0])) {
                
                // ✅ เพิ่มคำสั่งลบประวัติรูปเก่าทิ้งก่อน (ต้องไปเพิ่มฟังก์ชันนี้ใน databases/Events.php ด้วยนะครับ)
                deleteAllEventImages($id);
                
                $upload_dir = __DIR__ . '/../uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $fileCount = count($_FILES['event_images']['name']);
                
                for ($i = 0; $i < $fileCount; $i++) {
                    if ($_FILES['event_images']['error'][$i] === UPLOAD_ERR_OK) {
                        
                        $file_name = time() . '_' . uniqid() . '_' . basename($_FILES['event_images']['name'][$i]);
                        $target_file = $upload_dir . $file_name;

                        if (move_uploaded_file($_FILES['event_images']['tmp_name'][$i], $target_file)) {
                            $image_path = '/entrypj/uploads/' . $file_name;
                            
                            addEventImage($id, $image_path); 
                        }
                    }
                }
            }

            echo "<script>alert('แก้ไขข้อมูลกิจกรรมสำเร็จ!'); window.location.href='/entrypj/manage_event.php';</script>";
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดในการแก้ไขข้อมูล'); window.history.back();</script>";
        }
        exit();
    }
}
renderView('edit_event', ['event' => $event]);
?>