<?php
// ตรวจสอบสถานะ Session ก่อนเปิด
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Include/database.php';
require_once __DIR__ . '/../databases/Events.php';
require_once __DIR__ . '/../databases/Registrations.php';

if (empty($_SESSION['user_id'])) {
    header("Location: /entrypj/sign_in");
    exit();
}

$event_id = $_GET['event_id'] ?? 0;
if ($event_id == 0) {
    die("ไม่พบรหัสกิจกรรม");
}

$event = getEventById($event_id);

// ป้องกันคนอื่นแอบเข้า
if ($event['organizer_id'] != $_SESSION['user_id']) {
    echo "<script>alert('คุณไม่มีสิทธิ์จัดการกิจกรรมนี้!'); window.location.href='/entrypj/home';</script>";
    exit();
}

$registrations = getRegistrationsByEvent($event_id);

// ดึงข้อความแจ้งเตือนจากการเช็คอินด้วย OTP
$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการผู้ลงทะเบียน</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 1100px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
        h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-top: 0; }
        .btn-back { display: inline-block; margin-bottom: 20px; text-decoration: none; color: #7f8c8d; font-weight: bold; transition: 0.2s; }
        .btn-back:hover { color: #3498db; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: center; border-bottom: 1px solid #eee; }
        th { background-color: #f8f9fa; color: #2c3e50; font-weight: bold; }
        td:nth-child(2) { text-align: left; font-weight: bold; color: #34495e; }
        tr:hover { background-color: #f1f5f9; }

        /* สีตัวอักษรสถานะ (เปลี่ยนคลาสให้รองรับ JS) */
        .text-pending { color: #f39c12; font-weight: bold; }
        .text-approved { color: #27ae60; font-weight: bold; }
        .text-rejected { color: #e74c3c; font-weight: bold; }

        .btn-action { padding: 8px 12px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; color: white; transition: 0.2s; font-size: 0.9em; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); margin: 0 2px;}
        .btn-approve { background-color: #2ecc71; }
        .btn-approve:hover { background-color: #27ae60; transform: translateY(-2px); }
        .btn-reject { background-color: #e74c3c; }
        .btn-reject:hover { background-color: #c0392b; transform: translateY(-2px); }

        .empty-state { text-align: center; padding: 40px; color: #95a5a6; }
        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; font-weight: bold; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .otp-box { background: #e8f8f5; padding: 20px; border-radius: 8px; border: 1px solid #1abc9c; text-align: center; margin-bottom: 25px; }
        .otp-input { padding: 10px; font-size: 18px; letter-spacing: 3px; text-align: center; border: 1px solid #bdc3c7; border-radius: 5px; width: 150px; outline: none; }
        .otp-input:focus { border-color: #1abc9c; }

        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); }
        .modal-content { background-color: #fff; margin: 10% auto; padding: 25px 30px; border-radius: 12px; width: 400px; box-shadow: 0 5px 20px rgba(0,0,0,0.3); position: relative; animation: fadeIn 0.3s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        .close-btn { position: absolute; top: 15px; right: 20px; color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; transition: 0.2s; }
        .close-btn:hover { color: #e74c3c; }
        .user-detail-row { margin-bottom: 12px; font-size: 1.1em; color: #34495e; border-bottom: 1px dashed #ecf0f1; padding-bottom: 8px; }
        .user-detail-label { font-weight: bold; color: #2c3e50; display: inline-block; width: 110px; }
        .user-name-link { color: #2980b9; text-decoration: none; cursor: pointer; border-bottom: 1px solid transparent; transition: 0.2s; }
        .user-name-link:hover { color: #1abc9c; border-bottom: 1px solid #1abc9c; }
    </style>
</head>

<body>

    <?php include __DIR__ . '/header.php'; ?>

    <div class="container">
        <a href="/entrypj/manage_event" class="btn-back">⬅ กลับหน้าจัดการกิจกรรม</a>
        <a href="/entrypj/statistics.php?event_id=<?php echo $event_id; ?>" style="display: inline-block; margin-bottom: 20px; margin-left: 15px; text-decoration: none; background: #9b59b6; color: white; padding: 5px 15px; border-radius: 5px; font-weight: bold;">
            📊 ดูสถิติกิจกรรมนี้
        </a>

        <h2>👥 ผู้ลงทะเบียน: <?php echo htmlspecialchars($event['event_name']); ?></h2>

        <?php if ($success_msg): ?>
            <div class="alert alert-success">✅ <?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-error">❌ <?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <div class="otp-box">
            <h3 style="margin-top: 0; color: #16a085;">📍 เช็คอินผู้เข้าร่วมงานด้วย OTP</h3>
            <p style="color: #7f8c8d; font-size: 0.9em; margin-bottom: 15px;">สอบถามรหัส OTP 6 หลักจากผู้เข้าร่วมงานที่ได้รับการอนุมัติแล้ว เพื่อทำการเช็คชื่อ</p>
            <form action="/entrypj/routes/verify_checkin.php" method="POST">
                <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                <input type="text" name="otp" class="otp-input" placeholder="กรอก OTP" maxlength="6" required autocomplete="off">
                <button type="submit" class="btn-action" style="background-color: #1abc9c; padding: 10px 20px; font-size: 16px;">ตรวจสอบและเช็คอิน</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>รหัส</th>
                    <th>ชื่อ-นามสกุล (คลิกเพื่อดูข้อมูล)</th>
                    <th>เพศ</th>
                    <th>จังหวัด</th>
                    <th>สถานะสมัคร</th>
                    <th>สถานะเช็คอิน</th>
                    <th>จัดการอนุมัติ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($registrations)): ?>
                    <?php foreach ($registrations as $reg): ?>
                        <tr>
                            <td>#<?php echo $reg['registration_id']; ?></td>
                            
                            <td>
                                <a class="user-name-link" onclick="showUserModal(
                                    '<?php echo htmlspecialchars($reg['name']); ?>', 
                                    '<?php echo htmlspecialchars($reg['email'] ?? '-'); ?>', 
                                    '<?php echo htmlspecialchars($reg['gender']); ?>', 
                                    '<?php echo htmlspecialchars($reg['province']); ?>'
                                )">
                                    <?php echo htmlspecialchars($reg['name']); ?>
                                </a>
                            </td>

                            <td><?php echo htmlspecialchars($reg['gender']); ?></td>
                            <td><?php echo htmlspecialchars($reg['province']); ?></td>

                            <?php
                            $status = empty($reg['status']) ? 'Pending' : $reg['status'];
                            $class_name = "text-" . strtolower($status);
                            ?>
                            <td id="status-<?php echo $reg['registration_id']; ?>" class="<?php echo $class_name; ?>"><?php echo $status; ?></td>

                            <td>
                                <?php if (isset($reg['is_checked_in']) && $reg['is_checked_in'] == 1): ?>
                                    <span style="color: #27ae60; font-weight: bold;">✅ เช็คอินแล้ว</span>
                                <?php else: ?>
                                    <span style="color: #bdc3c7; font-weight: bold;">⏳ ยังไม่เช็คอิน</span>
                                <?php endif; ?>
                            </td>

                            <td id="action-<?php echo $reg['registration_id']; ?>">
                                <?php if ($status != 'approved' && $status != 'Approved'): ?>
                                    <button type="button" class="btn-action btn-approve" onclick="updateRegStatus(<?php echo $reg['registration_id']; ?>, 'approved')">✅ อนุมัติ</button>
                                <?php endif; ?>

                                <?php if ($status != 'rejected' && $status != 'Rejected'): ?>
                                    <button type="button" class="btn-action btn-reject" onclick="updateRegStatus(<?php echo $reg['registration_id']; ?>, 'rejected')">❌ ปฏิเสธ</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">ยังไม่มีผู้ลงทะเบียนในกิจกรรมนี้</div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <h3 style="margin-top: 0; color: #3498db; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px;">📋 ข้อมูลผู้เข้าร่วม</h3>
            <div class="user-detail-row"><span class="user-detail-label">👤 ชื่อ-นามสกุล:</span> <span id="modalName"></span></div>
            <div class="user-detail-row"><span class="user-detail-label">📧 อีเมล:</span> <span id="modalEmail"></span></div>
            <div class="user-detail-row"><span class="user-detail-label">⚧ เพศ:</span> <span id="modalGender"></span></div>
            <div class="user-detail-row" style="border-bottom: none;"><span class="user-detail-label">📍 จังหวัด:</span> <span id="modalProvince"></span></div>
        </div>
    </div>

    <script>
        function showUserModal(name, email, gender, province) {
            document.getElementById('modalName').innerText = name;
            document.getElementById('modalEmail').innerText = email;
            document.getElementById('modalGender').innerText = gender;
            document.getElementById('modalProvince').innerText = province;
            document.getElementById('userModal').style.display = 'block';
        }

        function closeModal() { document.getElementById('userModal').style.display = 'none'; }

        window.onclick = function(event) {
            var modal = document.getElementById('userModal');
            if (event.target == modal) { modal.style.display = "none"; }
        }

        // ---------- ฟังก์ชันอัปเดตสถานะแบบสลับปุ่มไปมา ----------
        function updateRegStatus(regId, status) {
            let confirmText = status === 'approved' ? 'ยืนยันการอนุมัติผู้เข้าร่วมคนนี้?' : 'ยืนยันการปฏิเสธผู้เข้าร่วมคนนี้?';
            if (!confirm(confirmText)) return;

            let formData = new FormData();
            formData.append('registration_id', regId);
            formData.append('status', status);

            // ส่งข้อมูลไปที่ API
            fetch('/entrypj/api/update_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // 1. เปลี่ยนคำและสีในช่องสถานะ
                    let statusTd = document.getElementById('status-' + regId);
                    statusTd.innerText = status;
                    statusTd.className = 'text-' + status; // เปลี่ยนสีตามคลาส

                    // 2. สลับปุ่มอนุมัติ/ปฏิเสธ
                    let actionTd = document.getElementById('action-' + regId);
                    
                    if (status === 'approved') {
                        // ถ้าเพิ่งกด "อนุมัติ" ให้แสดงปุ่ม "ปฏิเสธ" เผื่อเปลี่ยนใจ
                        actionTd.innerHTML = `<button type="button" class="btn-action btn-reject" onclick="updateRegStatus(${regId}, 'rejected')">❌ ปฏิเสธ</button>`;
                    } else if (status === 'rejected') {
                        // ถ้าเพิ่งกด "ปฏิเสธ" ให้แสดงปุ่ม "อนุมัติ" เผื่อเปลี่ยนใจ
                        actionTd.innerHTML = `<button type="button" class="btn-action btn-approve" onclick="updateRegStatus(${regId}, 'approved')">✅ อนุมัติ</button>`;
                    }
                    
                } else {
                    alert('เกิดข้อผิดพลาด: ' + data.message);
                }
            })
            .catch(error => {
                alert('ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้');
            });
        }
    </script>

</body>
</html>