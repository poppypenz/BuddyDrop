<?php
session_start();
require 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM User WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // ตรวจสอบรหัสผ่านที่เข้ารหัสไว้
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            
            // แยกว่าล็อกอินแล้วจะไปหน้าไหน
            if ($row['role'] == 'customer') {
                header("Location: customer_dashboard.php");
            } else {
                header("Location: sender_dashboard.php");
            }
    } else {
        echo "<script>alert('ไม่พบชื่อผู้ใช้งานนี้ในจักรวาลค่ะ');</script>";
    }
}
}
?>
<!DOCTYPE html>
<html>
<head><title>เข้าสู่ระบบ - รับหิ้วบางแสน</title></head>
<body>
    <h2>เข้าสู่ระบบ 🚀</h2>
    <form method="POST" action="">
        <label>ชื่อผู้ใช้งาน:</label><br>
        <input type="text" name="username" required><br><br>
        <label>รหัสผ่าน:</label><br>
        <input type="password" name="password" required><br><br>
        <button type="submit">ล็อกอิน</button>
    </form>
    <p>ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิกเลย!</a></p>
</body>
</html>