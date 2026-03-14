<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Include/database.php';
require_once __DIR__ . '/../databases/Users.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ออกจากระบบ 
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $get_action = $_GET['action'] ?? '';

    if ($get_action == 'logout') {
        session_unset(); 
        session_destroy(); 
        echo "<script>
                alert('ออกจากระบบเรียบร้อยแล้ว ไว้พบกันใหม่ครับ!');
                window.location.href='/entrypj/sign_in.php';
              </script>";
        exit();
    }
}

?>