<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ข้อมูลบัญชี</title>
</head>

<body style="font-family: sans-serif; background-color: #f4f6f9; padding: 20px; margin: 0;">

    <?php include __DIR__ . '/header.php'; ?>

    <div style="max-width: 500px; margin: 40px auto; background-color: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee;">
        <h2 style="margin-top: 0; color: #2c3e50; text-align: center;">👤 ข้อมูลบัญชีของคุณ</h2>
        <hr style="border: 0; border-top: 2px solid #3498db; margin-bottom: 20px; width: 50px; margin-left: auto; margin-right: auto;">

        <div style="font-size: 16px; line-height: 2; color: #34495e; padding: 10px 20px; background: #f8f9fa; border-radius: 8px;">
            <div style="margin-bottom: 10px; border-bottom: 1px dashed #ccc; padding-bottom: 10px;">
                <b>รหัสประจำตัว:</b> <span style="color: #e74c3c; font-weight: bold; font-size: 18px; float: right;">#<?php echo $user_id; ?></span>
            </div>
            <div><b>ชื่อผู้ใช้:</b> <span style="float: right;"><?php echo htmlspecialchars($user_name); ?></span></div>
            <div><b>อีเมล:</b> <span style="float: right; color: #7f8c8d;"><?php echo htmlspecialchars($email); ?></span></div>
            <div><b>เพศ:</b> <span style="float: right;"><?php echo $gender_display; ?></span></div>
            <div><b>อายุ:</b> <span style="float: right;"><?php echo $age; ?> ปี</span></div>
            <div><b>จังหวัด:</b> <span style="float: right;"><?php echo htmlspecialchars($province); ?></span></div>
        </div>

        <hr style="border: 0; border-top: 1px solid #eee; margin-top: 25px; margin-bottom: 20px;">

        <div style="text-align: center;">
            <a href="/entrypj/routes/User.php?action=logout" style="text-decoration: none; background: #e74c3c; color: white; padding: 10px 25px; border-radius: 5px; margin: 5px; display: inline-block; font-weight: bold; transition: background 0.3s;" onmouseover="this.style.background='#c0392b'" onmouseout="this.style.background='#e74c3c'" onclick="return confirm('ต้องการออกจากระบบใช่หรือไม่?');">
                🚪 ออกจากระบบ
            </a>
        </div>
    </div>

</body>

</html>