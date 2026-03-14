

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['event_name']); ?> - รายละเอียด</title>
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
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-800: #1e293b;
            --text-muted: #64748b;
        }

        body { 
            font-family: 'Kanit', sans-serif; 
            background-color: #e2e8f0; 
            margin: 0; 
            padding: 30px 20px; 
            color: var(--gray-800); 
            line-height: 1.6;
        }
        
        .container { 
            max-width: 900px; 
            margin: 0 auto; 
            background: #ffffff; 
            padding: 40px; 
            border-radius: 16px; 
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1); 
        }

        .btn-back { 
            display: inline-flex; 
            align-items: center;
            gap: 8px;
            margin-bottom: 25px; 
            text-decoration: none; 
            color: var(--text-muted); 
            font-weight: 500; 
            transition: color 0.2s, transform 0.2s; 
            background: var(--gray-50);
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid var(--gray-200);
        }
        .btn-back:hover { 
            color: var(--primary); 
            border-color: var(--primary);
            background: #ffffff;
        }

        .header-section {
            border-bottom: 2px solid var(--gray-100);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        h1 { 
            color: var(--primary); 
            margin: 0; 
            font-size: 2.2rem;
            font-weight: 600;
            line-height: 1.3;
        }

        /* ส่วนแสดงรายละเอียด (Description & Info) */
        .content-section {
            display: flex;
            flex-direction: column;
            gap: 25px;
            margin-bottom: 35px;
        }

        .description-box {
            background: var(--gray-50);
            padding: 25px;
            border-radius: 12px;
            border: 1px solid var(--gray-200);
            font-size: 1.05rem;
            color: #334155;
            white-space: pre-line; /* รองรับการเว้นบรรทัด */
        }
        .description-title {
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 10px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .info-card {
            background: #ffffff;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--gray-200);
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .info-label {
            font-weight: 500;
            color: var(--text-muted);
            font-size: 0.95rem;
        }
        .info-value {
            font-weight: 600;
            color: var(--gray-800);
            font-size: 1.1rem;
        }

        /* Action Box (ปุ่มกดเข้าร่วมและสถานะ) */
        .action-box { 
            text-align: center; 
            padding: 30px; 
            background: #f8fafc; 
            border-radius: 16px; 
            border: 2px dashed #cbd5e1; 
            margin-bottom: 40px;
        }
        
        .btn-join { 
            background-color: var(--primary); 
            color: white; 
            padding: 14px 35px; 
            border: none; 
            border-radius: 50px; 
            font-size: 1.1rem; 
            font-weight: 600; 
            font-family: 'Kanit', sans-serif;
            cursor: pointer; 
            transition: all 0.3s; 
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3);
        }
        .btn-join:hover { 
            background-color: var(--primary-hover); 
            transform: translateY(-2px); 
            box-shadow: 0 6px 10px -1px rgba(79, 70, 229, 0.4);
        }

        /* Status Badges */
        .status-badge { 
            display: inline-block; 
            padding: 12px 25px; 
            border-radius: 50px; 
            font-size: 1.1rem; 
            font-weight: 500; 
        }
        .status-approved { background-color: var(--success-bg); color: var(--success-text); border: 1px solid #a7f3d0; }
        .status-pending { background-color: var(--warning-bg); color: var(--warning-text); border: 1px solid #fde68a; }
        .status-rejected { background-color: var(--danger-bg); color: var(--danger-text); border: 1px solid #fecaca; }
        .status-ended { background-color: var(--gray-200); color: var(--text-muted); border: 1px solid #cbd5e1; }
        .status-full { background-color: var(--danger-bg); color: var(--danger-text); border: 1px solid #fecaca; }

        /* แกลลอรี่ */
        .gallery-title { 
            color: var(--gray-800); 
            margin-bottom: 20px; 
            font-size: 1.4rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .gallery-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); 
            gap: 15px; 
        }
        .gallery-item {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            background: var(--gray-100);
            aspect-ratio: 4/3;
        }
        .gallery-item img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        .gallery-item:hover img {
            transform: scale(1.05);
        }
        .no-image { 
            color: var(--text-muted); 
            background: var(--gray-50); 
            padding: 30px; 
            border-radius: 12px; 
            text-align: center; 
            border: 1px dashed var(--gray-200);
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    
    <div class="container">
        <a href="/entrypj/home" class="btn-back">⬅ กลับหน้ารายการกิจกรรม</a>
        
        <div class="header-section">
            <h1>📌 <?php echo htmlspecialchars($event['event_name']); ?></h1>
        </div>
        
        <div class="content-section">
            <div class="description-box">
                <div class="description-title">📝 รายละเอียดกิจกรรม</div>
                <?php echo nl2br(htmlspecialchars($event['description'])); ?>
            </div>

            <div class="info-grid">
                <div class="info-card">
                    <span class="info-label">📅 วันเวลาที่จัด</span>
                    <span class="info-value">
                        <?php echo date('d/m/Y H:i', strtotime($event['start_date'])); ?> <br>
                        <span style="color: var(--text-muted); font-size: 0.9em; font-weight: normal;">ถึง</span> <br>
                        <?php echo date('d/m/Y H:i', strtotime($event['end_date'])); ?>
                    </span>
                </div>
                
                <div class="info-card">
                    <span class="info-label">📍 สถานที่</span>
                    <span class="info-value"><?php echo htmlspecialchars($event['location']); ?></span>
                </div>

                <div class="info-card">
                    <span class="info-label">👥 จำนวนที่รับสมัคร</span>
                    <span class="info-value" style="<?php echo $is_full ? 'color: var(--danger);' : 'color: var(--success);'; ?>">
                        <?php echo $current_joined; ?> / <?php echo htmlspecialchars($event['max_participants']); ?> คน
                    </span>
                </div>
            </div>
        </div>

        <div class="action-box">
            <?php if (!$user_id): ?>
                <p style="color: var(--text-muted); margin-top: 0; margin-bottom: 20px; font-size: 1.1rem;">กรุณาเข้าสู่ระบบเพื่อขอเข้าร่วมกิจกรรมนี้</p>
                <a href="/entrypj/sign_in" class="btn-join" style="text-decoration: none;">🔒 เข้าสู่ระบบ</a>
                
            <?php else: ?>
                <?php if ($registration_status == 'approved'): ?>
                    <div class="status-badge status-approved">✅ คุณได้รับอนุมัติให้เข้าร่วมกิจกรรมนี้แล้ว</div>
                <?php elseif ($registration_status == 'pending'): ?>
                    <div class="status-badge status-pending">⏳ อยู่ระหว่างรอผู้จัดงานอนุมัติคำขอของคุณ</div>
                <?php elseif ($registration_status == 'rejected'): ?>
                    <div class="status-badge status-rejected">❌ ขออภัย คำขอเข้าร่วมของคุณถูกปฏิเสธ</div>
                
                <?php elseif ($is_ended): ?>
                    <div class="status-badge status-ended">⛔ กิจกรรมนี้จบลงแล้ว ไม่สามารถเข้าร่วมได้</div>
                <?php elseif ($is_full): ?>
                    <div class="status-badge status-full">🚫 ผู้เข้าร่วมเต็มแล้ว (<?php echo $current_joined; ?>/<?php echo $event['max_participants']; ?> คน)</div>
                
                <?php else: ?>
                    <form action="/entrypj/Registration" method="POST" style="margin: 0;">
                        <input type="hidden" name="action" value="request_join">
                        <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                        <button type="submit" class="btn-join" onclick="return confirm('ยืนยันการขอเข้าร่วมกิจกรรมนี้?');">
                            ➕ ขอเข้าร่วมกิจกรรม
                        </button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="gallery-title">📸 แกลลอรี่รูปภาพ <span style="font-size: 1rem; color: var(--text-muted); font-weight: normal;">(<?php echo count($images); ?> รูป)</span></div>
        <?php if (count($images) > 0): ?>
            <div class="gallery-grid">
                <?php foreach ($images as $img_path): ?>
                    <?php 
                        $rawPath = $img_path ?? '';
                        $cleanPath = str_replace('/entrypj', '', $rawPath);
                        $displayPath = !empty($cleanPath) ? '/entrypj' . $cleanPath : ''; 
                    ?>
                    <div class="gallery-item">
                        <img src="<?php echo htmlspecialchars($displayPath); ?>" alt="รูปภาพกิจกรรม">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-image">📭 กิจกรรมนี้ยังไม่มีรูปภาพเพิ่มเติม</div>
        <?php endif; ?>
    </div>
    
</body>
</html>