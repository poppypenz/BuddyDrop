<?php
$servername = "localhost"; 
$username = "root"; // เปลี่ยนเป็น username ของ Angsila เมื่อเอาขึ้นเซิร์ฟเวอร์จริง
$password = ""; // เปลี่ยนเป็นรหัสผ่านของ Angsila
$dbname = "rubhiew_db"; // ชื่อฐานข้อมูล

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("ระบบขัดข้อง การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}
$conn->set_charset("utf8"); // ให้รองรับภาษาไทย
?>