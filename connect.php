<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rubhiew_db";

// คำสั่งสร้างการเชื่อมต่อฐานข้อมูลตัวจริงเสียงจริง!
$conn = new mysqli($servername, $username, $password, $dbname);

// เช็คว่าเชื่อมต่อสำเร็จมั้ย
if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}
?>