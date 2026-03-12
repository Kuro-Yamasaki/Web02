<?php
declare(strict_types=1);
session_start();
date_default_timezone_set('Asia/Bangkok');
// เปิดโหมดแสดง Error ทุกชนิดบนหน้าจอ
ini_set('display_errors', '1');
error_reporting(E_ALL);

// กำหนด Path หลัก (สมมติว่า index.php อยู่ในโฟลเดอร์ entrypj อยู่แล้ว)
const INCLUDES_DIR = __DIR__ . '/Include';
const ROUTE_DIR = __DIR__ . '/routes';
const TEMPLATES_DIR = __DIR__ . '/templates';
const DATABASES_DIR = __DIR__ . '/databases';

// เช็คก่อนว่ามีไฟล์เหล่านี้อยู่จริงไหม ป้องกัน Error 500
if (!file_exists(INCLUDES_DIR . '/router.php')) die("❌ หาไฟล์ <b>router.php</b> ไม่เจอ! ตรวจสอบว่าอัปโหลดเข้าโฟลเดอร์ Include หรือยัง");
if (!file_exists(INCLUDES_DIR . '/view.php')) die("❌ หาไฟล์ <b>view.php</b> ไม่เจอ!");
if (!file_exists(INCLUDES_DIR . '/database.php')) die("❌ หาไฟล์ <b>database.php</b> ไม่เจอ!");

require_once INCLUDES_DIR . '/router.php';
require_once INCLUDES_DIR . '/view.php';
require_once INCLUDES_DIR . '/database.php';

// ทดสอบการเชื่อมต่อฐานข้อมูลตั้งแต่เข้าหน้าแรก
getConnection();

// เริ่มระบบ Router
dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
?>