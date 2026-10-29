<?php
session_start();
require 'connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'sender') {
    header("Location: login.php");
    exit();
}
$sender_id = $_SESSION['user_id'];

// [Update] รับงาน
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accept_order'])) {
    $order_id = $_POST['order_id'];
    $sql_update = "UPDATE Orders SET sender_id='$sender_id', status='รับงานแล้ว/กำลังไปส่ง' WHERE order_id='$order_id'";
    $conn->query($sql_update);
    echo "<script>window.location.href='sender_dashboard.php';</script>";
}

// [Update] จัดส่งสำเร็จปิดจ๊อบ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['finish_order'])) {
    $order_id = $_POST['order_id'];
    $sql_finish = "UPDATE Orders SET status='จัดส่งสำเร็จ' WHERE order_id='$order_id'";
    $conn->query($sql_finish);
    echo "<script>alert('สุดยอด! จัดส่งสำเร็จแล้ว ภารกิจลุล่วง!'); window.location.href='sender_dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head><title>หน้าหลักคนรับหิ้ว - รับหิ้วบางแสน</title></head>
<body>
    <h2>🛵 ยินดีต้อนรับคนรับหิ้วสุดซิ่ง: <?php echo $_SESSION['username']; ?></h2>
    <a href="logout.php"><button>ออกจากระบบ</button></a>
    <hr>

    <h3>📋 งานที่เปิดรับ (รอคนไปหิ้ว)</h3>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr bgcolor="#ffe6e6">
            <th>รหัส</th><th>รายละเอียด</th><th>สถานที่ส่ง</th><th>ค่าหิ้ว</th><th>จัดการ</th>
        </tr>
        <?php
        $sql_pending = "SELECT Orders.*, Locations.name_place FROM Orders 
                        JOIN Locations ON Orders.location_id = Locations.location_id 
                        WHERE status='รอผู้รับงาน'";
        $result_pending = $conn->query($sql_pending);
        if ($result_pending->num_rows > 0) {
            while($row = $result_pending->fetch_assoc()) {
                echo "<tr>
                        <td>".$row['order_id']."</td><td>".$row['order_detail']."</td><td>".$row['name_place']."</td><td>".$row['errand_fee']." บาท</td>
                        <td>
                            <form method='POST' action=''>
                                <input type='hidden' name='order_id' value='".$row['order_id']."'>
                                <button type='submit' name='accept_order'>กดรับงาน 🚀</button>
                            </form>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='5' align='center'>ตอนนี้ยังไม่มีงานเลยค่าา</td></tr>";
        }
        ?>
    </table>
    <hr>
    
    <h3>📦 งานที่คุณกำลังรับผิดชอบ</h3>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr bgcolor="#e6ffe6">
            <th>รหัส</th><th>รายละเอียด</th><th>สถานะ</th><th>จัดการ</th>
        </tr>
        <?php
        $sql_my_jobs = "SELECT * FROM Orders WHERE sender_id='$sender_id' ORDER BY order_id DESC";
        $result_my_jobs = $conn->query($sql_my_jobs);
        if ($result_my_jobs->num_rows > 0) {
            while($row = $result_my_jobs->fetch_assoc()) {
                echo "<tr>
                        <td>".$row['order_id']."</td>
                        <td>".$row['order_detail']."</td>
                        <td><b>".$row['status']."</b></td>
                        <td>";
                // โชว์ปุ่มจัดส่งสำเร็จเฉพาะงานที่กำลังทำอยู่
                if ($row['status'] == 'รับงานแล้ว/กำลังไปส่ง') {
                    echo "<form method='POST' action=''>
                            <input type='hidden' name='order_id' value='".$row['order_id']."'>
                            <button type='submit' name='finish_order'>✅ จัดส่งสำเร็จ</button>
                          </form>";
                } else {
                    echo "-";
                }
                echo "</td></tr>";
            }
        } else {
            echo "<tr><td colspan='4' align='center'>คุณยังไม่ได้รับงานไหนเลยค่า</td></tr>";
        }
        ?>
    </table>
</body>
</html>