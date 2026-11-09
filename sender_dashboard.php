<?php
session_start();
require 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// ตรงนี้เปลี่ยนชื่อตัวแปรให้เป็น $sender_id เพื่อให้จำง่าย
$sender_id = $_SESSION['user_id'];

// [Update] รับงาน
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accept_order'])) {
    $order_id = $_POST['order_id'];
    $sql_update = "UPDATE orders SET sender_id='$sender_id', status='รับงานแล้ว/กำลังไปส่ง' WHERE order_id='$order_id'";
    $conn->query($sql_update);
    header("Location: sender_dashboard.php?msg=order_accepted#jobs");
    exit();
}

// [Update] จัดส่งสำเร็จปิดจ๊อบ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['finish_order'])) {
    $order_id = $_POST['order_id'];
    $sql_finish = "UPDATE orders SET status='จัดส่งสำเร็จ' WHERE order_id='$order_id'";
    $conn->query($sql_finish);
    header("Location: sender_dashboard.php?msg=order_finished#jobs");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BuddyDrop - ฟีดงานรับหิ้วบางแสน (โหมดผู้รับหิ้ว)</title>
    
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
                <a href="customer_dashboard.php" class="brand-logo" title="ไปหน้าหลัก Home Feed">
                    <span class="logo-icon">🛵</span>
                    <span>Buddy<span class="brand-highlight">Drop</span></span>
                </a>
                <span class="brand-tag">บางแสน ม.บูรพา</span>
            </div>

            <div class="nav-user-actions">
                <!-- ลิงก์กลับหน้าหลัก Feed -->
                <a href="customer_dashboard.php" class="mode-nav-btn customer-mode">
                    <span>🏠 หน้าหลัก (Feed)</span>
                </a>

                <span class="role-badge rider">
                    <span>🛵</span>
                    <span>โหมดคนรับหิ้ว</span>
                </span>
                
                <a href="profile.php" class="user-profile-pill" title="คลิกเพื่อดูโปรไฟล์และประวัติกิจกรรมทั้งหมด">
                    <span class="user-avatar">👤</span>
                    <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </a>

                <!-- ปุ่มสลับโหมดสั่งของ -->
                <a href="customer_dashboard.php" class="mode-nav-btn customer-mode">
                    <span>🛍️ สลับไปสั่งของ</span>
                </a>

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

        <!-- Page Header Greeting -->
        <div class="page-header-box">
            <span class="page-badge">✨ ชุมชนคนรับหิ้วสุดซิ่ง บางแสน</span>
            <h1 class="page-title" id="pageTitle">ฟีดงานฝากหิ้วที่เปิดรับ</h1>
            <p class="page-subtitle" id="pageSubtitle">เลือกดูออเดอร์ของเพื่อนๆ ม.บูรพา แล้วกดรับงานไปส่งได้ทันที</p>
        </div>

        <!-- Interactive Tab Switcher (สลับหน้าแท็บ ไม่เด้งเลื่อนลงข้างล่าง) -->
        <div class="view-toggle-bar">
            <button type="button" class="view-toggle-btn active" id="tabFeedBtn" onclick="switchTab('feed')">
                <span>📋 ฟีดงานเปิดรับ</span>
            </button>
            <button type="button" class="view-toggle-btn" id="tabJobsBtn" onclick="switchTab('jobs')">
                <span>📦 งานที่คุณรับผิดชอบ</span>
            </button>
        </div>

        <!-- ==========================================
             TAB 1: ORDER FEED (ฟีดงานเปิดรับ)
             ========================================== -->
        <div class="order-feed tab-pane" id="feed-section">
            <?php
            // ดึงข้อมูลออเดอร์ พร้อมชื่อสถานที่ และชื่อลูกค้าที่โพสต์ 
            $sql_pending = "SELECT orders.*, locations.name_place, user.username 
                            FROM orders 
                            JOIN locations ON orders.location_id = locations.location_id 
                            JOIN user ON orders.customer_id = user.user_id 
                            WHERE orders.status='รอผู้รับงาน' AND orders.customer_id != '$sender_id'
                            ORDER BY orders.order_id DESC";
            $result_pending = $conn->query($sql_pending);

            if ($result_pending->num_rows > 0) {
                while($row = $result_pending->fetch_assoc()) {
                    $post_time = !empty($row['created_at']) ? $row['created_at'] : 'เพิ่งโพสต์เมื่อสักครู่';
                    ?>
                    <!-- Component การ์ดออเดอร์สไตล์ฟีดโซเชียลมีเดีย -->
                    <article class="order-card">
                        
                        <!-- 1. ส่วนหัว: รูปโปรไฟล์ (Placeholder ไอคอน), ชื่อลูกค้า, เวลาที่โพสต์, และป้ายสถานะ (Badge) -->
                        <div class="card-header">
                            <div class="user-profile">
                                <div class="avatar-placeholder">👤</div>
                                <div class="user-info">
                                    <h4 class="user-name"><?php echo htmlspecialchars($row['username']); ?></h4>
                                    <span class="post-time">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                        <?php echo htmlspecialchars($post_time); ?>
                                    </span>
                                </div>
                            </div>
                            <span class="status-badge badge-waiting">
                                <span class="badge-dot"></span>
                                <span>รอคนรับหิ้ว</span>
                            </span>
                        </div>

                        <!-- 2. ส่วนเนื้อหา: ข้อความรายละเอียดสินค้า -->
                        <div class="card-content">
                            <p class="order-description"><?php echo nl2br(htmlspecialchars($row['order_detail'])); ?></p>
                        </div>

                        <!-- 3. ส่วนรูปภาพ: พื้นที่แสดงรูปสินค้าขนาดใหญ่ชัดเจนตรงกลางการ์ด (คลิกเพื่อดูรูปขยายได้) -->
                        <?php if (!empty($row['order_image'])): ?>
                        <div class="card-media">
                            <img 
                                src="uploads/<?php echo htmlspecialchars($row['order_image']); ?>" 
                                alt="รูปสินค้าสำหรับหิ้ว" 
                                class="order-image" 
                                loading="lazy"
                                onclick="openImageModal(this.src)"
                                title="คลิกเพื่อดูรูปขนาดใหญ่"
                            >
                        </div>
                        <?php endif; ?>

                        <!-- 4. ส่วนข้อมูล: แถบกล่องแสดงสถานที่นัดรับ และ แถบราคาค่าหิ้วเด่นๆ -->
                        <div class="card-meta-grid">
                            <div class="meta-box location-box">
                                <div class="meta-icon-wrapper">📍</div>
                                <div class="meta-content">
                                    <span class="meta-label">สถานที่นัดรับ</span>
                                    <span class="location-name" title="<?php echo htmlspecialchars($row['name_place']); ?>">
                                        <?php echo htmlspecialchars($row['name_place']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="meta-box fee-box">
                                <div class="meta-icon-wrapper">💵</div>
                                <div class="meta-content">
                                    <span class="meta-label">ค่าหิ้วที่คุณจะได้</span>
                                    <span class="fee-amount">+<?php echo number_format((float)$row['errand_fee'], 2); ?> ฿</span>
                                </div>
                            </div>
                        </div>

                        <!-- 5. ส่วนปุ่มกด: ปุ่ม 'รับหิ้วออเดอร์นี้' ขนาดใหญ่ กดง่าย สีสันชัดเจน -->
                        <div class="card-actions">
                            <form method="POST" action="" style="margin: 0; width: 100%;">
                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                <input type="hidden" name="accept_order" value="1">
                                <button type="button" class="btn-accept" onclick="triggerConfirmSubmit(this, 'คุณต้องการรับหิ้วออเดอร์นี้ใช่หรือไม่? เมื่อกดยืนยันแล้ว ระบบจะย้ายงานไปที่แท็บงานที่คุณรับผิดชอบค่ะ', 'ยืนยันการรับหิ้ว', '🚀')">
                                    <span>🚀</span>
                                    <span>รับหิ้วออเดอร์นี้</span>
                                </button>
                            </form>
                        </div>

                    </article>
                    <?php
                }
            } else {
                ?>
                <!-- Empty State เมื่อยังไม่มีใครโพสต์ออเดอร์ -->
                <div class="empty-state-box">
                    <div class="empty-state-icon">🛵💨</div>
                    <h3 class="empty-state-title">ยังไม่มีออเดอร์ที่เปิดรับในขณะนี้</h3>
                    <p class="empty-state-text">ตอนนี้ยังไม่มีใครฝากหิ้วของเลยค่าา รอสักครู่นะคะ หรือชวนเพื่อนๆ มาโพสต์ฝากซื้อได้เลย!</p>
                </div>
                <?php
            }
            ?>
        </div>

        <!-- ==========================================
             TAB 2: MY JOBS (งานที่คุณกำลังรับผิดชอบ)
             ========================================== -->
        <div class="tab-pane" id="my-jobs-section" style="display: none;">
            <div class="jobs-card-list">
                <?php
                // ดึงข้อมูลงานที่ Sender รับไปทำ (คงคำสั่ง SQL เดิม 100%)
                $sql_my_jobs = "SELECT * FROM orders WHERE sender_id='$sender_id' ORDER BY order_id DESC";
                $result_my_jobs = $conn->query($sql_my_jobs);
                
                if ($result_my_jobs->num_rows > 0) {
                    while($row = $result_my_jobs->fetch_assoc()) {
                        $is_delivering = ($row['status'] == 'รับงานแล้ว/กำลังไปส่ง');
                        $is_completed = ($row['status'] == 'จัดส่งสำเร็จ');
                        ?>
                        <div class="job-item-card">
                            <div class="job-item-header">
                                <div class="job-header-left">
                                    <span class="job-order-id">รหัสออเดอร์ #<?php echo $row['order_id']; ?></span>
                                    <?php if (!empty($row['errand_fee'])): ?>
                                        <span class="job-fee-badge">💵 ค่าหิ้ว +<?php echo number_format((float)$row['errand_fee'], 2); ?> ฿</span>
                                    <?php endif; ?>
                                </div>

                                <div class="job-header-right">
                                    <?php if ($is_delivering): ?>
                                        <span class="status-badge badge-in-progress">🛵 รับงานแล้ว / กำลังไปส่ง</span>
                                    <?php elseif ($is_completed): ?>
                                        <span class="status-badge badge-completed">✅ จัดส่งสำเร็จ</span>
                                    <?php else: ?>
                                        <span class="status-badge"><?php echo htmlspecialchars($row['status']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- รายละเอียดสินค้า -->
                            <div class="job-detail-text">
                                <p class="job-detail-desc"><?php echo nl2br(htmlspecialchars($row['order_detail'])); ?></p>
                            </div>

                            <!-- แสดงรูปภาพสินค้าที่รับหิ้ว (คลิกเพื่อขยายดูรูปเต็มได้) -->
                            <?php if (!empty($row['order_image'])): ?>
                            <div class="job-media-box">
                                <div class="job-image-wrapper">
                                    <img 
                                        src="uploads/<?php echo htmlspecialchars($row['order_image']); ?>" 
                                        alt="รูปภาพสินค้าที่ต้องหิ้ว" 
                                        class="job-order-img"
                                        onclick="openImageModal(this.src)"
                                        title="คลิกเพื่อดูรูปภาพขนาดใหญ่"
                                    >
                                    <div class="job-image-badge">
                                        <span>🔍 คลิกดูรูปสินค้าขนาดเต็ม (Zoom)</span>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- ส่วนปุ่มจัดการ -->
                            <div class="job-footer-actions">
                                <div class="job-action-left">
                                    <?php if (!empty($row['order_image'])): ?>
                                        <button 
                                            type="button" 
                                            class="btn-view-job-detail" 
                                            onclick="openImageModal('uploads/<?php echo htmlspecialchars($row['order_image']); ?>')"
                                        >
                                            <span>🖼️ ดูรูปสินค้า</span>
                                        </button>
                                    <?php endif; ?>

                                    <!-- ปุ่มเปิดแชทกับลูกค้า -->
                                    <a href="chat_room.php?order_id=<?php echo $row['order_id']; ?>" class="btn-chat-link" style="margin-left: 8px;">
                                        <span>💬</span>
                                        <span>แชทกับลูกค้า</span>
                                    </a>
                                </div>

                                <div class="job-action-right">
                                    <?php if ($is_delivering): ?>
                                        <form method="POST" action="" style="margin: 0;">
                                            <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                            <input type="hidden" name="finish_order" value="1">
                                            <button type="button" class="btn-finish" onclick="triggerConfirmSubmit(this, 'ยืนยันว่าคุณจัดส่งสินค้าถึงมือลูกค้าเรียบร้อยแล้วใช่หรือไม่?', 'ยืนยันจัดส่งสำเร็จ', '✅')">
                                                <span>✅</span>
                                                <span>ยืนยันจัดส่งสำเร็จ (ปิดจ๊อบ)</span>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span style="font-size: 13px; color: var(--text-muted);">🎉 เสร็จสิ้นภารกิจแล้ว ขอบคุณที่ให้บริการค่ะ</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="empty-state-box" style="padding: 36px 20px;">
                        <div class="empty-state-icon" style="font-size: 36px;">🎒✨</div>
                        <h4 class="empty-state-title">คุณยังไม่ได้รับงานไหนเลยค่า</h4>
                        <p class="empty-state-text">เลือกรับออเดอร์ที่ถูกใจจากแท็บ "ฟีดงานเปิดรับ" เพื่อเริ่มสร้างรายได้ได้เลย!</p>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

    </main>

    <!-- Lightbox Modal สำหรับกดดูรูปภาพขนาดใหญ่ -->
    <div id="imageLightboxModal" class="image-modal-backdrop" onclick="closeImageModal()">
        <span class="modal-close-btn">&times;</span>
        <img id="lightboxModalImg" class="image-modal-content" src="" alt="ภาพสินค้าขยายใหญ่">
    </div>

    <!-- Tab Switching & Modal Script -->
    <script>
        // ฟังก์ชันสลับแท็บโดยไม่ต้องเลื่อนหน้าจอลงข้างล่าง
        function switchTab(tabName) {
            const feedSection = document.getElementById('feed-section');
            const jobsSection = document.getElementById('my-jobs-section');
            const tabFeedBtn = document.getElementById('tabFeedBtn');
            const tabJobsBtn = document.getElementById('tabJobsBtn');
            const pageTitle = document.getElementById('pageTitle');
            const pageSubtitle = document.getElementById('pageSubtitle');

            if (tabName === 'jobs') {
                feedSection.style.display = 'none';
                jobsSection.style.display = 'block';
                tabJobsBtn.classList.add('active');
                tabFeedBtn.classList.remove('active');
                if (pageTitle) pageTitle.textContent = 'งานที่คุณกำลังรับผิดชอบ';
                if (pageSubtitle) pageSubtitle.textContent = 'รายการออเดอร์ที่คุณรับงานไว้ จัดส่งถึงมือผู้รับแล้วกดยืนยันปิดจ๊อบได้เลย';
                history.replaceState(null, null, '#jobs');
            } else {
                feedSection.style.display = 'flex';
                jobsSection.style.display = 'none';
                tabFeedBtn.classList.add('active');
                tabJobsBtn.classList.remove('active');
                if (pageTitle) pageTitle.textContent = 'ฟีดงานฝากหิ้วที่เปิดรับ';
                if (pageSubtitle) pageSubtitle.textContent = 'เลือกดูออเดอร์ของเพื่อนๆ ม.บูรพา แล้วกดรับงานไปส่งได้ทันที';
                history.replaceState(null, null, '#feed');
            }
        }

        // ตรวจสอบ URL Hash เมื่อโหลดหน้าเว็บ
        window.addEventListener('DOMContentLoaded', () => {
            if (window.location.hash === '#jobs') {
                switchTab('jobs');
            } else {
                switchTab('feed');
            }
        });

        // ฟังก์ชัน Lightbox Modal ขยายรูป
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

        // Modern Top Toast System (แท็บแจ้งเตือนเล็กๆ ด้านบน)
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

        // Modern Centered Alert System (แท็บแจ้งเตือนตรงกลางหน้าจอ)
        function showCenterAlert(msg, title = 'แจ้งเตือน', icon = '✅') {
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

        // Override default window.alert
        window.alert = function(msg) {
            showCenterAlert(msg, 'แจ้งเตือน', 'ℹ️');
        };

        // Modern Centered Confirmation System (แท็บยืนยันตรงกลางหน้าจอ)
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

        function triggerConfirmSubmit(btn, msg, title, icon) {
            showCenterConfirm(msg, title, icon, () => {
                btn.closest('form').submit();
            });
        }
    </script>

    <!-- Modern Top Notification Toast (แท็บแจ้งเตือนเล็กๆ ด้านบน) -->
    <div id="modernTopToast" class="modern-top-toast">
        <div class="modern-top-toast-icon" id="topToastIcon">👋</div>
        <div class="modern-top-toast-content">
            <span class="modern-top-toast-title" id="topToastTitle">เข้าสู่ระบบสำเร็จ</span>
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
            <div class="modern-alert-icon-wrapper" id="centerAlertIcon">🎉</div>
            <h3 class="modern-alert-title" id="centerAlertTitle">สำเร็จแล้ว!</h3>
            <p class="modern-alert-msg" id="centerAlertMsg">ทำรายการเรียบร้อยแล้วค่า</p>
            <button type="button" class="btn-alert-confirm" onclick="closeCenterAlert()">ตกลง</button>
        </div>
    </div>

    <!-- Check URL Messages for Centered Alerts -->
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'order_finished'): ?>
    <script>
    window.addEventListener('DOMContentLoaded', () => {
        showCenterAlert('สุดยอด! จัดส่งสำเร็จแล้ว ภารกิจลุล่วงและบันทึกรายได้สะสมเรียบร้อยค่ะ', 'จัดส่งสำเร็จ!', '🎉');
        history.replaceState(null, null, 'sender_dashboard.php#jobs');
    });
    </script>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'order_accepted'): ?>
    <script>
    window.addEventListener('DOMContentLoaded', () => {
        showCenterAlert('รับหิ้วออเดอร์นี้เรียบร้อยแล้วค่ะ! ระบบได้เพิ่มงานนี้ลงในแท็บงานที่คุณรับผิดชอบแล้ว', 'รับงานสำเร็จ!', '🚀');
        history.replaceState(null, null, 'sender_dashboard.php#jobs');
    });
    </script>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'login_success'): ?>
    <script>
    window.addEventListener('DOMContentLoaded', () => {
        showTopToast('ยินดีต้อนรับคุณ <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?> เข้าสู่ระบบ BuddyDrop ค่ะ', 'เข้าสู่ระบบสำเร็จ', '👋', 'success');
        history.replaceState(null, null, 'sender_dashboard.php');
    });
    </script>
    <?php endif; ?>
</body>
</html>
