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

// ปุ่ม ลบ (GET) 
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($action == 'delete' && $id > 0) {
        if (deleteEvent($id)) {
            echo "<script>alert('ลบกิจกรรมเรียบร้อยแล้ว'); window.location.href='/entrypj/manage_event';</script>";
            exit();
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดในการลบ'); window.history.back();</script>";
            exit();
        }
    }
}

// แสดงหน้า manage_event
renderView('manage_event');
exit();
?>
