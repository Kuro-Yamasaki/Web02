<?php
// ตรวจสอบสถานะ Session ก่อนเปิด
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Include/database.php';
require_once __DIR__ . '/../databases/Registrations.php'; 

// ตรวจสอบการล็อกอิน
if (empty($_SESSION['user_id'])) {
    header("Location: /entrypj/sign_in");
    exit();
}

$user_id = $_SESSION['user_id'];
$history = getUserHistory($user_id);

// ดึงอีเมลของผู้ใช้เพื่อใช้แสดงในหน้าต่างขอ OTP
$conn = getConnection(); 
$stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$user_email = $userData ? $userData['email'] : '';
$stmt->close();
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
        
        /* ปุ่มเปิด Modal เช็คอิน */
        .btn-otp { background-color: #3498db; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-size: 0.9em; font-weight: bold; transition: 0.3s; }
        .btn-otp:hover { background-color: #2980b9; transform: translateY(-1px); }

        /* สไตล์ Modal */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); }
        .modal-content { background-color: #fff; margin: 10% auto; padding: 40px; border-radius: 12px; width: 400px; box-shadow: 0 5px 20px rgba(0,0,0,0.3); position: relative; text-align: center; animation: fadeIn 0.3s; }
        .close-btn { position: absolute; top: 15px; right: 20px; color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close-btn:hover { color: #e74c3c; }

        .btn-request { background-color: #f39c12; color: white; border: none; padding: 12px; border-radius: 5px; cursor: pointer; font-size: 1em; font-weight: bold; margin-bottom: 10px; width: 100%; transition: 0.3s; }
        .btn-request:hover { background-color: #e67e22; }
        .btn-request:disabled { background-color: #bdc3c7; cursor: not-allowed; }
        
        .message-box { font-size: 0.9em; font-weight: bold; min-height: 20px; margin-bottom: 15px; }

        /* กล่องโชว์รหัส OTP ขนาดใหญ่ */
        .otp-display-box {
            display: none; 
            margin-top: 20px;
        }
        .otp-code-text {
            font-size: 3em; 
            font-weight: bold; 
            color: #e74c3c; 
            letter-spacing: 10px; 
            background: #fdf2e9; 
            padding: 20px; 
            border-radius: 10px; 
            border: 2px dashed #e67e22; 
            margin-bottom: 15px;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
    
    <?php include __DIR__ . '/header.php'; ?> 

    <div class="container">
        <a href="/entrypj/home" class="btn-back">⬅ กลับหน้ารายการกิจกรรม</a>
        <h2>📜 ประวัติการขอเข้าร่วมกิจกรรมของคุณ</h2>

        <table>
            <thead>
                <tr>
                    <th>ชื่อกิจกรรม</th>
                    <th>วันที่เริ่ม</th>
                    <th>สถานที่</th>
                    <th>สถานะสมัคร</th>
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
                                <?php if ($row['is_checked_in'] == 1): ?>
                                    <span style="color: #27ae60; font-weight: bold;">✅ เช็คอินแล้ว</span>
                                <?php elseif ($current_time >= $start_time): ?>
                                    <button class="btn-otp" onclick="openOtpModal('<?php echo htmlspecialchars($row['event_name']); ?>')">
                                        📍 รับรหัสเช็คอิน (OTP)
                                    </button>
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
                            <div class="empty-state">📭 คุณยังไม่มีประวัติการลงทะเบียนกิจกรรม</div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div id="otpModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeOtpModal()">&times;</span>
            <h2 id="modalEventTitle" style="color: #2c3e50; margin-top: 0; font-size: 1.3em;">📍 เช็คอินเข้าหน้างาน</h2>
            <p style="color: #7f8c8d; font-size: 0.9em; margin-bottom: 20px;">
                กดปุ่มด้านล่างเพื่อสร้างรหัสผ่าน แล้วนำไปแสดงให้ <b>ผู้จัดงาน</b> เพื่อเช็คชื่อเข้างาน<br>
                (รหัสจะถูกส่งสำรองไปที่: <b><?php echo htmlspecialchars($user_email); ?></b> ด้วย)
            </p>
            
            <button id="btnRequest" class="btn-request" onclick="requestOTP('<?php echo htmlspecialchars($user_email); ?>')">
                📨 กดเพื่อขอรหัส OTP
            </button>
            <div id="requestMsg" class="message-box"></div>

            <div id="otpDisplayBox" class="otp-display-box">
                <hr style="border: 0; height: 1px; background: #eee; margin-bottom: 20px;">
                <h3 style="color: #2c3e50; margin-bottom: 10px;">รหัสสำหรับเช็คอินของคุณคือ</h3>
                <div id="showOtpCode" class="otp-code-text">
                    ------
                </div>
                <p style="color: #e67e22; font-size: 0.95em; font-weight: bold;">⚠️ โปรดนำรหัสนี้ไปแสดงต่อผู้จัดงาน ⚠️<br>(รหัสมีอายุการใช้งาน 5 นาที)</p>
            </div>
        </div>
    </div>

    <script>
    // ฟังก์ชันเปิด/ปิด Modal
    function openOtpModal(eventName) {
        document.getElementById('modalEventTitle').innerText = "📍 เช็คอิน: " + eventName;
        document.getElementById('otpModal').style.display = 'block';
        
        // รีเซ็ตค่าให้กลับมาเป็นสถานะเริ่มต้น (เผื่อกดปิดแล้วเปิดใหม่)
        document.getElementById('requestMsg').innerText = '';
        document.getElementById('btnRequest').style.display = 'block';
        document.getElementById('btnRequest').disabled = false;
        document.getElementById('btnRequest').innerText = "📨 กดเพื่อขอรหัส OTP";
        
        document.getElementById('otpDisplayBox').style.display = 'none';
        document.getElementById('showOtpCode').innerText = "------";
    }

    function closeOtpModal() {
        document.getElementById('otpModal').style.display = 'none';
    }

    // ฟังก์ชันขอ OTP
    function requestOTP(email) {
        if (!email) { alert('ไม่พบข้อมูลอีเมล'); return; }

        const btn = document.getElementById('btnRequest');
        const msg = document.getElementById('requestMsg');
        
        btn.disabled = true;
        btn.innerText = "กำลังประมวลผล...";
        msg.style.color = "orange";
        msg.innerText = "กำลังสร้างรหัสและส่งอีเมล...";

        const formData = new FormData();
        formData.append('email', email);

        fetch('/entrypj/api/send_otp.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                msg.style.color = "green";
                msg.innerText = "สร้างรหัส OTP และส่งเข้าอีเมลสำเร็จแล้ว!";
                
                // ซ่อนปุ่มกดขอรหัส
                btn.style.display = "none"; 
                
                // โชว์กล่องเลข OTP 
                document.getElementById('otpDisplayBox').style.display = 'block';
                // นำตัวเลขจาก API มาแสดงหน้าจอ
                document.getElementById('showOtpCode').innerText = data.otp || "ไม่มีข้อมูล"; 

            } else {
                msg.style.color = "red";
                msg.innerText = data.message;
                btn.disabled = false;
                btn.innerText = "📨 กดเพื่อขอรหัส OTP";
            }
        })
        .catch(error => {
            msg.style.color = "red";
            msg.innerText = "เกิดข้อผิดพลาดในการเชื่อมต่อกับระบบ";
            btn.disabled = false;
            btn.innerText = "📨 กดเพื่อขอรหัส OTP";
        });
    }

    // ปิด Modal เมื่อคลิกด้านนอกกล่องสีขาว
    window.onclick = function(event) {
        if (event.target == document.getElementById('otpModal')) {
            closeOtpModal();
        }
    }
    </script>
</body>
</html>