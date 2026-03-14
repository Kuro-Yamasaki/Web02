
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สถิติกิจกรรม</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 900px; margin: 0 auto; }
        .card { background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .stat-row { margin-bottom: 15px; }
        .stat-label { display: flex; justify-content: space-between; font-weight: bold; margin-bottom: 5px; color: #555; }
        .bar-bg { width: 100%; background-color: #ecf0f1; border-radius: 5px; height: 24px; overflow: hidden; }
        .bar-fill { height: 100%; display: flex; align-items: center; justify-content: flex-end; padding-right: 10px; color: white; font-size: 0.85em; font-weight: bold; width: 0; animation: fillBar 1s ease-out forwards; }
        @keyframes fillBar { from { width: 0; } }
    </style>
</head>
<body>
    <?php include 'header.php'; ?> 
    <div class="container">
        <a href="/entrypj/event_registrations.php?event_id=<?php echo $event_id; ?>" style="text-decoration:none; font-weight:bold; color:#7f8c8d;">⬅ กลับหน้าจัดการผู้เข้าร่วม</a>
        <h2 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px;">📊 สถิติผู้เข้าร่วม: <?php echo htmlspecialchars($event['event_name']); ?></h2>

        <div class="card">
            <h3>🚻 สัดส่วนเพศผู้เข้าร่วม</h3>
            <?php foreach($genders as $label => $count): ?>
                <?php $color = ($label == 'ชาย') ? '#3498db' : (($label == 'หญิง') ? '#e74c3c' : '#95a5a6'); ?>
                <div class="stat-row">
                    <div class="stat-label"><span><?php echo $label; ?></span> <span><?php echo $count; ?> คน</span></div>
                    <div class="bar-bg"><div class="bar-fill" style="background-color: <?php echo $color; ?>; width: <?php echo ($count/$max_gender)*100; ?>%;"></div></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <h3>🎂 ช่วงอายุผู้เข้าร่วม</h3>
            <?php foreach($age_ranges as $label => $count): ?>
                <?php if ($count == 0) continue; ?>
                <div class="stat-row">
                    <div class="stat-label"><span><?php echo $label; ?></span> <span><?php echo $count; ?> คน</span></div>
                    <div class="bar-bg"><div class="bar-fill" style="background-color: #2ecc71; width: <?php echo ($count/$max_age)*100; ?>%;"></div></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <h3>📍 จังหวัดที่เข้าร่วม</h3>
            <?php if (empty($provinces)): ?> <p style="text-align:center; color:#95a5a6;">ยังไม่มีข้อมูล</p> <?php endif; ?>
            <?php foreach($provinces as $label => $count): ?>
                <div class="stat-row">
                    <div class="stat-label"><span><?php echo htmlspecialchars($label); ?></span> <span><?php echo $count; ?> คน</span></div>
                    <div class="bar-bg"><div class="bar-fill" style="background-color: #f39c12; width: <?php echo ($count/$max_prov)*100; ?>%;"></div></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>