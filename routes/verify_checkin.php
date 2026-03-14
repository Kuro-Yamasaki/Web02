<?php
// ไฟล์: routes/verify_checkin.php

// 1. ✅ เปิด Session (สำคัญมาก ไม่งั้นข้อความแจ้งเตือนจะหายไปตอนเปลี่ยนหน้า)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. ✅ เติม __DIR__ เพื่อป้องกันหาไฟล์ไม่เจอ
require_once __DIR__ . '/../Include/database.php';
require_once __DIR__ . '/../databases/Registrations.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_otp = trim($_POST['otp']);
    $event_id = intval($_POST['event_id']);

    // ✅ สร้างตัวแปร URL แบบสั้น สำหรับเด้งกลับ (ลบ .php ออก)
    $redirect_url = "/entrypj/event_registrations?event_id=" . $event_id;

    // 1. อ่านข้อมูล OTP จากไฟล์ JSON
    $json_file = __DIR__ . '/../databases/otp_data.json';
    if (!file_exists($json_file)) {
        $_SESSION['error_msg'] = "ไม่พบระบบฐานข้อมูล OTP";
        header("Location: " . $redirect_url);
        exit;
    }

    $otp_data = json_decode(file_get_contents($json_file), true);
    $matched_email = null;

    // 2. ตรวจสอบว่า OTP ตรงกับในระบบและยังไม่หมดเวลาหรือไม่
    foreach ($otp_data as $email => $data) {
        if ($data['code'] == $input_otp) {
            // ตรวจสอบเวลาหมดอายุ (5 นาที)
            if (time() > $data['expires_at']) {
                unset($otp_data[$email]); 
                file_put_contents($json_file, json_encode($otp_data, JSON_PRETTY_PRINT));
                
                $_SESSION['error_msg'] = "รหัส OTP หมดอายุแล้ว กรุณาให้ผู้เข้าร่วมขอรหัสใหม่";
                header("Location: " . $redirect_url);
                exit;
            }
            $matched_email = $email;
            break;
        }
    }

    // 3. ถ้ารหัสไม่ถูกต้อง
    if (!$matched_email) {
        $_SESSION['error_msg'] = "รหัส OTP ไม่ถูกต้อง หรือไม่มีในระบบ";
        header("Location: " . $redirect_url);
        exit;
    }

    // 4. ถ้ารหัสถูกต้อง ให้ตรวจสอบสิทธิ์การเข้าร่วมจากฐานข้อมูล
    global $conn;
    $sql = "SELECT r.registration_id, r.status, u.name, r.is_checked_in 
            FROM registrations r 
            JOIN users u ON r.user_id = u.user_id 
            WHERE u.email = ? AND r.event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $matched_email, $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $registration_id = $row['registration_id'];
        
        // ดึงชื่อมาเก็บไว้ก่อนเลย
        $user_name = !empty($row['name']) ? $row['name'] : 'ไม่ระบุชื่อ';
        
        // เช็คว่าผู้จัดอนุมัติให้เข้าร่วมกิจกรรมหรือยัง 
        if (strtolower($row['status']) !== 'approved') {
            $_SESSION['error_msg'] = "คุณ " . $user_name . " รหัสถูกต้อง แต่ยังไม่ได้รับการอนุมัติให้เข้าร่วมงาน";
            header("Location: " . $redirect_url);
            exit;
        }

        // เช็คว่าเคยเช็คอินไปแล้วหรือไม่
        if ($row['is_checked_in'] == 1) {
            $_SESSION['error_msg'] = "ผู้ใช้นี้ (คุณ " . $user_name . ") ทำการเช็คอินไปแล้ว!";
            header("Location: " . $redirect_url);
            exit;
        }

        // 5. บันทึกการเช็คอินลงฐานข้อมูล
        updateCheckInStatus($registration_id, 1); 

        // ลบ OTP ออกจากระบบ เพื่อไม่ให้ใช้ซ้ำได้อีก
        unset($otp_data[$matched_email]);
        file_put_contents($json_file, json_encode($otp_data, JSON_PRETTY_PRINT));
        
        // ✅ ส่งชื่อกลับไปโชว์ที่หน้าจอ (ข้อความสีเขียว)
        $_SESSION['success_msg'] = "เช็คอินสำเร็จ! ยืนยันตัวตนของ: คุณ " . $user_name;

    } else {
        $_SESSION['error_msg'] = "ไม่พบข้อมูลผู้ใช้นี้ในกิจกรรมนี้";
    }

    // กลับไปที่หน้าจัดการผู้ลงทะเบียน
    header("Location: " . $redirect_url);
    exit;
}
?>