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
        .otp-box { background-color: #fff3cd; border: 2px dashed #ffeeba; padding: 20px; border-radius: 8px; text-align: center; margin: 20px 0; }
        
        /* เพิ่ม Animation ให้ตารางตอนเปลี่ยนสี */
        tr { transition: background-color 0.8s ease; }
    </style>
</head>
<body>

<div class="container">
    <h2>📍 ระบบเช็คชื่อหน้างาน: <?php echo htmlspecialchars($event['event_name']); ?></h2>
    <a href="/templates/manage_event.php" style="color: #3498db; text-decoration: none;">⬅ กลับหน้าจัดการกิจกรรม</a>

    <div class="otp-box">
        <h3 style="margin-top: 0; color: #856404;">🔍 ตรวจสอบรหัสเข้างาน (OTP)</h3>
        <p style="color: #856404; font-size: 14px; margin-bottom: 15px;">
            กรอกรหัส 6 หลักที่ผู้เข้าร่วมแสดง ระบบจะหาชื่อและเช็คชื่อให้อัตโนมัติ
        </p>
        
        <form onsubmit="event.preventDefault(); verifyGlobalOTP(<?php echo $event_id; ?>);" style="display: flex; justify-content: center; gap: 10px; align-items: center;">
            <input type="text" id="global_otp" placeholder="เลข 6 หลัก..." maxlength="6" required autocomplete="off"
                   style="padding: 12px 15px; font-size: 20px; width: 180px; text-align: center; border: 2px solid #ffda6a; border-radius: 5px; outline: none; letter-spacing: 3px; font-weight: bold;">
            
            <button type="submit" style="background: #28a745; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; transition: 0.2s;">
                ตรวจสอบ & เช็คอิน
            </button>
        </form>

        <div id="global_status" style="margin-top: 15px; font-size: 16px; font-weight: bold;"></div>
    </div>

    <script>
function verifyGlobalOTP(eventId) {
    const otpInput = document.getElementById('global_otp');
    const statusText = document.getElementById('global_status');
    const otp = otpInput.value;

    if(otp.length !== 6) {
        statusText.innerHTML = "❌ <span style='color:red;'>กรุณากรอกรหัส 6 หลักให้ครบถ้วน</span>";
        return;
    }

    statusText.innerHTML = "⏳ <span style='color:#856404;'>กำลังค้นหาข้อมูลและเช็คอิน...</span>";

    const formData = new FormData();
    formData.append('otp', otp);
    formData.append('event_id', eventId);

    // ยิงไปที่ API
    fetch('/api/verify_otp.php', { method: 'POST', body: formData })
    .then(res => res.text()) // 💡 รับค่ากลับมาเป็น Text ธรรมดาก่อน เพื่อดักจับ Error PHP
    .then(text => {
        console.log("ข้อความที่ได้จากเซิร์ฟเวอร์:", text); // พิมพ์ลง Console เผื่อใช้ดูบัค
        
        try {
            // ลองแปลงเป็น JSON
            const data = JSON.parse(text);
            
            if(data.status === 'success') {
                // โชว์ชื่อ และข้อความสีเขียว
                statusText.innerHTML = `✅ <span style='color:green;'>${data.message}</span> <br><span style='color: #0056b3; font-size: 22px; margin-top:10px; display:block;'>ผู้เข้าร่วม: <u>${data.user_name}</u></span>`;
                otpInput.value = ""; 
                
                // 💡 สั่งให้รีเฟรชหน้าเว็บหลังจากผ่านไป 1.5 วินาที เพื่อให้ข้อมูลในตารางชัวร์ที่สุด
                setTimeout(() => {
                    window.location.reload();
                }, 1500);

            } else {
                statusText.innerHTML = `❌ <span style='color:red;'>${data.message}</span>`;
            }
        } catch(e) {
            // ถ้าแปลง JSON ไม่ได้ แปลว่า PHP น่าจะมี Error
            statusText.innerHTML = `❌ <span style='color:red;'>ระบบตอบกลับผิดพลาด! (กด F12 ดูแถบ Console)</span>`;
            console.error("เซิร์ฟเวอร์ส่งค่ามาผิดปกติ:", text);
        }
    })
    .catch(err => {
        statusText.innerHTML = "❌ <span style='color:red;'>เกิดข้อผิดพลาดในการเชื่อมต่อ (Network Error)</span>";
        console.error(err);
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
                <th>จัดการ (Manual)</th>
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
                <tr id="row_<?php echo $reg['registration_id']; ?>">
                    <td>#<?php echo $reg['registration_id']; ?></td>
                    <td style="text-align: left; font-weight: bold;"><?php echo htmlspecialchars($reg['name']); ?></td>
                    <td><?php echo htmlspecialchars($reg['email']); ?></td>
                    
                    <td id="status_<?php echo $reg['registration_id']; ?>">
                        <?php if ($reg['is_checked_in'] == 1): ?>
                            <span class="status-badge" style="background-color: #badc58; color: #2f3640;">✅ เข้าร่วมแล้ว</span>
                        <?php else: ?>
                            <span class="status-badge" style="background-color: #ffbe76; color: #2f3640;">⏳ รอเช็คอิน</span>
                        <?php endif; ?>
                    </td>
                    
                    <td id="action_<?php echo $reg['registration_id']; ?>">
                        <?php if ($reg['is_checked_in'] == 0): ?>
                            <a class="btn-checkin" href="/routes/Registration.php?action=checkin&id=<?php echo $reg['registration_id']; ?>&event_id=<?php echo $event_id; ?>">👉 เช็คชื่อ (ข้าม OTP)</a>
                        <?php else: ?>
                            <a class="btn-undo" href="/routes/Registration.php?action=undo_checkin&id=<?php echo $reg['registration_id']; ?>&event_id=<?php echo $event_id; ?>">ยกเลิกการเช็คอิน</a>
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

</div>

</body>
</html>