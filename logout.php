<?php
session_start();
session_destroy(); // ทำลายเซสชัน ลบความจำว่าเคยล็อกอิน
header("Location: login.php?msg=logged_out"); // เด้งกลับไปหน้าล็อกอินพร้อมแจ้งเตือน
exit();
?>