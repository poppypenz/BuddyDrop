<?php
session_start();
require 'connect.php';

$alert_msg = "";
$alert_title = "";
$alert_icon = "";

$toast_msg = "";
$toast_title = "";
$toast_icon = "";
$toast_type = "info";

// กรณีเพิ่งสมัครสมาชิกเสร็จแล้วเด้งมาหน้านี้
if (isset($_GET['registered'])) {
    $alert_msg = "สมัครสมาชิกสำเร็จเรียบร้อยแล้วค่ะ! คุณสามารถเข้าสู่ระบบเพื่อเริ่มต้นใช้งานได้ทันที";
    $alert_title = "สมัครสมาชิกสำเร็จ!";
    $alert_icon = "🎉";
}

// กรณีเพิ่งออกจากระบบมา (แสดงเป็นแท็บแจ้งเตือนเล็กๆ ด้านบน)
if (isset($_GET['msg']) && $_GET['msg'] === 'logged_out') {
    $toast_msg = "ออกจากระบบเรียบร้อยแล้วค่ะ ไว้วางใจใช้บริการ BuddyDrop ได้เสมอนะคะ";
    $toast_title = "ออกจากระบบสำเร็จ";
    $toast_icon = "👋";
    $toast_type = "info";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM user WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // ตรวจสอบรหัสผ่านที่เข้ารหัสไว้
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            
            // แบบ Single Account: ล็อกอินปุ๊บ พุ่งตรงไปหน้าหลักพร้อมแจ้งเตือนต้อนรับตรงกลางหน้าจอ!
            header("Location: customer_dashboard.php?msg=login_success");
            exit();
        } else {
            $alert_msg = "รหัสผ่านไม่ถูกต้อง กรุณาตรวจสอบและลองใหม่อีกครั้งค่ะ";
            $alert_title = "เข้าสู่ระบบไม่สำเร็จ";
            $alert_icon = "🔒";
        }
    } else {
        $alert_msg = "ไม่พบชื่อผู้ใช้งานนี้ในระบบค่ะ กรุณาสมัครสมาชิกใหม่หรือตรวจสอบตัวสะกด";
        $alert_title = "ไม่พบผู้ใช้งาน";
        $alert_icon = "👤";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - BuddyDrop รับหิ้วบางแสน</title>
    
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
        <div class="auth-card">
            
            <!-- Logo & Brand Header -->
            <div class="auth-header">
                <div class="auth-brand-badge">
                    <span>🛵</span>
                    <span>บริการรับหิ้วของบางแสน ม.บูรพา</span>
                </div>
                <div>
                    <a href="login.php" class="auth-logo">
                        <span class="auth-logo-icon">🛵</span>
                        <span>Buddy<span style="color: var(--primary);">Drop</span></span>
                    </a>
                </div>
                <h1 class="auth-title">เข้าสู่ระบบ</h1>
                <p class="auth-subtitle">ยินดีต้อนรับกลับมา! กรอกข้อมูลเพื่อเข้าใช้งานบัญชีของคุณ</p>
            </div>

            <!-- Login Form (คงฟิลด์ username & password ตาม Backend เดิม 100%) -->
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
                            placeholder="กรอกชื่อผู้ใช้งานของคุณ" 
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
                            placeholder="กรอกรหัสผ่านของคุณ" 
                            required 
                            autocomplete="current-password"
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

                <!-- ปุ่ม Submit เข้าสู่ระบบ -->
                <button type="submit" class="btn-submit-order" style="margin-top: 16px;">
                    <span>🚀</span>
                    <span>เข้าสู่ระบบ (Sign In)</span>
                </button>
            </form>

            <!-- ลิงก์สลับไปหน้าสมัครสมาชิก -->
            <div class="auth-footer-text">
                ยังไม่มีบัญชีผู้ใช้งานกับ BuddyDrop? 
                <a href="register.php">สมัครสมาชิกใหม่ที่นี่</a>
            </div>
        </div>

        <!-- Campus Trust Tag -->
        <div class="auth-campus-tag">
            <span>🎓</span>
            <span>สำหรับนิสิตและบุคลากร มหาวิทยาลัยบูรพา</span>
        </div>
    </div>

    <!-- Modern Top Notification Toast (แท็บแจ้งเตือนเล็กๆ ด้านบน) -->
    <div id="modernTopToast" class="modern-top-toast">
        <div class="modern-top-toast-icon" id="topToastIcon">👋</div>
        <div class="modern-top-toast-content">
            <span class="modern-top-toast-title" id="topToastTitle">ออกจากระบบสำเร็จ</span>
            <span class="modern-top-toast-text" id="topToastText">ข้อความแจ้งเตือน</span>
        </div>
        <button type="button" class="modern-top-toast-close" onclick="closeTopToast()" title="ปิด">&times;</button>
    </div>

    <!-- Modern Centered Alert Modal (แท็บแจ้งเตือนตรงกลางหน้าจอ) -->
    <div id="modernAlertBackdrop" class="modern-alert-backdrop" onclick="closeCenterAlert()">
        <div class="modern-alert-box" onclick="event.stopPropagation()">
            <div class="modern-alert-icon-wrapper" id="centerAlertIcon">ℹ️</div>
            <h3 class="modern-alert-title" id="centerAlertTitle">แจ้งเตือน</h3>
            <p class="modern-alert-msg" id="centerAlertMsg">ข้อความแจ้งเตือน</p>
            <button type="button" class="btn-alert-confirm" onclick="closeCenterAlert()">ตกลง</button>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Toggle password visibility
        const togglePasswordBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');

        if (togglePasswordBtn && passwordInput) {
            togglePasswordBtn.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                this.textContent = isPassword ? '🙈' : '👁️';
            });
        }

        // Modern Top Toast System
        let topToastTimeout = null;

        function showTopToast(msg, title = 'สำเร็จ', icon = '✅', type = 'info', duration = 4000) {
            const toast = document.getElementById('modernTopToast');
            if (!toast) return;

            const iconEl = document.getElementById('topToastIcon');
            const titleEl = document.getElementById('topToastTitle');
            const textEl = document.getElementById('topToastText');

            if (iconEl) iconEl.textContent = icon;
            if (titleEl) titleEl.textContent = title;
            if (textEl) textEl.textContent = msg;

            toast.className = `modern-top-toast toast-${type} show`;

            if (topToastTimeout) clearTimeout(topToastTimeout);
            if (duration > 0) {
                topToastTimeout = setTimeout(() => {
                    closeTopToast();
                }, duration);
            }
        }

        function closeTopToast() {
            const toast = document.getElementById('modernTopToast');
            if (toast) {
                toast.classList.remove('show');
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

        <?php if (!empty($toast_msg)): ?>
        window.addEventListener('DOMContentLoaded', () => {
            showTopToast(
                <?php echo json_encode($toast_msg); ?>, 
                <?php echo json_encode($toast_title); ?>, 
                <?php echo json_encode($toast_icon); ?>,
                <?php echo json_encode($toast_type); ?>
            );
            history.replaceState(null, null, 'login.php');
        });
        <?php endif; ?>

        <?php if (!empty($alert_msg)): ?>
        window.addEventListener('DOMContentLoaded', () => {
            showCenterAlert(
                <?php echo json_encode($alert_msg); ?>, 
                <?php echo json_encode($alert_title); ?>, 
                <?php echo json_encode($alert_icon); ?>
            );
            <?php if (isset($_GET['registered'])): ?>
            history.replaceState(null, null, 'login.php');
            <?php endif; ?>
        });
        <?php endif; ?>
    </script>
</body>
</html>
