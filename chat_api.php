<?php
session_start();
require 'connect.php';

// ดักไว้ก่อน ถ้าไม่ได้ล็อกอิน ห้ามใช้งานเด็ดขาด!
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$user_id = $_SESSION['user_id'];
// รับค่าออเดอร์และคำสั่ง (ส่งข้อความ หรือ โหลดข้อความ)
$order_id = isset($_REQUEST['order_id']) ? (int)$_REQUEST['order_id'] : 0;
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// 1. ระบบส่งข้อความ (Save to Database)
if ($action === 'send') {
    $message = $conn->real_escape_string(trim($_POST['message']));
    if ($message !== '' && $order_id > 0) {
        $sql = "INSERT INTO chat_messages (order_id, user_id, message) VALUES ('$order_id', '$user_id', '$message')";
        $conn->query($sql);
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error"]);
    }
    exit();
}

// 2. ระบบโหลดข้อความ (Fetch from Database)
if ($action === 'load') {
    $sql = "SELECT * FROM chat_messages WHERE order_id = '$order_id' ORDER BY created_at ASC";
    $result = $conn->query($sql);
    $messages = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // เช็กว่าข้อความนี้เป็นของเรา (is_mine) หรือของอีกฝั่ง
            $row['is_mine'] = ($row['user_id'] == $user_id) ? true : false;
            // จัดฟอร์แมตเวลาให้ดูง่ายๆ เช่น 14:30
            $row['time'] = date('H:i', strtotime($row['created_at']));
            $messages[] = $row;
        }
    }
    echo json_encode($messages);
    exit();
}
?>