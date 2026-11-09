<?php
require 'connect.php';

$alert_msg = "";
$alert_title = "";
$alert_icon = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    $role = 'member'; // บังคับให้ทุกคนเป็น member ทันที!
    $phone = $_POST['phone'];

    $sql = "INSERT INTO user (username, password, role, phone) VALUES ('$username', '$password', '$role', '$phone')";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: login.php?registered=1");
        exit();
    } else {
        $alert_msg = "เกิดข้อผิดพลาดในการสมัครสมาชิก (อาจมีชื่อผู้ใช้งานนี้แล้ว): " . $conn->error;
        $alert_title = "สมัครสมาชิกไม่สำเร็จ";
        $alert_icon = "⚠️";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - BuddyDrop รับหิ้วบางแสน</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <!-- Main Minimalist Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css?v=2.3">

    <!-- Theme Switcher Script (Loads immediately to prevent flicker) -->
    <script src="assets/js/theme.js?v=2.3"></script>
</head>
<body>
    <div class="auth-page-wrapper">
        <!-- Theme Switcher Floating Pill -->
        <div class="auth-theme-toggle-wrap">
            <button type="button" class="theme-toggle-btn" onclick="toggleTheme()" title="เปลี่ยนธีม สว่าง / ดำ-ทอง 🌙">
                <span class="theme-toggle-icon">🌙</span>
                <span class="theme-toggle-text">ดวงจันทร์</span>
            </button>
        </div>

        <!-- Ambient Background Glow -->
        <div class="auth-ambient-glow"></div>

        <!-- Fujiking-Inspired Minimalist Auth Card -->
        <div class="auth-card" style="max-width: 460px;">
            
            <!-- Logo & Brand Header -->
            <div class="auth-header">
                <div class="auth-brand-badge">
                    <span>🛵</span>
                    <span>บริการรับหิ้วของบางแสน ม.บูรพา</span>
                </div>
                <div>
                    <a href="register.php" class="auth-logo">
                        <span class="auth-logo-icon">🛵</span>
                        <span>Buddy<span style="color: var(--primary);">Drop</span></span>
                    </a>
                </div>
                <h1 class="auth-title">สมัครสมาชิกใหม่</h1>
                <p class="auth-subtitle">สร้างบัญชีเพื่อเริ่มฝากซื้อ หรือรับงานหิ้วสร้างรายได้</p>
            </div>

            <!-- Register Form (คงฟิลด์ username, password, role, phone ตาม Backend เดิม 100%) -->
            <form method="POST" action="">
                
                <!-- ชื่อผู้ใช้งาน -->
                <div class="form-group">
                    <label class="form-label" for="username">
                        <span>ชื่อผู้ใช้งาน</span>
                        <span class="required-mark">*</span>
                    </label>
                    <div class="input-with-icon-wrapper">
                        <span class="input-leading-icon">👤</span>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-control has-leading-icon" 
                            placeholder="ตั้งชื่อผู้ใช้งานของคุณ" 
                            required 
                            autocomplete="username"
                        >
                    </div>
                </div>

                <!-- รหัสผ่าน พร้อมปุ่มเปิด/ปิดดูรหัสผ่าน -->
                <div class="form-group">
                    <label class="form-label" for="password">
                        <span>รหัสผ่าน</span>
                        <span class="required-mark">*</span>
                    </label>
                    <div class="input-with-icon-wrapper">
                        <span class="input-leading-icon">🔒</span>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control has-leading-icon has-trailing-icon" 
                            placeholder="ตั้งรหัสผ่านสำหรับเข้าสู่ระบบ" 
                            required 
                            autocomplete="new-password"
                        >
                        <button 
                            type="button" 
                            class="btn-toggle-password" 
                            id="togglePasswordBtn" 
                            title="แสดง/ซ่อนรหัสผ่าน"
                            aria-label="Toggle password visibility"
                        >
                            👁️
                        </button>
                    </div>
                </div>

                <!-- เลือกบทบาท (Interactive Role Cards + Select Dropdown) -->
                <div class="form-group">
                    <label class="form-label" for="role">
                        <span>เลือกบทบาทของคุณ</span>
                        <span class="required-mark">*</span>
                    </label>
                    
                    <div class="role-select-grid">
                        <div class="role-choice-card active" id="cardCustomer" onclick="chooseRole('customer')">
                            <span class="role-choice-icon">🛍️</span>
                            <span class="role-choice-name">ลูกค้า</span>
                            <span class="role-choice-sub">ฝากซื้อของ</span>
                        </div>
                        <div class="role-choice-card" id="cardSender" onclick="chooseRole('sender')">
                            <span class="role-choice-icon">🛵</span>
                            <span class="role-choice-name">คนรับหิ้ว</span>
                            <span class="role-choice-sub">ไรเดอร์รับงาน</span>
                        </div>
                    </div>

                    <select id="role" name="role" class="form-control" required style="display: none;">
                        <option value="customer" selected>ลูกค้า (ฝากซื้อ)</option>
                        <option value="sender">คนรับหิ้ว (Rider)</option>
                    </select>
                </div>

                <!-- เบอร์โทรศัพท์ -->
                <div class="form-group">
                    <label class="form-label" for="phone">
                        <span>เบอร์โทรศัพท์</span>
                        <span class="required-mark">*</span>
                    </label>
                    <div class="input-with-icon-wrapper">
                        <span class="input-leading-icon">📱</span>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            class="form-control has-leading-icon" 
                            placeholder="เช่น 0812345678" 
                            required 
                            autocomplete="tel"
                        >
                    </div>
                </div>

                <!-- ปุ่ม Submit สมัครสมาชิก -->
                <button type="submit" class="btn-submit-order" style="margin-top: 16px;">
                    <span>✨</span>
                    <span>สมัครสมาชิก (Create Account)</span>
                </button>
            </form>

            <!-- ลิงก์สลับไปหน้าเข้าสู่ระบบ -->
            <div class="auth-footer-text">
                มีบัญชีผู้ใช้งานอยู่แล้วใช่ไหม? 
                <a href="login.php">เข้าสู่ระบบที่นี่</a>
            </div>
        </div>

        <!-- Campus Trust Tag -->
        <div class="auth-campus-tag">
            <span>🎓</span>
            <span>สำหรับนิสิตและบุคลากร มหาวิทยาลัยบูรพา</span>
        </div>
    </div>

    <!-- Interactive Scripts (Toggle Password & Role Selection Cards) -->
    <script>
        // Password visibility toggle
        const togglePasswordBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');

        if (togglePasswordBtn && passwordInput) {
            togglePasswordBtn.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                this.textContent = isPassword ? '🙈' : '👁️';
            });
        }

        // Role Card Selection helper
        function chooseRole(role) {
            const selectElement = document.getElementById('role');
            const cardCustomer = document.getElementById('cardCustomer');
            const cardSender = document.getElementById('cardSender');

            if (selectElement) {
                selectElement.value = role;
            }

            if (role === 'customer') {
                cardCustomer.classList.add('active');
                cardSender.classList.remove('active');
            } else {
                cardSender.classList.add('active');
                cardCustomer.classList.remove('active');
            }
        }

        // Modern Centered Alert System
        function showCenterAlert(msg, title = 'แจ้งเตือน', icon = 'ℹ️') {
            const backdrop = document.getElementById('modernAlertBackdrop');
            const msgEl = document.getElementById('centerAlertMsg');
            const titleEl = document.getElementById('centerAlertTitle');
            const iconEl = document.getElementById('centerAlertIcon');

            if (msgEl) msgEl.textContent = msg;
            if (titleEl) titleEl.textContent = title;
            if (iconEl) iconEl.textContent = icon;
            if (backdrop) backdrop.style.display = 'flex';
        }

        function closeCenterAlert() {
            const backdrop = document.getElementById('modernAlertBackdrop');
            if (backdrop) backdrop.style.display = 'none';
        }

        // Override native alert
        window.alert = function(msg) {
            showCenterAlert(msg, 'แจ้งเตือน', 'ℹ️');
        };

        <?php if (!empty($alert_msg)): ?>
        window.addEventListener('DOMContentLoaded', () => {
            showCenterAlert(
                <?php echo json_encode($alert_msg); ?>, 
                <?php echo json_encode($alert_title); ?>, 
                <?php echo json_encode($alert_icon); ?>
            );
        });
        <?php endif; ?>
    </script>

    <!-- Modern Centered Alert Modal (แท็บแจ้งเตือนตรงกลางหน้าจอ) -->
    <div id="modernAlertBackdrop" class="modern-alert-backdrop" onclick="closeCenterAlert()">
        <div class="modern-alert-box" onclick="event.stopPropagation()">
            <div class="modern-alert-icon-wrapper" id="centerAlertIcon">ℹ️</div>
            <h3 class="modern-alert-title" id="centerAlertTitle">แจ้งเตือน</h3>
            <p class="modern-alert-msg" id="centerAlertMsg">ข้อความแจ้งเตือน</p>
            <button type="button" class="btn-alert-confirm" onclick="closeCenterAlert()">ตกลง</button>
        </div>
    </div>
</body>
</html>
