<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Include/database.php';
require_once __DIR__ . '/../databases/Users.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    // สมัครสมาชิก
    if ($action == 'register') {
        $userData = [
            'name'      => $_POST['name']?? null,
            'gender'    => $_POST['gender']?? null,
            'birthdate' => $_POST['birthdate']?? null,
            'province'  => $_POST['province']?? null,
            'email'     => $_POST['email']?? null,
            'password'  => $_POST['password']?? null
        ];

        if (createUser($userData)) {
            echo "<script>
                    alert('สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ'); 
                    window.location.href='/entrypj/sign_in.php';
                  </script>";
        } else {
            echo "<script>alert('เกิดข้อผิดพลาด! อีเมลนี้อาจมีผู้ใช้งานแล้ว'); window.history.back();</script>";
        }
        exit();
    
    // เข้าสู่ระบบ 
    } 
}

?>