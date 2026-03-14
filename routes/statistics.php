<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Include/database.php';
require_once __DIR__ . '/../databases/Events.php';

if (empty($_SESSION['user_id'])) {
    header("Location: /entrypj/sign_in");
    exit();
}

// ต้องระบุ event_id ถึงจะดูสถิติได้
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
if ($event_id <= 0) {
    echo "<script>alert('ไม่พบรหัสกิจกรรม'); window.location.href='/entrypj/home';</script>";
    exit();
}

$event = getEventById($event_id);
if (!$event) {
    echo "<script>alert('กิจกรรมไม่ถูกต้องหรือถูกลบแล้ว'); window.location.href='/entrypj/home';</script>";
    exit();
}

// ป้องกันคนอื่นแอบดูสถิติ (ต้องเป็นผู้จัดเท่านั้น)
if ($event['organizer_id'] != $_SESSION['user_id']) {
    echo "<script>alert('คุณไม่มีสิทธิ์ดูสถิติของกิจกรรมนี้'); window.location.href='/entrypj/home.php';</script>";
    exit();
}

$conn = getConnection();

// 1. ดึงสถิติเพศ (เฉพาะที่ได้รับอนุมัติในกิจกรรมนี้)
$genders = ['ชาย' => 0, 'หญิง' => 0, 'อื่นๆ/ไม่ระบุ' => 0];
$stmt = $conn->prepare("SELECT u.gender, COUNT(*) as cnt FROM users u JOIN registrations r ON u.user_id = r.user_id WHERE r.event_id = ? AND (r.status = 'approved' OR r.status = 'Approved') GROUP BY u.gender");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $g = $row['gender'];
    if ($g == 'Male') $genders['ชาย'] += $row['cnt'];
    elseif ($g == 'Female') $genders['หญิง'] += $row['cnt'];
    else $genders['อื่นๆ/ไม่ระบุ'] += $row['cnt'];
}
$stmt->close();
$max_gender = max($genders) > 0 ? max($genders) : 1;

// 2. ดึงสถิติจังหวัด (เฉพาะที่ได้รับอนุมัติในกิจกรรมนี้)
$valid_provinces = [
    "กรุงเทพมหานคร", "กระบี่", "กาญจนบุรี", "กาฬสินธุ์", "กำแพงเพชร", "ขอนแก่น", "จันทบุรี", "ฉะเชิงเทรา", "ชลบุรี", "ชัยนาท", 
    "ชัยภูมิ", "ชุมพร", "เชียงราย", "เชียงใหม่", "ตรัง", "ตราด", "ตาก", "นครนายก", "นครปฐม", "นครพนม", "นครราชสีมา", 
    "นครศรีธรรมราช", "นครสวรรค์", "นนทบุรี", "นราธิวาส", "น่าน", "บึงกาฬ", "บุรีรัมย์", "ปทุมธานี", "ประจวบคีรีขันธ์", 
    "ปราจีนบุรี", "ปัตตานี", "พระนครศรีอยุธยา", "พะเยา", "พังงา", "พัทลุง", "พิจิตร", "พิษณุโลก", "เพชรบุรี", "เพชรบูรณ์", 
    "แพร่", "ภูเก็ต", "มหาสารคาม", "มุกดาหาร", "แม่ฮ่องสอน", "ยโสธร", "ยะลา", "ร้อยเอ็ด", "ระนอง", "ระยอง", "ราชบุรี", 
    "ลพบุรี", "ลำปาง", "ลำพูน", "เลย", "ศรีสะเกษ", "สกลนคร", "สงขลา", "สตูล", "สมุทรปราการ", "สมุทรสงคราม", "สมุทรสาคร", 
    "สระแก้ว", "สระบุรี", "สิงห์บุรี", "สุโขทัย", "สุพรรณบุรี", "สุราษฎร์ธานี", "สุรินทร์", "หนองคาย", "หนองบัวลำภู", 
    "อ่างทอง", "อำนาจเจริญ", "อุดรธานี", "อุตรดิตถ์", "อุทัยธานี", "อุบลราชธานี"
];

$provinces_raw = [];
// ลบ LIMIT 5 ใน SQL ออกก่อน เพราะเราต้องเอามาจัดกลุ่มใน PHP ก่อน
$stmt2 = $conn->prepare("SELECT u.province, COUNT(*) as cnt FROM users u JOIN registrations r ON u.user_id = r.user_id WHERE r.event_id = ? AND (r.status = 'approved' OR r.status = 'Approved') AND u.province != '' GROUP BY u.province");
$stmt2->bind_param("i", $event_id);
$stmt2->execute();
$res2 = $stmt2->get_result();

while ($row = $res2->fetch_assoc()) {
    $prov_name = trim($row['province']);
    
    // **ตัวกรองความฉลาด:** ถ้าจังหวัดที่ดึงมา ไม่มีใน Array 77 จังหวัด ให้ปัดเป็น "อื่นๆ"
    if (!in_array($prov_name, $valid_provinces)) {
        $prov_name = "อื่นๆ";
    }

    // เอาจำนวนมาบวกสะสมกัน (เช่น Kalasin 2 คน + กาฬสินธ์ุ 1 คน + 5555 1 คน = อื่นๆ 4 คน)
    if (!isset($provinces_raw[$prov_name])) {
        $provinces_raw[$prov_name] = 0;
    }
    $provinces_raw[$prov_name] += $row['cnt'];
}
$stmt2->close();

// เรียงลำดับจากจังหวัดที่คนเยอะสุดไปน้อยสุด
arsort($provinces_raw);

// ตัดมาแสดงแค่ 5 อันดับแรก (เพื่อให้กราฟไม่ยาวเกินไปเหมือนเดิม)
$provinces = array_slice($provinces_raw, 0, 5, true);

// หาค่าสูงสุดสำหรับทำความกว้างของกราฟแท่ง
$max_prov = !empty($provinces) ? max($provinces) : 1;

// 3. ช่วงอายุ (เฉพาะที่ได้รับอนุมัติในกิจกรรมนี้)
$age_ranges = ['ต่ำกว่า 18 ปี' => 0, '18-24 ปี' => 0, '25-34 ปี' => 0, '35-44 ปี' => 0, '45 ปีขึ้นไป' => 0];
$stmt3 = $conn->prepare("SELECT u.birthdate FROM users u JOIN registrations r ON u.user_id = r.user_id WHERE r.event_id = ? AND (r.status = 'approved' OR r.status = 'Approved')");
$stmt3->bind_param("i", $event_id);
$stmt3->execute();
$res3 = $stmt3->get_result();
while ($row = $res3->fetch_assoc()) {
    if (empty($row['birthdate'])) continue;
    $age = (new DateTime($row['birthdate']))->diff(new DateTime('today'))->y;
    if ($age < 18) $age_ranges['ต่ำกว่า 18 ปี']++;
    elseif ($age >= 18 && $age <= 24) $age_ranges['18-24 ปี']++;
    elseif ($age >= 25 && $age <= 34) $age_ranges['25-34 ปี']++;
    elseif ($age >= 35 && $age <= 44) $age_ranges['35-44 ปี']++;
    else $age_ranges['45 ปีขึ้นไป']++;
}
$stmt3->close();
$max_age = max($age_ranges) > 0 ? max($age_ranges) : 1;

renderView('statistics', [
    'event' => $event,
    'event_id' => $event_id,
    'genders' => $genders,
    'max_gender' => $max_gender,
    'provinces' => $provinces,
    'max_prov' => $max_prov,
    'age_ranges' => $age_ranges,
    'max_age' => $max_age
]);
?>