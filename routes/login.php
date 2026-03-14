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

if ($action == 'login') {
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        $user = getUserByEmail($email);

        // ✅ แก้ไข: เพิ่ม password_verify เพื่อให้ถอดรหัสผ่านตอนล็อกอินได้ (สำคัญมาก!)
        if ($user && password_verify($password, $user['password'])) {
           
            $_SESSION['user_id'] = $user['user_id']; 
            $_SESSION['name'] = $user['name'];
            
            $show_name = htmlspecialchars($user['name']);
            // แก้ให้เด้งกลับไปที่หน้าแรก
            echo "<script>
                    alert('เข้าสู่ระบบสำเร็จ! ยินดีต้อนรับคุณ $show_name'); 
                    window.location.href='/entrypj/home.php';
                  </script>";
        } else {
            echo "<script>alert('อีเมลหรือรหัสผ่านไม่ถูกต้อง'); window.history.back();</script>";
        }
        exit();
    }

}
?>