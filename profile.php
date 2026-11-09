<?php
session_start();
require 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลบัญชีผู้ใช้งาน
$sql_user = "SELECT * FROM user WHERE user_id='$user_id'";
$result_user = $conn->query($sql_user);
$user_info = ($result_user && $result_user->num_rows > 0) ? $result_user->fetch_assoc() : ['username' => $_SESSION['username'], 'phone' => '-'];

// สถิติ: งานที่เคยโพสต์ฝากซื้อ
$sql_count_orders = "SELECT COUNT(*) AS total_orders FROM orders WHERE customer_id='$user_id'";
$res_count_orders = $conn->query($sql_count_orders);
$count_orders = $res_count_orders ? $res_count_orders->fetch_assoc()['total_orders'] : 0;

// สถิติ: งานที่เคยรับหิ้ว
$sql_count_jobs = "SELECT COUNT(*) AS total_jobs FROM orders WHERE sender_id='$user_id'";
$res_count_jobs = $conn->query($sql_count_jobs);
$count_jobs = $res_count_jobs ? $res_count_jobs->fetch_assoc()['total_jobs'] : 0;

// คำนวณรายได้สะสมจากการรับหิ้วที่จัดส่งสำเร็จ
$sql_earnings = "SELECT SUM(errand_fee) AS total_earnings FROM orders WHERE sender_id='$user_id' AND status='จัดส่งสำเร็จ'";
$res_earnings = $conn->query($sql_earnings);
$total_earnings = ($res_earnings && $row_e = $res_earnings->fetch_assoc()) && !empty($row_e['total_earnings']) ? (float)$row_e['total_earnings'] : 0.00;

// ดึงประวัติการโพสต์ฝากซื้อ (Customer History)
$sql_customer_history = "SELECT orders.*, locations.name_place 
                         FROM orders 
                         LEFT JOIN locations ON orders.location_id = locations.location_id 
                         WHERE orders.customer_id='$user_id' 
                         ORDER BY orders.order_id DESC";
$result_customer_history = $conn->query($sql_customer_history);

// ดึงประวัติการรับหิ้ว (Rider History)
$sql_rider_history = "SELECT orders.*, locations.name_place, user.username AS customer_name 
                      FROM orders 
                      LEFT JOIN locations ON orders.location_id = locations.location_id 
                      LEFT JOIN user ON orders.customer_id = user.user_id 
                      WHERE orders.sender_id='$user_id' 
                      ORDER BY orders.order_id DESC";
$result_rider_history = $conn->query($sql_rider_history);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ & ประวัติกิจกรรม - BuddyDrop</title>
    
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

    <!-- Minimalist Sticky Navigation Bar -->
    <header class="app-navbar">
        <div class="nav-container">
            <div class="brand-wrapper">
                <a href="customer_dashboard.php" class="brand-logo">
                    <span class="logo-icon">🛵</span>
                    <span>Buddy<span class="brand-highlight">Drop</span></span>
                </a>
                <span class="brand-tag">บางแสน ม.บูรพา</span>
            </div>

            <div class="nav-user-actions">
                <!-- ทางลัดกลับหน้าหลัก -->
                <a href="customer_dashboard.php" class="mode-nav-btn customer-mode">
                    <span>🏠 หน้าหลัก (Feed)</span>
                </a>
                <a href="sender_dashboard.php" class="mode-nav-btn rider-mode">
                    <span>🛵 โหมดรับหิ้ว</span>
                </a>

                <div class="user-profile-pill" style="border-color: var(--primary); background: var(--primary-light);">
                    <span class="user-avatar" style="background: var(--primary); color: white;">👤</span>
                    <span style="font-weight: 600; color: var(--primary);"><?php echo htmlspecialchars($user_info['username']); ?></span>
                </div>

                <!-- ปุ่มสลับธีม ดวงจันทร์ (Dark Mode) -->
                <button type="button" class="theme-toggle-btn" onclick="toggleTheme()" title="เปลี่ยนธีม สว่าง / ดำ-ทอง 🌙">
                    <span class="theme-toggle-icon">🌙</span>
                    <span class="theme-toggle-text">ดวงจันทร์</span>
                </button>

                <a href="logout.php" class="btn-logout" title="ออกจากระบบ" onclick="event.preventDefault(); showCenterConfirm('คุณต้องการออกจากระบบใช่หรือไม่?', 'ยืนยันออกจากระบบ', '🚪', () => { window.location.href = 'logout.php'; });">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span>ออก</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="main-wrapper">

        <!-- Profile Hero & Stats Card -->
        <div class="profile-card-hero">
            <div class="profile-hero-top">
                <div class="profile-avatar-large">👤</div>
                <div class="profile-info-block">
                    <h1 class="profile-name"><?php echo htmlspecialchars($user_info['username']); ?></h1>
                    <div class="profile-meta-chips">
                        <span class="profile-chip">
                            <span>📱 เบอร์โทร:</span>
                            <strong><?php echo !empty($user_info['phone']) ? htmlspecialchars($user_info['phone']) : 'ไม่ได้ระบุ'; ?></strong>
                        </span>
                        <span class="profile-chip" style="background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe;">
                            <span>⚡ สมาชิก BuddyDrop (สั่งของ & รับหิ้วได้)</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stats Metrics Grid -->
            <div class="profile-stats-grid">
                <div class="stat-metric-card">
                    <span class="stat-metric-label">🛍️ โพสต์ฝากซื้อ</span>
                    <span class="stat-metric-value"><?php echo $count_orders; ?> <small style="font-size: 13px; font-weight: normal; color: var(--text-muted);">รายการ</small></span>
                </div>

                <div class="stat-metric-card">
                    <span class="stat-metric-label">🛵 รับงานหิ้ว</span>
                    <span class="stat-metric-value"><?php echo $count_jobs; ?> <small style="font-size: 13px; font-weight: normal; color: var(--text-muted);">รายการ</small></span>
                </div>

                <div class="stat-metric-card">
                    <span class="stat-metric-label">💵 รายได้สะสม</span>
                    <span class="stat-metric-value highlight-green">+<?php echo number_format($total_earnings, 2); ?> <small style="font-size: 13px; font-weight: normal;">฿</small></span>
                </div>
            </div>
        </div>

        <!-- Activity History Tab Switcher -->
        <div class="view-toggle-bar">
            <button type="button" class="view-toggle-btn active" id="tabCustomerBtn" onclick="switchProfileTab('customer')">
                <span>🛍️ ประวัติการฝากซื้อ</span>
                <span class="count-chip"><?php echo $count_orders; ?></span>
            </button>
            <button type="button" class="view-toggle-btn" id="tabRiderBtn" onclick="switchProfileTab('rider')">
                <span>🛵 ประวัติการรับหิ้ว</span>
                <span class="count-chip"><?php echo $count_jobs; ?></span>
            </button>
        </div>

        <!-- ==========================================
             TAB 1: CUSTOMER ACTIVITY (ประวัติที่เคยโพสต์ฝากซื้อ)
             ========================================== -->
        <div class="tab-pane" id="customer-activity-section">
            <div class="history-card-list">
                <?php
                if ($result_customer_history && $result_customer_history->num_rows > 0) {
                    while($row = $result_customer_history->fetch_assoc()) {
                        $is_waiting = ($row['status'] == 'รอผู้รับงาน');
                        $is_delivering = ($row['status'] == 'รับงานแล้ว/กำลังไปส่ง');
                        $is_completed = ($row['status'] == 'จัดส่งสำเร็จ');
                        $post_time = !empty($row['created_at']) ? $row['created_at'] : 'ไม่มีข้อมูลเวลา';
                        ?>
                        <div class="history-item-card">
                            <div class="history-item-left">
                                <!-- รูปภาพสินค้า -->
                                <?php if (!empty($row['order_image'])): ?>
                                    <img 
                                        src="uploads/<?php echo htmlspecialchars($row['order_image']); ?>" 
                                        alt="รูปสินค้า" 
                                        class="history-img-thumb"
                                        onclick="openImageModal(this.src)"
                                        title="คลิกเพื่อดูรูปภาพขนาดใหญ่"
                                    >
                                <?php else: ?>
                                    <div class="history-img-placeholder">🛍️</div>
                                <?php endif; ?>

                                <!-- ข้อมูลออเดอร์ -->
                                <div class="history-item-info">
                                    <div class="history-meta-row">
                                        <span class="history-order-id">ออเดอร์ #<?php echo $row['order_id']; ?></span>
                                        <span class="history-fee-tag">ค่าหิ้ว <?php echo number_format((float)$row['errand_fee'], 2); ?> ฿</span>
                                        <?php if (!empty($row['name_place'])): ?>
                                            <span style="font-size: 12px; color: var(--text-muted);">📍 นัดรับ: <?php echo htmlspecialchars($row['name_place']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="history-detail-text">
                                        <?php echo nl2br(htmlspecialchars($row['order_detail'])); ?>
                                    </p>
                                    <span style="font-size: 11px; color: var(--text-muted);">🕒 วันที่โพสต์: <?php echo htmlspecialchars($post_time); ?></span>
                                </div>
                            </div>

                            <!-- สถานะของออเดอร์ และปุ่มแชท -->
                            <div class="history-item-right" style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                                <?php if (!$is_waiting): ?>
                                    <a href="chat_room.php?order_id=<?php echo $row['order_id']; ?>" class="btn-chat-link">
                                        <span>💬</span>
                                        <span>แชทคุยกับไรเดอร์</span>
                                    </a>
                                <?php endif; ?>

                                <?php if ($is_waiting): ?>
                                    <span class="status-badge badge-waiting">
                                        <span class="badge-dot"></span>
                                        <span>รอคนรับหิ้ว</span>
                                    </span>
                                <?php elseif ($is_delivering): ?>
                                    <span class="status-badge badge-in-progress">
                                        <span>🛵</span>
                                        <span>กำลังจัดส่ง</span>
                                    </span>
                                <?php elseif ($is_completed): ?>
                                    <span class="status-badge badge-completed">
                                        <span>✅</span>
                                        <span>จัดส่งสำเร็จ</span>
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="empty-state-box" style="padding: 36px 20px;">
                        <div class="empty-state-icon">🛍️</div>
                        <h4 class="empty-state-title">ยังไม่มีประวัติการโพสต์ฝากซื้อ</h4>
                        <p class="empty-state-text">อยากได้อะไรเป็นพิเศษไหมคะ? ลองโพสต์ฝากซื้อในโหมดลูกค้าได้เลย!</p>
                        <a href="customer_dashboard.php" class="btn-submit-order" style="display: inline-flex; width: auto; padding: 10px 20px; margin-top: 14px;">
                            <span>🚀 ไปหน้าฝากซื้อสินค้า</span>
                        </a>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

        <!-- ==========================================
             TAB 2: RIDER ACTIVITY (ประวัติที่เคยรับหิ้ว)
             ========================================== -->
        <div class="tab-pane" id="rider-activity-section" style="display: none;">
            <div class="jobs-card-list">
                <?php
                if ($result_rider_history && $result_rider_history->num_rows > 0) {
                    while($row = $result_rider_history->fetch_assoc()) {
                        $is_delivering = ($row['status'] == 'รับงานแล้ว/กำลังไปส่ง');
                        $is_completed = ($row['status'] == 'จัดส่งสำเร็จ');
                        ?>
                        <div class="job-item-card">
                            <div class="job-item-header">
                                <div class="job-header-left">
                                    <span class="job-order-id">รหัสออเดอร์ #<?php echo $row['order_id']; ?></span>
                                    <span class="job-fee-badge">💵 ได้รับ +<?php echo number_format((float)$row['errand_fee'], 2); ?> ฿</span>
                                    <?php if (!empty($row['customer_name'])): ?>
                                        <span style="font-size: 12px; color: var(--text-muted);">👤 ผู้ฝาก: <b><?php echo htmlspecialchars($row['customer_name']); ?></b></span>
                                    <?php endif; ?>
                                </div>

                                <div class="job-header-right">
                                    <?php if ($is_delivering): ?>
                                        <span class="status-badge badge-in-progress">🛵 กำลังจัดส่ง</span>
                                    <?php elseif ($is_completed): ?>
                                        <span class="status-badge badge-completed">✅ สำเร็จแล้ว</span>
                                    <?php else: ?>
                                        <span class="status-badge"><?php echo htmlspecialchars($row['status']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- รายละเอียด -->
                            <div class="job-detail-text">
                                <p class="job-detail-desc"><?php echo nl2br(htmlspecialchars($row['order_detail'])); ?></p>
                                <?php if (!empty($row['name_place'])): ?>
                                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 6px;">📍 สถานที่นัดส่ง: <b><?php echo htmlspecialchars($row['name_place']); ?></b></div>
                                <?php endif; ?>
                            </div>

                            <!-- รูปสินค้า (ถ้ามี) -->
                            <?php if (!empty($row['order_image'])): ?>
                            <div class="job-media-box">
                                <div class="job-image-wrapper">
                                    <img 
                                        src="uploads/<?php echo htmlspecialchars($row['order_image']); ?>" 
                                        alt="รูปภาพสินค้า" 
                                        class="job-order-img"
                                        onclick="openImageModal(this.src)"
                                        title="คลิกเพื่อดูรูปภาพขนาดใหญ่"
                                    >
                                    <div class="job-image-badge">
                                        <span>🔍 คลิกดูรูปสินค้า (Zoom)</span>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- ส่วนท้าย -->
                            <div class="job-footer-actions">
                                <div class="job-action-left" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <?php if (!empty($row['order_image'])): ?>
                                        <button 
                                            type="button" 
                                            class="btn-view-job-detail" 
                                            onclick="openImageModal('uploads/<?php echo htmlspecialchars($row['order_image']); ?>')"
                                        >
                                            <span>🖼️ ดูรูปสินค้า</span>
                                        </button>
                                    <?php endif; ?>

                                    <!-- ปุ่มแชทกับลูกค้า (สำหรับงานที่กำลังส่ง และงานที่ส่งสำเร็จแล้ว) -->
                                    <a href="chat_room.php?order_id=<?php echo $row['order_id']; ?>" class="btn-chat-link">
                                        <span>💬</span>
                                        <span>แชทกับลูกค้า</span>
                                    </a>
                                </div>

                                <div class="job-action-right">
                                    <?php if ($is_delivering): ?>
                                        <a href="sender_dashboard.php#jobs" class="btn-finish" style="text-decoration: none;">
                                            <span>🛵 ไปที่หน้าจัดการงาน</span>
                                        </a>
                                    <?php else: ?>
                                        <span style="font-size: 13px; color: var(--text-muted);">🎉 ได้รับค่าตอบแทนเรียบร้อย</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="empty-state-box" style="padding: 36px 20px;">
                        <div class="empty-state-icon">🛵✨</div>
                        <h4 class="empty-state-title">ยังไม่มีประวัติการรับหิ้ว</h4>
                        <p class="empty-state-text">อยากสร้างรายได้พิเศษในมอไหมคะ? ลองเข้าฟีดรับงานในโหมดคนรับหิ้วได้เลย!</p>
                        <a href="sender_dashboard.php" class="btn-submit-order" style="display: inline-flex; width: auto; padding: 10px 20px; margin-top: 14px;">
                            <span>🚀 ไปดูฟีดงานรับหิ้ว</span>
                        </a>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

    </main>

    <!-- Lightbox Modal สำหรับขยายดูรูปภาพ -->
    <div id="imageLightboxModal" class="image-modal-backdrop" onclick="closeImageModal()">
        <span class="modal-close-btn">&times;</span>
        <img id="lightboxModalImg" class="image-modal-content" src="" alt="ภาพสินค้าขยายใหญ่">
    </div>

    <!-- Scripts -->
    <script>
        function switchProfileTab(tabName) {
            const customerSec = document.getElementById('customer-activity-section');
            const riderSec = document.getElementById('rider-activity-section');
            const tabCustomerBtn = document.getElementById('tabCustomerBtn');
            const tabRiderBtn = document.getElementById('tabRiderBtn');

            if (tabName === 'rider') {
                customerSec.style.display = 'none';
                riderSec.style.display = 'block';
                tabRiderBtn.classList.add('active');
                tabCustomerBtn.classList.remove('active');
                history.replaceState(null, null, '#rider');
            } else {
                customerSec.style.display = 'block';
                riderSec.style.display = 'none';
                tabCustomerBtn.classList.add('active');
                tabRiderBtn.classList.remove('active');
                history.replaceState(null, null, '#customer');
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            if (window.location.hash === '#rider') {
                switchProfileTab('rider');
            } else {
                switchProfileTab('customer');
            }
        });

        function openImageModal(src) {
            const modal = document.getElementById('imageLightboxModal');
            const modalImg = document.getElementById('lightboxModalImg');
            if (modal && modalImg) {
                modalImg.src = src;
                modal.style.display = 'flex';
            }
        }

        function closeImageModal() {
            const modal = document.getElementById('imageLightboxModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // Modern Top Toast System
        let topToastTimeout = null;

        function showTopToast(msg, title = 'สำเร็จ', icon = '✅', type = 'success', duration = 4000) {
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

        window.alert = function(msg) {
            showCenterAlert(msg, 'แจ้งเตือน', 'ℹ️');
        };

        // Modern Centered Confirmation System
        let pendingConfirmCallback = null;

        function showCenterConfirm(msg, title = 'ยืนยันการทำรายการ', icon = '❓', onConfirm = null) {
            const backdrop = document.getElementById('modernConfirmBackdrop');
            const msgEl = document.getElementById('centerConfirmMsg');
            const titleEl = document.getElementById('centerConfirmTitle');
            const iconEl = document.getElementById('centerConfirmIcon');

            if (msgEl) msgEl.textContent = msg;
            if (titleEl) titleEl.textContent = title;
            if (iconEl) iconEl.textContent = icon;
            pendingConfirmCallback = onConfirm;

            if (backdrop) backdrop.style.display = 'flex';
        }

        function closeCenterConfirm(result) {
            const backdrop = document.getElementById('modernConfirmBackdrop');
            if (backdrop) backdrop.style.display = 'none';
            if (result && typeof pendingConfirmCallback === 'function') {
                pendingConfirmCallback();
            }
            pendingConfirmCallback = null;
        }
    </script>

    <!-- Modern Top Notification Toast (แท็บแจ้งเตือนเล็กๆ ด้านบน) -->
    <div id="modernTopToast" class="modern-top-toast">
        <div class="modern-top-toast-icon" id="topToastIcon">👋</div>
        <div class="modern-top-toast-content">
            <span class="modern-top-toast-title" id="topToastTitle">สำเร็จ</span>
            <span class="modern-top-toast-text" id="topToastText">ข้อความแจ้งเตือน</span>
        </div>
        <button type="button" class="modern-top-toast-close" onclick="closeTopToast()" title="ปิด">&times;</button>
    </div>

    <!-- Modern Centered Confirm Modal (แท็บยืนยันตรงกลางหน้าจอ) -->
    <div id="modernConfirmBackdrop" class="modern-alert-backdrop" onclick="closeCenterConfirm(false)">
        <div class="modern-alert-box" onclick="event.stopPropagation()">
            <div class="modern-alert-icon-wrapper" id="centerConfirmIcon" style="background: #fff7ed; border-color: #fed7aa; color: #ea580c;">❓</div>
            <h3 class="modern-alert-title" id="centerConfirmTitle">ยืนยันการทำรายการ</h3>
            <p class="modern-alert-msg" id="centerConfirmMsg">คุณแน่ใจหรือไม่ว่าต้องการดำเนินการนี้?</p>
            <div style="display: flex; gap: 10px; width: 100%;">
                <button type="button" class="btn-cancel-order" style="flex: 1; padding: 12px; font-size: 14px; border-radius: var(--radius-md);" onclick="closeCenterConfirm(false)">ยกเลิก</button>
                <button type="button" class="btn-alert-confirm" style="flex: 1; padding: 12px; font-size: 14px;" onclick="closeCenterConfirm(true)">ตกลง</button>
            </div>
        </div>
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
</body>
</html>

