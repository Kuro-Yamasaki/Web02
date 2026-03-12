<?php
session_start();

require_once __DIR__ . '/../Include/database.php';
require_once __DIR__ . '/../databases/Registrations.php'; 

// ตรวจสอบการล็อกอิน
if (empty($_SESSION['user_id'])) {
    header("Location: /entrypj/templates/sign_in.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$history = getUserHistory($user_id);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ประวัติการเข้าร่วมกิจกรรม</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-top: 0; }
        .btn-back { display: inline-block; margin-bottom: 20px; text-decoration: none; color: #7f8c8d; font-weight: bold; transition: 0.2s; }
        .btn-back:hover { color: #3498db; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #f8f9fa; color: #2c3e50; font-weight: bold; }
        tr:hover { background-color: #f1f5f9; }
        
        .badge { padding: 8px 15px; border-radius: 20px; font-size: 0.9em; font-weight: bold; display: inline-block; text-align: center; min-width: 80px; }
        .badge-pending { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .badge-approved { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .badge-rejected { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .empty-state { text-align: center; padding: 50px; color: #95a5a6; font-size: 1.1em; }
        
        .btn-otp { background-color: #3498db; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-size: 0.9em; transition: 0.3s; text-decoration: none; display: inline-block;}
        .btn-otp:hover { background-color: #2980b9; }
    </style>
</head>
<body>
    
    <?php include __DIR__ . '/header.php'; ?> 

    <div class="container">
        <a href="/entrypj/templates/home.php" class="btn-back">⬅ กลับหน้ารายการกิจกรรม</a>
        <h2>📜 ประวัติการขอเข้าร่วมกิจกรรมของคุณ</h2>

        <table>
            <thead>
                <tr>
                    <th>ชื่อกิจกรรม</th>
                    <th>วันที่เริ่ม</th>
                    <th>สถานที่</th>
                    <th>สถานะ</th>
                    <th>เช็คอินหน้างาน</th> 
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($history)): ?>
                    <?php foreach ($history as $row): ?>
                    <tr>
                        <td style="font-weight: bold; color: #34495e;"><?php echo htmlspecialchars($row['event_name']); ?></td>
                        <td><?php echo date('d M Y, H:i', strtotime($row['start_date'])); ?></td>
                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                        <?php 
                            $status = empty($row['status']) ? 'pending' : strtolower($row['status']); 
                            
                            // เช็คว่าถึงเวลาเริ่มกิจกรรมหรือยัง
                            $current_time = time();
                            $start_time = strtotime($row['start_date']);
                        ?>
                        <td>
                            <span class="badge badge-<?php echo $status; ?>">
                                <?php 
                                    if ($status == 'approved') echo '✅ อนุมัติแล้ว';
                                    elseif ($status == 'rejected') echo '❌ ปฏิเสธ';
                                    else echo '⏳ รออนุมัติ';
                                ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($status == 'approved'): ?>
                                <?php if ($current_time >= $start_time): ?>
                                    <a href="/entrypj/templates/user_checkin_otp.php?event_id=<?php echo $row['event_id']; ?>" class="btn-otp">📍 ไปหน้าเช็คอิน (OTP)</a>
                                <?php else: ?>
                                    <span style="color: #f39c12; font-size: 0.9em; font-weight: bold;">⏳ รอกิจกรรมเริ่ม</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: #95a5a6; font-size: 0.9em;">ยังไม่สามารถเช็คอินได้</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                📭 คุณยังไม่มีประวัติการลงทะเบียนกิจกรรม
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>