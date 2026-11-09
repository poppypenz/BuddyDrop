<?php
session_start();
require 'connect.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header("Location: customer_dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = (int)$_GET['order_id'];

// ดึงข้อมูลออเดอร์มาโชว์บนหัวแชท จะได้รู้ว่าคุยงานไหนอยู่
$sql_order = "SELECT * FROM orders WHERE order_id = '$order_id'";
$result = $conn->query($sql_order);
if ($result->num_rows == 0) {
    die("ไม่พบข้อมูลออเดอร์นี้ในจักรวาลค่ะ!");
}
$order = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ห้องแชท - ออเดอร์ #<?php echo $order_id; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&family=Prompt:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=2.3">
    <script src="assets/js/theme.js?v=2.3"></script>
    <style>
        body { font-family: 'Prompt', sans-serif; background: #f0f2f5; margin: 0; padding: 20px; display: flex; justify-content: center; }
        .chat-container { width: 100%; max-width: 450px; background: #fff; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; display: flex; flex-direction: column; height: 80vh; }
        .chat-header { background: #1877f2; color: #fff; padding: 16px; text-align: center; font-weight: 600; position: relative; }
        .back-btn { position: absolute; left: 16px; color: #fff; text-decoration: none; font-size: 14px; }
        .chat-box { flex: 1; padding: 16px; overflow-y: auto; background: #eef1f6; display: flex; flex-direction: column; gap: 10px; }
        .msg-bubble { max-width: 75%; padding: 10px 14px; border-radius: 18px; font-size: 14px; position: relative; word-wrap: break-word; }
        .msg-time { font-size: 10px; color: #999; margin-top: 4px; text-align: right; }
        /* ข้อความฝั่งคนอื่น (สีขาว ซ้าย) */
        .msg-other { background: #fff; align-self: flex-start; border-bottom-left-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        /* ข้อความของเราเอง (สีน้ำเงิน ขวา) */
        .msg-mine { background: #1877f2; color: #fff; align-self: flex-end; border-bottom-right-radius: 4px; }
        .msg-mine .msg-time { color: #d1e4ff; }
        
        .chat-input-area { padding: 14px; background: #fff; border-top: 1px solid #ddd; display: flex; gap: 10px; }
        .chat-input { flex: 1; padding: 12px; border: 1px solid #ccc; border-radius: 20px; outline: none; font-family: 'Prompt', sans-serif; }
        .btn-send { background: #1877f2; color: #fff; border: none; padding: 0 18px; border-radius: 20px; font-weight: 600; cursor: pointer; transition: 0.2s; }
        .btn-send:hover { background: #166fe5; }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="chat-header">
        <a href="javascript:history.back()" class="back-btn">◀ กลับ</a>
        💬 แชทออเดอร์ #<?php echo $order_id; ?>
        <button type="button" class="theme-toggle-btn chat-theme-toggle" onclick="toggleTheme()" title="เปลี่ยนธีม 🌙">
            <span class="theme-toggle-icon">🌙</span>
        </button>
    </div>

    <!-- พื้นที่แสดงข้อความ -->
    <div class="chat-box" id="chatBox">
        <!-- ข้อความจะถูกโหลดมาใส่ตรงนี้ด้วย JavaScript -->
    </div>

    <!-- ช่องพิมพ์ข้อความ -->
    <div class="chat-input-area">
        <input type="text" id="messageInput" class="chat-input" placeholder="พิมพ์ข้อความที่นี่..." onkeypress="handleEnter(event)">
        <button onclick="sendMessage()" class="btn-send">ส่ง 🚀</button>
    </div>
</div>

<script>
    const orderId = <?php echo $order_id; ?>;
    const chatBox = document.getElementById('chatBox');
    const messageInput = document.getElementById('messageInput');

    // ฟังก์ชันโหลดข้อความจาก Database
    function loadMessages() {
        fetch(`chat_api.php?action=load&order_id=${orderId}`)
            .then(res => res.json())
            .then(data => {
                chatBox.innerHTML = ''; // ล้างแชทเก่าก่อน
                data.forEach(msg => {
                    // แยกว่าข้อความของเรา หรือของอีกฝั่ง
                    const bubbleClass = msg.is_mine ? 'msg-mine' : 'msg-other';
                    const div = document.createElement('div');
                    div.className = `msg-bubble ${bubbleClass}`;
                    div.innerHTML = `
                        <div>${msg.message}</div>
                        <div class="msg-time">${msg.time}</div>
                    `;
                    chatBox.appendChild(div);
                });
                // เลื่อนหน้าจอลงล่างสุดอัตโนมัติ
                chatBox.scrollTop = chatBox.scrollHeight;
            });
    }

    // ฟังก์ชันส่งข้อความ
    function sendMessage() {
        const text = messageInput.value.trim();
        if (text === '') return;

        const formData = new FormData();
        formData.append('action', 'send');
        formData.append('order_id', orderId);
        formData.append('message', text);

        fetch('chat_api.php', {
            method: 'POST',
            body: formData
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                messageInput.value = ''; // ล้างช่องพิมพ์
                loadMessages(); // โหลดข้อความใหม่ทันที
            }
        });
    }

    function handleEnter(e) {
        if (e.key === 'Enter') sendMessage();
    }

    // โหลดข้อความครั้งแรกตอนเปิดหน้าเว็บ
    loadMessages();
    
    // ตั้งเวลาให้รีเฟรชข้อความอัตโนมัติทุกๆ 2 วินาที (เพื่อให้เห็นตอนอีกฝั่งตอบกลับ)
    setInterval(loadMessages, 2000);

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