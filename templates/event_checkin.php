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
        body { font-family: sans-serif; background-color: #f4f6f9; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
        th { background-color: #f2f2f2; }
        .btn-checkin { background-color: #2ecc71; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;}
        .btn-checkin:hover { background-color: #27ae60; }
        .btn-undo { background-color: #95a5a6; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; display: inline-block;}
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 0.9em; font-weight: bold;}
        
        /* สไตล์กล่องตรวจ OTP */
        .otp-box {
            background-color: #fff3cd;
            border: 2px dashed #ffeeba;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>

     <div class="container">
    <h2>📍 ระบบเช็คชื่อหน้างาน: <?php echo htmlspecialchars($event['event_name']); ?></h2>
    <a href="/templates/manage_event.php" style="color: #3498db; text-decoration: none;">⬅ กลับหน้าจัดการกิจกรรม</a>

    <div class="otp-box">
        <h3 style="margin-top: 0; color: #856404;">🔍 ตรวจสอบรหัสเข้างาน (OTP)</h3>
        <p style="color: #856404; font-size: 14px; margin-bottom: 15px;">
            กรอกรหัส 6 หลักที่ผู้เข้าร่วมแสดงในหน้าตั๋ว ระบบจะทำการเช็คชื่อให้อัตโนมัติ
        </p>
        
        <form action="/routes/Registration.php" method="POST" style="display: flex; justify-content: center; gap: 10px; align-items: center;">
            <input type="hidden" name="action" value="verify_otp_frontdesk">
            
            <input type="text" name="otp_input" placeholder="เลข 6 หลัก..." maxlength="6" required 
                   style="padding: 12px 15px; font-size: 20px; width: 180px; text-align: center; border: 2px solid #ffda6a; border-radius: 5px; outline: none; letter-spacing: 3px; font-weight: bold;">
            
            <button type="submit" style="background: #28a745; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; transition: 0.2s;">
                ตรวจสอบ & เช็คอิน
            </button>
        </form>
    </div>
</div>
    

<script>

function verifyOTP(email, inputId, statusId) {
    const otp = document.getElementById(inputId).value;
    const statusText = document.getElementById(statusId);

    if(!otp) {
        statusText.innerText = "กรุณากรอกรหัสก่อน";
        statusText.style.color = "red";
        return;
    }

    const formData = new FormData();
    formData.append('email', email);
    formData.append('otp', otp);

    fetch('api/verify_otp.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        statusText.innerText = data.message;
        statusText.style.color = data.status === 'success' ? 'green' : 'red';
        
        if(data.status === 'success') {
            // ถ้ายืนยันสำเร็จ ให้ซ่อนปุ่มหรือทำเครื่องหมายว่ามาแล้ว
            document.getElementById(inputId).disabled = true;
        }
    });
}
</script>
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