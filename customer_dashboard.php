<?php
session_start();
require 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$customer_id = $_SESSION['user_id'];

// [Create] สร้างออเดอร์พร้อมอัปโหลดรูปภาพ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_order'])) {
    $location_id = $_POST['location_id'];
    $order_detail = $_POST['order_detail'];
    $errand_fee = $_POST['errand_fee'];
    $order_image = "";

    // จัดการอัปโหลดไฟล์รูปภาพ
    if (isset($_FILES['order_image']) && $_FILES['order_image']['error'] == 0) {
        $ext = pathinfo($_FILES['order_image']['name'], PATHINFO_EXTENSION);
        $new_name = uniqid('order_') . '.' . $ext;
        $target = 'uploads/' . $new_name;
        if (move_uploaded_file($_FILES['order_image']['tmp_name'], $target)) {
            $order_image = $new_name;
        }
    }

    $sql_insert = "INSERT INTO orders (customer_id, location_id, order_detail, order_image, errand_fee, status) 
                   VALUES ('$customer_id', '$location_id', '$order_detail', '$order_image', '$errand_fee', 'รอผู้รับงาน')";
    $conn->query($sql_insert);
    header("Location: customer_dashboard.php?msg=order_created");
    exit();
}

// [Delete] ยกเลิกออเดอร์
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    $sql_delete = "DELETE FROM orders WHERE order_id='$order_id' AND status='รอผู้รับงาน'";
    $conn->query($sql_delete);
    header("Location: customer_dashboard.php?msg=order_cancelled");
    exit();
}

// [Update] รับงานหิ้วจากฟีดหน้าหลัก
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accept_order'])) {
    $order_id = $_POST['order_id'];
    $sql_update = "UPDATE orders SET sender_id='$customer_id', status='รับงานแล้ว/กำลังไปส่ง' WHERE order_id='$order_id'";
    $conn->query($sql_update);
    header("Location: customer_dashboard.php?msg=order_accepted");
    exit();
}

// [Update] จัดส่งสำเร็จ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['finish_order'])) {
    $order_id = $_POST['order_id'];
    $sql_finish = "UPDATE orders SET status='จัดส่งสำเร็จ' WHERE order_id='$order_id'";
    $conn->query($sql_finish);
    header("Location: customer_dashboard.php?msg=order_finished");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BuddyDrop - หน้าหลักฟีดออเดอร์ (Home Feed)</title>
    
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
                <!-- สลับไปหน้ารับงาน Rider -->
                <a href="sender_dashboard.php" class="mode-nav-btn rider-mode">
                    <span>🛵 โหมดคนรับหิ้ว</span>
                </a>

                <!-- ปุ่มสลับธีม ดวงจันทร์ (Dark Mode) -->
                <button type="button" class="theme-toggle-btn" onclick="toggleTheme()" title="เปลี่ยนธีม สว่าง / ดำ-ทอง 🌙">
                    <span class="theme-toggle-icon">🌙</span>
                    <span class="theme-toggle-text">ดวงจันทร์</span>
                </button>

                <!-- โปรไฟล์ผู้ใช้งาน -->
                <a href="profile.php" class="user-profile-pill" title="คลิกเพื่อดูโปรไฟล์และประวัติกิจกรรมทั้งหมด">
                    <span class="user-avatar">👤</span>
                    <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </a>

                <!-- ปุ่มออกจากระบบ -->
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

        <!-- ==========================================
             FACEBOOK-STYLE CREATE POST BOX (กล่องสร้างโพสต์ด้านบนสุด)
             ========================================== -->
        <div class="fb-create-post-card">
            <div class="fb-prompt-row">
                <div class="fb-user-avatar">👤</div>
                <div class="fb-fake-input" onclick="openCreatePostModal()">
                    คุณอยากฝากซื้ออะไรวันนี้, <?php echo htmlspecialchars($_SESSION['username']); ?>?
                </div>
            </div>

            <div class="fb-actions-row">
                <button type="button" class="fb-action-btn" onclick="openCreatePostModal()">
                    <span>🖼️</span>
                    <span>แนบรูปสินค้า</span>
                </button>
                <button type="button" class="fb-action-btn" onclick="openCreatePostModal()">
                    <span>📍</span>
                    <span>จุดนัดรับ</span>
                </button>
                <button type="button" class="fb-action-btn" onclick="openCreatePostModal()">
                    <span>💵</span>
                    <span>ระบุค่าหิ้ว</span>
                </button>
            </div>
        </div>

        <!-- ==========================================
             FEED FILTER TABS (ตัวกรองฟีดออเดอร์)
             ========================================== -->
        <div class="feed-filter-bar">
            <button type="button" class="feed-filter-pill active" onclick="filterFeed('all', this)">
                <span>🌐 โพสต์ทั้งหมด</span>
            </button>
            <button type="button" class="feed-filter-pill" onclick="filterFeed('waiting', this)">
                <span>⏳ รอคนรับหิ้ว</span>
            </button>
            <button type="button" class="feed-filter-pill" onclick="filterFeed('mine', this)">
                <span>🛍️ โพสต์ของคุณ</span>
            </button>
            <button type="button" class="feed-filter-pill" onclick="filterFeed('myjobs', this)">
                <span>🛵 งานที่คุณรับหิ้ว</span>
            </button>
        </div>

        <!-- ==========================================
             SOCIAL MEDIA NEWS FEED (แสดงโพสต์ของตัวเองและคนอื่น)
             ========================================== -->
        <div class="order-feed" id="mainHomeFeed">
            <?php
            // ดึงออเดอร์ทั้งหมด ทั้งของตัวเองและของคนอื่น (เรียงใหม่ล่าสุดขึ้นก่อน)
            $sql_all_orders = "SELECT orders.*, locations.name_place, user.username 
                               FROM orders 
                               LEFT JOIN locations ON orders.location_id = locations.location_id 
                               LEFT JOIN user ON orders.customer_id = user.user_id 
                               ORDER BY orders.order_id DESC";
            $result_all_orders = $conn->query($sql_all_orders);

            if ($result_all_orders && $result_all_orders->num_rows > 0) {
                while($row = $result_all_orders->fetch_assoc()) {
                    $is_mine = ($row['customer_id'] == $customer_id);
                    $is_my_job = (!empty($row['sender_id']) && $row['sender_id'] == $customer_id);
                    $is_waiting = ($row['status'] == 'รอผู้รับงาน');
                    $is_delivering = ($row['status'] == 'รับงานแล้ว/กำลังไปส่ง');
                    $is_completed = ($row['status'] == 'จัดส่งสำเร็จ');
                    $post_time = !empty($row['created_at']) ? $row['created_at'] : 'เพิ่งโพสต์เมื่อสักครู่';

                    // Data-filter tag สำหรับการกรองฟีดด้วย JS
                    $filter_tags = 'all ';
                    if ($is_waiting) $filter_tags .= 'waiting ';
                    if ($is_mine) $filter_tags .= 'mine ';
                    if ($is_my_job) $filter_tags .= 'myjobs ';
                    ?>
                    <!-- Component การ์ดออเดอร์โซเชียลมีเดีย -->
                    <article class="order-card feed-item" data-filter="<?php echo trim($filter_tags); ?>">
                        
                        <!-- 1. ส่วนหัวการ์ด (Avatar, ชื่อผู้โพสต์, เวลา, ป้ายสถานะ) -->
                        <div class="card-header">
                            <div class="user-profile">
                                <div class="avatar-placeholder"><?php echo $is_mine ? '⭐' : '👤'; ?></div>
                                <div class="user-info">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <h4 class="user-name">
                                            <?php echo htmlspecialchars($row['username']); ?>
                                            <?php if ($is_mine): ?>
                                                <span class="badge-self-post">(คุณ)</span>
                                            <?php endif; ?>
                                        </h4>
                                    </div>
                                    <span class="post-time">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                        <?php echo htmlspecialchars($post_time); ?>
                                    </span>
                                </div>
                            </div>

                            <!-- ป้ายสถานะออเดอร์ -->
                            <div>
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
                                    <span class="status-badge"><?php echo htmlspecialchars($row['status']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- 2. ข้อความรายละเอียดสินค้า -->
                        <div class="card-content">
                            <p class="order-description"><?php echo nl2br(htmlspecialchars($row['order_detail'])); ?></p>
                        </div>

                        <!-- 3. รูปภาพสินค้าขนาดใหญ่ตรงกลาง (คลิกดูภาพขยายได้) -->
                        <?php if (!empty($row['order_image'])): ?>
                        <div class="card-media">
                            <img 
                                src="uploads/<?php echo htmlspecialchars($row['order_image']); ?>" 
                                alt="รูปภาพสินค้า" 
                                class="order-image" 
                                loading="lazy"
                                onclick="openImageModal(this.src)"
                                title="คลิกเพื่อดูรูปขนาดใหญ่"
                            >
                        </div>
                        <?php endif; ?>

                        <!-- 4. แถบสถานที่นัดรับ และ แถบราคาค่าหิ้ว -->
                        <div class="card-meta-grid">
                            <div class="meta-box location-box">
                                <div class="meta-icon-wrapper">📍</div>
                                <div class="meta-content">
                                    <span class="meta-label">สถานที่นัดรับ</span>
                                    <span class="location-name" title="<?php echo htmlspecialchars($row['name_place']); ?>">
                                        <?php echo !empty($row['name_place']) ? htmlspecialchars($row['name_place']) : 'ไม่ได้ระบุ'; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="meta-box fee-box">
                                <div class="meta-icon-wrapper">💵</div>
                                <div class="meta-content">
                                    <span class="meta-label"><?php echo $is_mine ? 'ค่าหิ้วที่จ่าย' : 'ค่าหิ้วที่คุณจะได้'; ?></span>
                                    <span class="fee-amount">+<?php echo number_format((float)$row['errand_fee'], 2); ?> ฿</span>
                                </div>
                            </div>
                        </div>

                        <!-- 5. แถบปุ่มกดแอ็กชัน (ตามสถานะและความสัมพันธ์กับโพสต์) -->
                        <div class="card-actions" style="display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                            
                            <!-- กรณีที่เป็นโพสต์ของตัวเอง -->
                            <?php if ($is_mine): ?>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <?php if (!$is_waiting): ?>
                                        <a href="chat_room.php?order_id=<?php echo $row['order_id']; ?>" class="btn-chat-link">
                                            <span>💬</span>
                                            <span>แชทกับคนรับหิ้ว</span>
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <?php if ($is_waiting): ?>
                                        <form method="POST" action="" style="margin: 0;">
                                            <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                            <input type="hidden" name="cancel_order" value="1">
                                            <button type="button" class="btn-cancel-order" onclick="triggerConfirmSubmit(this, 'แน่ใจนะว่าจะยกเลิกออเดอร์นี้?', 'ยืนยันการยกเลิก', '🗑️')">
                                                <span>✕ ยกเลิกออเดอร์</span>
                                            </button>
                                        </form>
                                    <?php elseif ($is_completed): ?>
                                        <span style="font-size: 13px; color: var(--success-text); font-weight: 600;">🎉 ออเดอร์ของคุณจัดส่งสำเร็จแล้ว</span>
                                    <?php else: ?>
                                        <span style="font-size: 13px; color: var(--primary); font-weight: 600;">🛵 มีเพื่อนกำลังไปส่งให้คุณค่ะ</span>
                                    <?php endif; ?>
                                </div>

                            <!-- กรณีที่เป็นโพสต์ของคนอื่น -->
                            <?php else: ?>
                                
                                <!-- ถ้าออเดอร์ยังรอคนรับหิ้ว: แสดงปุ่มรับหิ้ว -->
                                <?php if ($is_waiting): ?>
                                    <form method="POST" action="" style="margin: 0; width: 100%;">
                                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                        <input type="hidden" name="accept_order" value="1">
                                        <button type="button" class="btn-accept" onclick="triggerConfirmSubmit(this, 'คุณต้องการรับหิ้วออเดอร์นี้ใช่หรือไม่? เมื่อกดยืนยันแล้ว คุณจะต้องไปซื้อและนำส่งสินค้าให้ลูกค้านะคะ', 'ยืนยันการรับหิ้ว', '🚀')">
                                            <span>🚀</span>
                                            <span>รับหิ้วออเดอร์นี้ (+<?php echo number_format((float)$row['errand_fee'], 2); ?> ฿)</span>
                                        </button>
                                    </form>

                                <!-- ถ้าเราเป็นคนรับหิ้วออเดอร์นี้ -->
                                <?php elseif ($is_my_job): ?>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <a href="chat_room.php?order_id=<?php echo $row['order_id']; ?>" class="btn-chat-link">
                                            <span>💬</span>
                                            <span>แชทกับลูกค้า</span>
                                        </a>
                                    </div>

                                    <div>
                                        <?php if ($is_delivering): ?>
                                            <form method="POST" action="" style="margin: 0;">
                                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                                <input type="hidden" name="finish_order" value="1">
                                                <button type="button" class="btn-finish" onclick="triggerConfirmSubmit(this, 'ยืนยันว่าคุณจัดส่งสินค้าถึงมือลูกค้าเรียบร้อยแล้วใช่หรือไม่?', 'ยืนยันจัดส่งสำเร็จ', '✅')">
                                                    <span>✅</span>
                                                    <span>ยืนยันจัดส่งสำเร็จ</span>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span style="font-size: 13px; color: var(--text-muted);">🎉 ภารกิจเสร็จสิ้น</span>
                                        <?php endif; ?>
                                    </div>

                                <!-- ถ้าคนอื่นเป็นคนรับหิ้วไปแล้ว -->
                                <?php else: ?>
                                    <span style="font-size: 13px; color: var(--text-muted);">
                                        <?php echo $is_completed ? '✅ ออเดอร์นี้จัดส่งสำเร็จแล้ว' : '🛵 มีเพื่อนรับหิ้วออเดอร์นี้ไปแล้วค่ะ'; ?>
                                    </span>
                                <?php endif; ?>

                            <?php endif; ?>

                        </div>

                    </article>
                    <?php
                }
            } else {
                ?>
                <div class="empty-state-box">
                    <div class="empty-state-icon">🛵💨</div>
                    <h3 class="empty-state-title">ยังไม่มีโพสต์ออเดอร์ในระบบ</h3>
                    <p class="empty-state-text">คลิกที่กล่องด้านบนเพื่อเป็นคนแรกที่สร้างโพสต์ฝากซื้อได้เลยค่ะ!</p>
                </div>
                <?php
            }
            ?>
        </div>

    </main>

    <!-- ==========================================
         FACEBOOK-STYLE CREATE POST MODAL (หน้าต่างสร้างโพสต์แบบ Facebook)
         ========================================== -->
    <div class="fb-modal-backdrop" id="createPostModalBackdrop" onclick="handleBackdropClick(event)">
        <div class="fb-modal-container">
            
            <div class="fb-modal-header">
                <h3 class="fb-modal-title">สร้างโพสต์ฝากซื้อ</h3>
                <button type="button" class="fb-btn-close-modal" onclick="closeCreatePostModal()">✕</button>
            </div>

            <!-- ฟอร์มสร้างโพสต์ (Method POST & enctype multipart/form-data) -->
            <form method="POST" action="" enctype="multipart/form-data" style="margin: 0; display: flex; flex-direction: column; overflow: hidden;">
                <div class="fb-modal-body">
                    
                    <!-- User Row -->
                    <div class="fb-modal-user">
                        <div class="fb-user-avatar" style="width: 40px; height: 40px; font-size: 18px;">👤</div>
                        <div>
                            <div class="fb-modal-user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                            <span class="fb-public-badge">🌐 โพสต์สาธารณะใน ม.บูรพา</span>
                        </div>
                    </div>

                    <!-- Textarea Prompt -->
                    <div>
                        <textarea 
                            name="order_detail" 
                            id="order_detail" 
                            class="fb-textarea-prompt" 
                            required 
                            placeholder="คุณอยากฝากซื้ออะไรวันนี้? (ระบุชื่อร้าน เมนู จำนวน หรือข้อความเพิ่มเติม...)"
                        ></textarea>
                    </div>

                    <!-- Image Dropzone (Upload) -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <div class="dropzone-box" id="dropzoneBox">
                            <input type="file" id="order_image" name="order_image" accept="image/*" class="file-input-hidden">
                            <div class="dropzone-icon" style="width: 36px; height: 36px; font-size: 18px;">🖼️</div>
                            <div class="dropzone-prompt" style="font-size: 13px;">แนบรูปภาพสินค้า <span>(คลิกหรือลากวาง)</span></div>
                        </div>

                        <!-- Live Preview Box -->
                        <div class="preview-container" id="previewContainer">
                            <img id="previewImage" src="" alt="ตัวอย่างรูป" class="preview-thumbnail">
                            <div class="preview-meta">
                                <div id="previewFilename" class="preview-filename"></div>
                                <div id="previewFilesize" class="preview-size"></div>
                            </div>
                            <button type="button" id="btnRemoveImage" class="btn-remove-preview">✕ ลบ</button>
                        </div>
                    </div>

                    <!-- สถานที่จัดส่ง (Location) -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="location_id" style="font-size: 13px;">
                            <span>📍 สถานที่นัดรับ</span>
                            <span class="required-mark">*</span>
                        </label>
                        <select id="location_id" name="location_id" class="form-control" required>
                            <option value="">-- กรุณาเลือกสถานที่นัดรับ --</option>
                            <?php
                            $result_loc = $conn->query("SELECT * FROM locations");
                            if ($result_loc) {
                                while($row_l = $result_loc->fetch_assoc()) {
                                    echo "<option value='".$row_l['location_id']."'>📍 ".$row_l['name_place']."</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <!-- ค่าหิ้ว (Errand Fee) -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="errand_fee" style="font-size: 13px;">
                            <span>💵 ค่าหิ้ว / ค่าตอบแทน (บาท)</span>
                            <span class="required-mark">*</span>
                        </label>
                        <div class="fee-input-wrapper">
                            <span class="fee-currency-prefix">฿</span>
                            <input 
                                type="number" 
                                id="errand_fee" 
                                name="errand_fee" 
                                class="form-control input-fee" 
                                required 
                                min="1" 
                                step="any" 
                                placeholder="เช่น 30"
                            >
                            <span class="fee-currency-suffix">บาท</span>
                        </div>

                        <!-- Preset Chips -->
                        <div class="preset-fee-chips">
                            <span style="font-size: 11px; color: var(--text-muted);">เลือกด่วน:</span>
                            <button type="button" class="preset-chip-btn" onclick="setFee(20)">+20 ฿</button>
                            <button type="button" class="preset-chip-btn" onclick="setFee(30)">+30 ฿</button>
                            <button type="button" class="preset-chip-btn" onclick="setFee(50)">+50 ฿</button>
                            <button type="button" class="preset-chip-btn" onclick="setFee(80)">+80 ฿</button>
                            <button type="button" class="preset-chip-btn" onclick="setFee(100)">+100 ฿</button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" name="create_order" class="btn-submit-order" style="margin-top: 8px;">
                        <span>🚀</span>
                        <span>โพสต์ออเดอร์เลย!</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- Lightbox Modal สำหรับกดดูรูปภาพขนาดใหญ่ -->
    <div id="imageLightboxModal" class="image-modal-backdrop" onclick="closeImageModal()">
        <span class="modal-close-btn">&times;</span>
        <img id="lightboxModalImg" class="image-modal-content" src="" alt="ภาพสินค้าขยายใหญ่">
    </div>

    <!-- Client-side Scripts -->
    <script>
        // Preset fee helper
        function setFee(amount) {
            const feeInput = document.getElementById('errand_fee');
            if (feeInput) {
                feeInput.value = amount;
                feeInput.focus();
            }
        }

        // Facebook Create Post Modal
        const modalBackdrop = document.getElementById('createPostModalBackdrop');
        const orderDetailInput = document.getElementById('order_detail');

        function openCreatePostModal() {
            if (modalBackdrop) {
                modalBackdrop.style.display = 'flex';
                setTimeout(() => {
                    if (orderDetailInput) orderDetailInput.focus();
                }, 100);
            }
        }

        function closeCreatePostModal() {
            if (modalBackdrop) {
                modalBackdrop.style.display = 'none';
            }
        }

        function handleBackdropClick(e) {
            if (e.target === modalBackdrop) {
                closeCreatePostModal();
            }
        }

        // Feed Filter
        function filterFeed(filterType, element) {
            document.querySelectorAll('.feed-filter-pill').forEach(pill => pill.classList.remove('active'));
            if (element) element.classList.add('active');

            const items = document.querySelectorAll('.feed-item');
            items.forEach(item => {
                const filters = (item.getAttribute('data-filter') || '').split(' ');
                if (filterType === 'all' || filters.includes(filterType)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Live Image Preview
        const fileInput = document.getElementById('order_image');
        const dropzoneBox = document.getElementById('dropzoneBox');
        const previewContainer = document.getElementById('previewContainer');
        const previewImage = document.getElementById('previewImage');
        const previewFilename = document.getElementById('previewFilename');
        const previewFilesize = document.getElementById('previewFilesize');
        const btnRemoveImage = document.getElementById('btnRemoveImage');

        function handleFileSelect(file) {
            if (!file) return;
            if (!file.type.startsWith('image/')) {
                showCenterAlert('กรุณาเลือกไฟล์ที่เป็นรูปภาพเท่านั้นค่ะ (JPG, PNG, WEBP, GIF)', 'ชนิดไฟล์ไม่ถูกต้อง', '⚠️');
                fileInput.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewFilename.textContent = file.name;
                previewFilesize.textContent = (file.size / 1024).toFixed(1) + ' KB';
                previewContainer.style.display = 'flex';
                dropzoneBox.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }

        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    handleFileSelect(this.files[0]);
                }
            });
        }

        if (btnRemoveImage) {
            btnRemoveImage.addEventListener('click', function() {
                fileInput.value = '';
                previewImage.src = '';
                previewFilename.textContent = '';
                previewFilesize.textContent = '';
                previewContainer.style.display = 'none';
                dropzoneBox.style.display = 'block';
            });
        }

        // Lightbox Modal
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

        // Override default window.alert ให้ใช้ Modal ตรงกลางหน้าจออัตโนมัติ
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
            <div class="modern-alert-icon-wrapper" id="centerAlertIcon">✅</div>
            <h3 class="modern-alert-title" id="centerAlertTitle">สำเร็จแล้ว!</h3>
            <p class="modern-alert-msg" id="centerAlertMsg">ทำรายการเรียบร้อยแล้วค่า</p>
            <button type="button" class="btn-alert-confirm" onclick="closeCenterAlert()">ตกลง</button>
        </div>
    </div>

    <!-- Check URL Messages for Centered Alerts -->
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'order_cancelled'): ?>
    <script>
    window.addEventListener('DOMContentLoaded', () => {
        showCenterAlert('ยกเลิกออเดอร์เรียบร้อยแล้วค่า!', 'ยกเลิกสำเร็จ', '🗑️');
        history.replaceState(null, null, 'customer_dashboard.php');
    });
    </script>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'order_finished'): ?>
    <script>
    window.addEventListener('DOMContentLoaded', () => {
        showCenterAlert('จัดส่งสำเร็จแล้ว ภารกิจลุล่วงและบันทึกประวัติเรียบร้อยค่ะ!', 'จัดส่งสำเร็จ', '🎉');
        history.replaceState(null, null, 'customer_dashboard.php');
    });
    </script>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'order_created'): ?>
    <script>
    window.addEventListener('DOMContentLoaded', () => {
        showCenterAlert('โพสต์หาคนรับหิ้วสำเร็จแล้วค่ะ! ระบบได้นำโพสต์ของคุณขึ้นสู่หน้าฟีดเรียบร้อยแล้ว', 'สร้างออเดอร์สำเร็จ!', '🎉');
        history.replaceState(null, null, 'customer_dashboard.php');
    });
    </script>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'order_accepted'): ?>
    <script>
    window.addEventListener('DOMContentLoaded', () => {
        showCenterAlert('รับหิ้วออเดอร์นี้เรียบร้อยแล้วค่ะ! คุณสามารถเปิดแชทคุยกับลูกค้าเพื่อสอบถามข้อมูลเพิ่มเติมได้เลย', 'รับงานสำเร็จ!', '🚀');
        history.replaceState(null, null, 'customer_dashboard.php');
    });
    </script>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'login_success'): ?>
    <script>
    window.addEventListener('DOMContentLoaded', () => {
        showTopToast('ยินดีต้อนรับคุณ <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?> เข้าสู่ระบบ BuddyDrop ค่ะ', 'เข้าสู่ระบบสำเร็จ', '👋', 'success');
        history.replaceState(null, null, 'customer_dashboard.php');
    });
    </script>
    <?php endif; ?>
</body>
</html>
