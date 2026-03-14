<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../Include/database.php';
require_once __DIR__ . '/../Include/view.php';
require_once __DIR__ . '/../databases/Events.php';

if (empty($_SESSION['user_id'])) {
    header('Location: /entrypj/sign_in');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    $data = [
        'organizer_id'     => $_SESSION['user_id'],
        'event_name'       => trim($_POST['event_name'] ?? ''),
        'description'      => trim($_POST['description'] ?? ''),
        'start_date'       => $_POST['start_date'] ?? '',
        'end_date'         => $_POST['end_date'] ?? '',
        'max_participants' => !empty($_POST['max_participants']) ? intval($_POST['max_participants']) : null,
        'location'         => trim($_POST['location'] ?? '')
    ];

    if ($action === 'create') {
        $new_event_id = createEvent($data);

        if ($new_event_id) {
            $upload_dir = __DIR__ . '/../uploads/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (isset($_FILES['event_images']) && !empty($_FILES['event_images']['name'][0])) {
                $fileCount = count($_FILES['event_images']['name']);

                for ($i = 0; $i < $fileCount; $i++) {
                    if ($_FILES['event_images']['error'][$i] === UPLOAD_ERR_OK) {
                        $file_name = time() . '_' . uniqid() . '_' . basename($_FILES['event_images']['name'][$i]);
                        $target_file = $upload_dir . $file_name;

                        if (move_uploaded_file($_FILES['event_images']['tmp_name'][$i], $target_file)) {
                            $image_path = '/entrypj/uploads/' . $file_name;
                            addEventImage($new_event_id, $image_path);
                        }
                    }
                }
            }

            echo "<script>
                    alert('บันทึกกิจกรรมและอัปโหลดรูปภาพเรียบร้อยแล้ว!');
                    window.location.href='/entrypj/manage_event';
                  </script>";
            exit();
        }

        echo "<script>
                alert('เกิดข้อผิดพลาดในการบันทึกข้อมูลกิจกรรมลงฐานข้อมูล');
                window.history.back();
              </script>";
        exit();
    }
}

// GET: แสดงหน้าสร้างกิจกรรม
renderView('create_event');
?>