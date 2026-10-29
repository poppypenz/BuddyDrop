<?php
session_start();
require 'connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php");
    exit();
}
$customer_id = $_SESSION['user_id'];

// [Create] สร้างออเดอร์
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_order'])) {
    $location_id = $_POST['location_id'];
    $order_detail = $_POST['order_detail'];
    $errand_fee = $_POST['errand_fee'];
    $sql_insert = "INSERT INTO Orders (customer_id, location_id, order_detail, errand_fee, status) 
                   VALUES ('$customer_id', '$location_id', '$order_detail', '$errand_fee', 'รอผู้รับงาน')";
    $conn->query($sql_insert);
    echo "<script>window.location.href='customer_dashboard.php';</script>";
}

// [Delete] ยกเลิกออเดอร์
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    $sql_delete = "DELETE FROM Orders WHERE order_id='$order_id' AND status='รอผู้รับงาน'";
    $conn->query($sql_delete);
    echo "<script>alert('ยกเลิกออเดอร์เรียบร้อยแล้วค่า!'); window.location.href='customer_dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head><title>หน้าหลักลูกค้า - รับหิ้วบางแสน</title></head>
<body>
    <h2>🎉 ยินดีต้อนรับคุณลูกค้า: <?php echo $_SESSION['username']; ?></h2>
    <a href="logout.php"><button>ออกจากระบบ</button></a>
    <hr>

    <h3>📝 สร้างออเดอร์ฝากซื้อ (Create)</h3>
    <form method="POST" action="">
        <label>รายละเอียดของ:</label><br>
        <textarea name="order_detail" required></textarea><br><br>
        <label>สถานที่จัดส่ง:</label><br>
        <select name="location_id" required>
            <option value="">-- เลือกสถานที่ --</option>
            <?php
            $result_loc = $conn->query("SELECT * FROM Locations");
            while($row = $result_loc->fetch_assoc()) echo "<option value='".$row['location_id']."'>".$row['name_place']."</option>";
            ?>
        </select><br><br>
        <label>ค่าหิ้ว (บาท):</label><br>
        <input type="number" name="errand_fee" required><br><br>
        <button type="submit" name="create_order">🚀 สร้างออเดอร์เลย!</button>
    </form>
    <hr>
    
    <h3>📦 ประวัติการสั่งซื้อของคุณ (Read & Delete)</h3>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr bgcolor="#f2f2f2">
            <th>รหัส</th><th>รายละเอียด</th><th>ค่าหิ้ว</th><th>สถานะ</th><th>จัดการ</th>
        </tr>
        <?php
        $sql_orders = "SELECT * FROM Orders WHERE customer_id='$customer_id' ORDER BY order_id DESC";
        $result_orders = $conn->query($sql_orders);
        if ($result_orders->num_rows > 0) {
            while($row = $result_orders->fetch_assoc()) {
                echo "<tr>
                        <td>".$row['order_id']."</td>
                        <td>".$row['order_detail']."</td>
                        <td>".$row['errand_fee']." บาท</td>
                        <td><b>".$row['status']."</b></td>
                        <td>";
                // โชว์ปุ่มยกเลิกเฉพาะตอนที่ยังไม่มีคนรับงาน
                if ($row['status'] == 'รอผู้รับงาน') {
                    echo "<form method='POST' action=''>
                            <input type='hidden' name='order_id' value='".$row['order_id']."'>
                            <button type='submit' name='cancel_order' onclick=\"return confirm('แน่ใจนะว่าจะยกเลิก?');\">❌ ยกเลิก</button>
                          </form>";
                } else {
                    echo "-";
                }
                echo "</td></tr>";
            }
        } else {
            echo "<tr><td colspan='5' align='center'>ยังไม่มีออเดอร์ค่า</td></tr>";
        }
        ?>
    </table>
</body>
</html>