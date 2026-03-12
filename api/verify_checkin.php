<?php
// ไฟล์: routes/verify_checkin.php
require_once 'Include/database.php';
require_once 'databases/Registrations.php'; // โหลด Model



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_otp = trim($_POST['otp']);
    $event_id = intval($_POST['event_id']);

    // 1. อ่านข้อมูล OTP จากไฟล์ JSON
    $json_file = 'databases/otp_data.json';
    if (!file_exists($json_file)) {
        $_SESSION['error_msg'] = "ไม่พบระบบฐานข้อมูล OTP";
        header("Location: /entrypj/event_detail?id=" . $event_id);
        exit;
    }

    $otp_data = json_decode(file_get_contents($json_file), true);
    $matched_email = null;

    // 2. ตรวจสอบว่า OTP ตรงกับในระบบและยังไม่หมดเวลาหรือไม่
    foreach ($otp_data as $email => $data) {
        if ($data['code'] == $input_otp) {
            // ตรวจสอบเวลาหมดอายุ (30 นาที)
            if (time() > $data['expires_at']) {
                unset($otp_data[$email]); // ลบ OTP ที่หมดอายุทิ้ง
                file_put_contents($json_file, json_encode($otp_data, JSON_PRETTY_PRINT));
                
                $_SESSION['error_msg'] = "รหัส OTP หมดอายุแล้ว กรุณาให้ผู้เข้าร่วมขอรหัสใหม่";
                header("Location: /entrypj/event_detail?id=" . $event_id);
                exit;
            }
            $matched_email = $email;
            break;
        }
    }

    // 3. ถ้ารหัสไม่ถูกต้อง
    if (!$matched_email) {
        $_SESSION['error_msg'] = "รหัส OTP ไม่ถูกต้อง หรือไม่มีในระบบ";
        header("Location: /entrypj/event_detail?id=" . $event_id);
        exit;
    }

    // 4. ถ้ารหัสถูกต้อง ให้ตรวจสอบสิทธิ์การเข้าร่วมจากฐานข้อมูล
    global $conn;
    // ดึงข้อมูลการลงทะเบียน โดยเช็คจาก email และ event_id
    $sql = "SELECT r.registration_id, r.status, u.name 
            FROM registrations r 
            JOIN users u ON r.user_id = u.user_id 
            WHERE u.email = ? AND r.event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $matched_email, $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $registration_id = $row['registration_id'];
        
        // เช็คว่าผู้จัดอนุมัติให้เข้าร่วมกิจกรรมหรือยัง (status = 'approved')
        if ($row['status'] !== 'approved') {
            $_SESSION['error_msg'] = "ผู้ใช้นี้ยืนยัน OTP ถูกต้อง แต่ยังไม่ได้รับการอนุมัติให้เข้าร่วมงาน";
            header("Location: /entrypj/event_detail?id=" . $event_id);
            exit;
        }

        // 5. บันทึกการเช็คอิน (อัปเดต is_checked_in = 1)
        updateCheckInStatus($registration_id, 1); // ฟังก์ชันนี้มีอยู่แล้วใน Registrations.php

        // ลบ OTP ออกจากระบบ เพื่อไม่ให้ใช้ซ้ำได้อีก
        unset($otp_data[$matched_email]);
        file_put_contents($json_file, json_encode($otp_data, JSON_PRETTY_PRINT));
        
        $user_name = !empty($row['name']) ? $row['name'] : 'ไม่ระบุชื่อ';
        $_SESSION['success_msg'] = "เช็คอินสำเร็จ! ยินดีต้อนรับคุณ " . $user_name;

    } else {
        $_SESSION['error_msg'] = "ไม่พบข้อมูลผู้ใช้นี้ในกิจกรรมนี้";
    }

    // กลับไปที่หน้าเช็คอิน
    header("Location: /entrypj/event_detail?id=" . $event_id);
    exit;
}
?>