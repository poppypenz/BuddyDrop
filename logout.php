<?php
session_start();
session_destroy(); // ทำลายเซสชัน ลบความจำว่าเคยล็อกอิน
header("Location: login.php"); // เด้งกลับไปหน้าล็อกอิน
?>