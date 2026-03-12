<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../Include/database.php';
require_once __DIR__ . '/../databases/Events.php';

if (empty($_SESSION['user_id'])) {
    header("Location: /entrypj/templates/sign_in.php");
    exit();
}

// 1. รับค่าการค้นหา และ ค่าตัวกรอง Tab (ค่าเริ่มต้นคือ available)
$search_name = $_GET['search_name'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$current_filter = $_GET['filter'] ?? 'available'; 

// 2. ดึงกิจกรรมทั้งหมดตามการค้นหามาก่อน
$all_events = searchEventsForHome($_SESSION['user_id'], $search_name, $start_date, $end_date);

// 3. เตรียม Array สำหรับเก็บกิจกรรมที่ถูกคัดกรองแล้ว
$filtered_events = [];
global $conn;

if (!empty($all_events)) {
    foreach ($all_events as $event) {
        $current_event_id = $event['event_id'];
        
        // เช็คสถานะผู้ใช้งาน
        $registration_status = null;
        $stmt = $conn->prepare("SELECT status FROM registrations WHERE user_id = ? AND event_id = ?");
        $stmt->bind_param("ii", $_SESSION['user_id'], $current_event_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $registration_status = strtolower($row['status']);
        }
        $stmt->close();

        // นับจำนวนคนที่เข้าร่วม (เฉพาะ Approved)
        $current_joined = 0;
        $stmt_count = $conn->prepare("SELECT COUNT(*) AS total_joined FROM registrations WHERE event_id = ? AND (status = 'approved' OR status = 'Approved')");
        $stmt_count->bind_param("i", $current_event_id);
        $stmt_count->execute();
        $res_count = $stmt_count->get_result();
        if ($row_count = $res_count->fetch_assoc()) {
            $current_joined = $row_count['total_joined'];
        }
        $stmt_count->close();

        // เช็คเงื่อนไขคนเต็ม และ เวลาจบกิจกรรม
        $is_full = ($event['max_participants'] > 0 && $current_joined >= $event['max_participants']);
        $is_ended = (time() > strtotime($event['end_date']));

        // --- ระบบจัดหมวดหมู่ (หัวใจสำคัญของการแบ่งหน้า) ---
        $event_category = 'available'; // ค่าเริ่มต้นคือ เข้าร่วมได้

        if ($registration_status == 'approved' || $registration_status == 'pending') {
            // ถ้ารออนุมัติ หรือ อนุมัติแล้ว ให้อยู่ในหมวด "เข้าร่วมแล้ว" (แม้กิจกรรมจะจบก็ยังแสดงให้ดูประวัติ)
            $event_category = 'joined';
        } elseif ($registration_status == 'rejected' || $is_ended || $is_full) {
            // ถ้าถูกปฏิเสธ หรือ กิจกรรมจบแล้ว หรือ เต็มแล้ว ให้อยู่ในหมวด "ไม่สามารถเข้าร่วมได้"
            $event_category = 'unavailable';
        }

        // คัดเฉพาะกิจกรรมที่ตรงกับ Tab ที่กำลังกดดูอยู่เท่านั้น ไปแสดงผล
        if ($current_filter == $event_category) {
            $event['registration_status'] = $registration_status;
            $event['current_joined'] = $current_joined;
            $event['is_full'] = $is_full;
            $event['is_ended'] = $is_ended;
            $event['cover_image'] = getEventCoverImage($current_event_id); // โหลดรูปปกมาเผื่อไว้เลย
            
            $filtered_events[] = $event;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการกิจกรรมทั้งหมด</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --secondary: #64748b;
            --success: #10b981;
            --success-bg: #d1fae5;
            --success-text: #065f46;
            --warning: #f59e0b;
            --warning-bg: #fef3c7;
            --warning-text: #92400e;
            --danger: #ef4444;
            --danger-bg: #fee2e2;
            --danger-text: #991b1b;
            --gray-100: #f8fafc;
            --gray-200: #e2e8f0;
            --gray-800: #1e293b;
            --text-muted: #64748b;
        }

        body { 
            font-family: 'Kanit', sans-serif; 
            background-color: #f1f5f9; 
            margin: 0; 
            padding: 20px; 
            color: var(--gray-800);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-title {
            text-align: center;
            font-size: 2rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 30px;
        }

        /* Tabs Styling */
        .tabs-container { 
            display: flex; 
            justify-content: center; 
            gap: 15px; 
            margin-bottom: 30px; 
            flex-wrap: wrap; 
        }
        .btn-tab { 
            padding: 10px 24px; 
            background-color: white; 
            color: var(--secondary); 
            text-decoration: none; 
            border-radius: 50px; 
            font-weight: 500; 
            border: 1px solid var(--gray-200);
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.3s ease; 
        }
        .btn-tab:hover { 
            border-color: var(--primary); 
            color: var(--primary); 
        }
        .btn-tab.active { 
            background-color: var(--primary); 
            color: white; 
            border-color: var(--primary); 
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3); 
        }

        /* Search Container */
        .search-container { 
            background-color: white; 
            padding: 20px; 
            border-radius: 12px; 
            margin-bottom: 30px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); 
        }
        .search-form {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .form-group label {
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 500;
        }
        .search-container input { 
            padding: 10px 15px; 
            border: 1px solid var(--gray-200); 
            border-radius: 8px; 
            font-family: 'Kanit', sans-serif;
            outline: none;
            transition: border-color 0.2s;
        }
        .search-container input:focus {
            border-color: var(--primary);
        }
        .btn-search { 
            background-color: var(--primary); 
            color: white; 
            border: none; 
            padding: 11px 20px; 
            border-radius: 8px; 
            cursor: pointer; 
            font-family: 'Kanit', sans-serif;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        .btn-search:hover { background-color: var(--primary-hover); }
        .btn-clear { 
            background-color: var(--gray-200); 
            color: var(--gray-800); 
            text-decoration: none; 
            padding: 11px 20px; 
            border-radius: 8px; 
            font-weight: 500;
            transition: background-color 0.2s;
        }
        .btn-clear:hover { background-color: #cbd5e1; }

        /* Grid & Cards */
        .event-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
            gap: 25px; 
        }
        .event-card { 
            background: white; 
            border-radius: 16px; 
            overflow: hidden; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); 
            transition: transform 0.3s ease, box-shadow 0.3s ease; 
            display: flex; 
            flex-direction: column; 
            border: 1px solid rgba(0,0,0,0.05);
        }
        .event-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); 
        }
        .event-img-wrapper {
            position: relative;
            width: 100%;
            height: 200px;
        }
        .event-img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            background-color: var(--gray-100); 
        }
        
        .event-info { 
            padding: 20px; 
            flex-grow: 1; 
        }
        .event-title { 
            font-size: 1.25rem; 
            font-weight: 600; 
            color: var(--gray-800); 
            margin: 0 0 15px 0; 
            line-height: 1.4;
        }
        .event-detail { 
            font-size: 0.95rem; 
            color: var(--text-muted); 
            margin-bottom: 10px; 
            display: flex; 
            align-items: flex-start; 
        }
        .event-detail strong { 
            width: 90px; 
            flex-shrink: 0;
            color: var(--gray-800); 
            font-weight: 500;
        }
        
        /* Actions & Status */
        .card-footer {
            padding: 15px 20px;
            background-color: var(--gray-100);
            border-top: 1px solid var(--gray-200);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .btn-view {
            display: block; 
            text-align: center; 
            padding: 10px; 
            background: white; 
            color: var(--primary); 
            border: 1px solid var(--primary);
            text-decoration: none; 
            border-radius: 8px; 
            font-size: 0.95rem; 
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-view:hover {
            background: var(--primary);
            color: white;
        }
        .btn-join { 
            width: 100%; 
            background-color: var(--primary); 
            color: white; 
            border: none; 
            padding: 12px; 
            font-size: 1rem; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: 500; 
            font-family: 'Kanit', sans-serif;
            transition: background-color 0.2s; 
        }
        .btn-join:hover { background-color: var(--primary-hover); }

        /* Status Badges */
        .status-badge {
            text-align: center; 
            padding: 10px; 
            border-radius: 8px; 
            font-weight: 500;
            font-size: 0.95rem;
        }
        .status-approved { background-color: var(--success-bg); color: var(--success-text); }
        .status-pending { background-color: var(--warning-bg); color: var(--warning-text); }
        .status-rejected { background-color: var(--danger-bg); color: var(--danger-text); }
        .status-ended { background-color: var(--gray-200); color: var(--text-muted); }
        .status-full { background-color: var(--danger-bg); color: var(--danger-text); }

        .no-events { 
            text-align: center; 
            padding: 60px 20px; 
            background: white; 
            border-radius: 12px; 
            color: var(--text-muted); 
            font-size: 1.2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
    </style>
</head>
<body>

    <div class="container">
        <?php include 'header.php' ?>

        <h2 class="page-title">📅 รายการกิจกรรมที่น่าสนใจ</h2>

        <div class="tabs-container">
            <a href="?filter=available&search_name=<?php echo urlencode($search_name); ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
               class="btn-tab <?php echo $current_filter == 'available' ? 'active' : ''; ?>">🎯 กิจกรรมที่เปิดรับสมัคร</a>
            
            <a href="?filter=joined&search_name=<?php echo urlencode($search_name); ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
               class="btn-tab <?php echo $current_filter == 'joined' ? 'active' : ''; ?>">✅ เข้าร่วมแล้ว / รออนุมัติ</a>
            
            <a href="?filter=unavailable&search_name=<?php echo urlencode($search_name); ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
               class="btn-tab <?php echo $current_filter == 'unavailable' ? 'active' : ''; ?>">⛔ ปฏิเสธ / จบแล้ว / เต็ม</a>
        </div>

        <div class="search-container">
            <form method="GET" action="" class="search-form">
                <input type="hidden" name="filter" value="<?php echo htmlspecialchars($current_filter); ?>">

                <div class="form-group">
                    <label>ชื่อกิจกรรม</label>
                    <input type="text" name="search_name" value="<?php echo htmlspecialchars($search_name); ?>" placeholder="ค้นหาชื่อกิจกรรม...">
                </div>
                
                <div class="form-group">
                    <label>ตั้งแต่วันที่</label>
                    <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                </div>

                <div class="form-group">
                    <label>ถึงวันที่</label>
                    <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                </div>

                <div class="form-group" style="flex-direction: row; gap: 10px;">
                    <button type="submit" class="btn-search">🔍 ค้นหา</button>
                    <a href="/entrypj/templates/home.php?filter=<?php echo $current_filter; ?>" class="btn-clear">ล้างค่า</a>
                </div>
            </form>
        </div>

        <?php if (!empty($filtered_events)): ?>
            <div class="event-grid">
                <?php foreach ($filtered_events as $event): ?>
                    <div class="event-card">
                        <div class="event-img-wrapper">
                            <img src="<?php echo htmlspecialchars($event['cover_image']); ?>" alt="รูปกิจกรรม" class="event-img">
                        </div>

                        <div class="event-info">
                            <h3 class="event-title"><?php echo htmlspecialchars($event['event_name']); ?></h3>
                            <div class="event-detail"><strong>ผู้จัดงาน:</strong> <span><?php echo htmlspecialchars($event['organizer_name']); ?></span></div>
                            <div class="event-detail"><strong>วันที่:</strong> <span><?php echo date('d/m/Y H:i', strtotime($event['start_date'])); ?></span></div>
                            <div class="event-detail"><strong>สถานที่:</strong> <span><?php echo htmlspecialchars($event['location']); ?></span></div>
                            <div class="event-detail">
                                <strong>รับสมัคร:</strong> 
                                <span style="<?php echo $event['is_full'] ? 'color: var(--danger);' : 'color: var(--success);'; ?> font-weight: 500;">
                                    <?php echo $event['current_joined']; ?>/<?php echo $event['max_participants']; ?> คน
                                </span>
                            </div>
                        </div>
                        
                        <div class="card-footer">
                            <a href="/entrypj/templates/event_detail.php?id=<?php echo $event['event_id']; ?>" class="btn-view">
                                🔍 ดูรายละเอียด
                            </a>

                            <?php if ($event['registration_status'] == 'approved'): ?>
                                <div class="status-badge status-approved">✅ เข้าร่วมแล้ว</div>
                            <?php elseif ($event['registration_status'] == 'pending'): ?>
                                <div class="status-badge status-pending">⏳ รออนุมัติ</div>
                            <?php elseif ($event['registration_status'] == 'rejected'): ?>
                                <div class="status-badge status-rejected">❌ ถูกปฏิเสธ</div>
                            <?php elseif ($event['is_ended']): ?>
                                <div class="status-badge status-ended">⛔ กิจกรรมจบลงแล้ว</div>
                            <?php elseif ($event['is_full']): ?>
                                <div class="status-badge status-full">🚫 ผู้เข้าร่วมเต็มแล้ว</div>
                            <?php else: ?>
                                <form action="/entrypj/routes/Registration.php" method="POST" style="margin: 0;">
                                    <input type="hidden" name="action" value="request_join">
                                    <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                    <button type="submit" class="btn-join" onclick="return confirm('ต้องการขอเข้าร่วมกิจกรรมนี้ใช่หรือไม่?');">➕ ขอเข้าร่วมกิจกรรม</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-events">
                📭 ยังไม่มีกิจกรรมในหมวดหมู่นี้ หรือไม่พบกิจกรรมที่ค้นหา
            </div>
        <?php endif; ?>
    </div>

</body>
</html>