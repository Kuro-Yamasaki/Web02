<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['otp'])) {
    $email = $_POST['email'];
    $input_otp = $_POST['otp'];

    $json_file = '../databases/otp_data.json';
    if (!file_exists($json_file)) {
         echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูลระบบ OTP']);
         exit;
    }

    $otp_data = json_decode(file_get_contents($json_file), true);

    // เช็คว่ามีข้อมูลของอีเมลนี้ไหม
    if (!isset($otp_data[$email])) {
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบรหัส OTP หรือผู้ใช้ยังไม่ได้กดขอรหัส']);
        exit;
    }

    // เช็คเวลาหมดอายุ
    if (time() > $otp_data[$email]['expires_at']) {
        unset($otp_data[$email]); // ลบทิ้ง
        file_put_contents($json_file, json_encode($otp_data, JSON_PRETTY_PRINT));
        echo json_encode(['status' => 'error', 'message' => 'รหัสหมดอายุแล้ว กรุณาให้ผู้เข้าร่วมกดขอใหม่']);
        exit;
    }

    // เทียบรหัส
    if ($input_otp == $otp_data[$email]['code']) {
        unset($otp_data[$email]); // ใช้แล้วลบทิ้งทันทีไม่ให้ใช้ซ้ำ
        file_put_contents($json_file, json_encode($otp_data, JSON_PRETTY_PRINT));
        
        // ตรงนี้สามารถดึงไฟล์ฐานข้อมูลหรือ Database มา Update status เป็น 'เช็คอินแล้ว' ได้เลย
        
        echo json_encode(['status' => 'success', 'message' => 'เช็คอินสำเร็จ!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'รหัส OTP ไม่ถูกต้อง']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
}
?>