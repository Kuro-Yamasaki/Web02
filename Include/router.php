<?php

declare(strict_types=1);

const ALLOW_METHODS = ['GET', 'POST'];
const INDEX_URI = '';
const INDEX_ROUNTE = 'home';

function normalizeUri(string $uri): string
{
    // 1. ตัดพารามิเตอร์ด้านหลังออก (เช่น ?event_id=1) 
    $parsedUri = parse_url($uri, PHP_URL_PATH);
    
    // 2. จัดรูปแบบข้อความ
    $cleanUri = strtolower(trim($parsedUri, '/'));

    // 3. ป้องกันการส่ง .php ติดมาด้วย
    $cleanUri = str_replace('.php', '', $cleanUri);

    // 4. *** เพิ่มใหม่ *** ตัดคำว่า entrypj ออกจาก Path เพื่อให้เหลือแค่ชื่อไฟล์
    if (strpos($cleanUri, 'entrypj/') === 0) {
        $cleanUri = substr($cleanUri, strlen('entrypj/'));
    }

    // ถ้าเข้าหน้าหลัก (ค่าว่าง หรือเหลือแค่ entrypj) ให้เด้งไปที่หน้า home
    if ($cleanUri === '' || $cleanUri === 'entrypj') {
        return INDEX_ROUNTE;
    }

    return $cleanUri;
}

function notFound()
{
    http_response_code(404);
    renderView('404');
    exit;
}

function getFilePath(string $uri): string
{
    $normalized = normalizeUri($uri);

    // 1. ลองค้นหาในโฟลเดอร์ routes/ ก่อน (เป็น Controller)
    $routePath = ROUTE_DIR . '/' . $normalized . '.php';
    if (file_exists($routePath)) {
        return $routePath;
    }

    // 2. *** เพิ่มใหม่ *** ถ้าไม่มีใน routes/ ให้ลองค้นหาในโฟลเดอร์ templates/ (หน้าเว็บที่เรียกตรงๆ)
    $templatePath = TEMPLATES_DIR . '/' . $normalized . '.php';
    if (file_exists($templatePath)) {
        return $templatePath;
    }

    // 3. ถ้าหาไม่เจอทั้งสองที่ ให้คืนค่าเป็นสตริงว่าง
    return '';
}

function dispatch(string $uri, string $method): void
{
    if (!in_array(strtoupper($method), ALLOW_METHODS)) {
        notFound();
    }

    $filePath = getFilePath($uri);
    
    // เช็คว่าหาไฟล์เจอหรือไม่
    if ($filePath !== '' && file_exists($filePath)) {
        include($filePath);
        return;
    } else {
        // ถ้าหาไม่เจอ ให้โยนไปหน้า 404
        notFound();
    }
}
?>