<?php
require_once '../Include/database.php';
require_once '../databases/Events.php';
require_once '../databases/Registrations.php';

$event_id = $_GET['event_id'] ?? 0;

if ($event_id == 0) {
    die("ไม่พบรหัสกิจกรรม");
}

$event = getEventById($event_id);
$registrations = getRegistrationsByEvent($event_id);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ระบบเช็คชื่อเข้างาน</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
        th { background-color: #f2f2f2; }
        .btn-checkin { background-color: #2ecc71; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .btn-checkin:hover { background-color: #27ae60; }
        .btn-undo { background-color: #95a5a6; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; }
        .status-badge { background-color: #dff9fb; color: #22a6b3; padding: 5px 10px; border-radius: 20px; font-size: 0.9em; }
    </style>
</head>
<body>

    <h2>📍 ระบบเช็คชื่อหน้างาน: <?php echo htmlspecialchars($event['event_name']); ?></h2>
    <a href="/templates/home.php">⬅ กลับหน้ารายการกิจกรรม</a>

    <table>
        <thead>
            <tr>
                <th>รหัสสมัคร</th>
                <th>ชื่อ-นามสกุล</th>
                <th>อีเมล</th>
                <th>สถานะเช็คชื่อ</th>
                <th>จัดการ (Check-in)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $has_approved_users = false;
            if (!empty($registrations)): 
                foreach ($registrations as $reg): 
                    if ($reg['status'] == 'Approved'): 
                        $has_approved_users = true;
            ?>
                <tr>
                    <td>#<?php echo $reg['registration_id']; ?></td>
                    <td style="text-align: left; font-weight: bold;"><?php echo htmlspecialchars($reg['name']); ?></td>
                    <td><?php echo htmlspecialchars($reg['email']); ?></td>
                    
                    <td>
                        <?php if ($reg['is_checked_in'] == 1): ?>
                            <span class="status-badge" style="background-color: #badc58; color: #2f3640;">✅ เข้าร่วมแล้ว</span>
                        <?php else: ?>
                            <span class="status-badge" style="background-color: #ffbe76; color: #2f3640;">⏳ รอเช็คอิน</span>
                        <?php endif; ?>
                    </td>
                    
                    <td>
                        <?php if ($reg['is_checked_in'] == 0): ?>
                            <a class="btn-checkin" href="/routes/Registration.php?action=checkin&id=<?php echo $reg['registration_id']; ?>&event_id=<?php echo $event_id; ?>">👉 เช็คชื่อเข้างาน</a>
                        <?php else: ?>
                            <a class="btn-undo" href="/routes/Registration.php?action=undo_checkin&id=<?php echo $reg['registration_id']; ?>&event_id=<?php echo $event_id; ?>">ยกเลิก</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php 
                    endif;
                endforeach; 
            endif; 
            
            if (!$has_approved_users):
            ?>
                <tr>
                    <td colspan="5">ยังไม่มีผู้เข้าร่วมที่ได้รับการอนุมัติในกิจกรรมนี้</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>