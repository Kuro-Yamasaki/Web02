<?php
declare(strict_types=1);
session_start();
date_default_timezone_set('Asia/Bangkok');


// กำหนด Path หลัก (สมมติว่า index.php อยู่ในโฟลเดอร์ entrypj อยู่แล้ว)
const INCLUDES_DIR = __DIR__ . '/Include';
const ROUTE_DIR = __DIR__ . '/routes';
const TEMPLATES_DIR = __DIR__ . '/templates';
const DATABASES_DIR = __DIR__ . '/databases';


require_once INCLUDES_DIR . '/router.php';
require_once INCLUDES_DIR . '/view.php';
require_once INCLUDES_DIR . '/database.php';

// ทดสอบการเชื่อมต่อฐานข้อมูลตั้งแต่เข้าหน้าแรก
getConnection();

// เริ่มระบบ Router
dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
?>