<style>
    /* สไตล์สำหรับ Header ให้เข้ากับธีม Modern UI */
    .main-header {
        background-color: #ffffff;
        padding: 15px 25px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-family: 'Kanit', sans-serif;
        border: 1px solid rgba(0,0,0,0.05);
        flex-wrap: wrap; /* รองรับหน้าจอขนาดเล็ก */
        gap: 15px;
    }
    
    .header-logo a {
        text-decoration: none;
        font-weight: 600;
        font-size: 1.2rem;
        color: #4f46e5; /* สีน้ำเงินม่วง (Primary) */
        display: flex;
        align-items: center;
        gap: 8px;
        transition: color 0.2s;
    }
    .header-logo a:hover {
        color: #4338ca;
    }

    .header-nav {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .user-greeting {
        color: #64748b;
        font-weight: 400;
        font-size: 0.95rem;
        padding-right: 15px;
        border-right: 2px solid #e2e8f0;
    }
    .user-greeting span {
        color: #1e293b;
        font-weight: 600;
    }

    .nav-link {
        text-decoration: none;
        color: #475569;
        font-weight: 500;
        font-size: 0.95rem;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .nav-link:hover {
        color: #4f46e5;
        transform: translateY(-1px);
    }

    /* ปุ่มออกจากระบบ (Danger) */
    .btn-logout {
        background-color: #fee2e2;
        color: #dc2626;
        padding: 8px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.95rem;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .btn-logout:hover {
        background-color: #fca5a5;
        color: #991b1b;
    }

    /* สไตล์สำหรับกล่องแจ้งเตือนกรณียังไม่ล็อกอิน */
    .alert-warning {
        background-color: #fef3c7;
        color: #92400e;
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 30px;
        border: 1px solid #fde68a;
        font-family: 'Kanit', sans-serif;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .alert-warning a {
        color: #b45309;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.2s;
    }
    .alert-warning a:hover {
        color: #78350f;
        text-decoration: underline;
    }
</style>

<?php if (isset($_SESSION['user_id'])): ?>
    <div class="main-header">
        
        <div class="header-logo">
            <a href="/entrypj/templates/home.php">🏠 หน้าหลัก</a>
        </div>

        <div class="header-nav">
            <div class="user-greeting">
                👋 สวัสดี, <span><?php echo htmlspecialchars($_SESSION['name'] ?? 'ผู้ใช้งาน'); ?></span>
            </div>
            
            <a href="/entrypj/templates/profile.php" class="nav-link">👤 ข้อมูลบัญชี</a>
            <a href="/entrypj/templates/history.php" class="nav-link">📜 ประวัติ</a>
            <a href="/entrypj/templates/manage_event.php" class="nav-link">⚙️ จัดการกิจกรรม</a>
            
            <a href="/entrypj/routes/User.php?action=logout" class="btn-logout">🚪 ออกจากระบบ</a>
        </div>

    </div>
<?php else: ?>
    <div class="alert-warning">
        <span>⚠️ คุณยังไม่ได้เข้าสู่ระบบ <a href="/entrypj/templates/sign_in.php">คลิกที่นี่เพื่อเข้าสู่ระบบ</a> หรือสมัครสมาชิกเพื่อลงทะเบียนเข้าร่วมกิจกรรม</span>
    </div>
<?php endif; ?>