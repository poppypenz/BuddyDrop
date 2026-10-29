<?php
require 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    // เข้ารหัสพาสเวิร์ดเพื่อความปลอดภัย อาจารย์เห็นแล้วต้องปลื้มค่ะ!
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    $role = $_POST['role'];
    $phone = $_POST['phone'];

    $sql = "INSERT INTO User (username, password, role, phone) VALUES ('$username', '$password', '$role', '$phone')";
    
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('สมัครสมาชิกสำเร็จ 1,000,000%!'); window.location.href='login.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>สมัครสมาชิก - รับหิ้วบางแสน</title></head>
<body>
    <h2>สมัครสมาชิก 🛵</h2>
    <form method="POST" action="">
        <label>ชื่อผู้ใช้งาน:</label><br>
        <input type="text" name="username" required><br><br>
        
        <label>รหัสผ่าน:</label><br>
        <input type="password" name="password" required><br><br>
        
        <label>เลือกบทบาท:</label><br>
        <select name="role">
            <option value="customer">ลูกค้า (ฝากซื้อ)</option>
            <option value="sender">คนรับหิ้ว (Rider)</option>
        </select><br><br>
        
        <label>เบอร์โทรศัพท์:</label><br>
        <input type="text" name="phone" required><br><br>
        
        <button type="submit">สมัครสมาชิก</button>
    </form>
</body>
</html>