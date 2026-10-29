-- 1. สร้างตาราง User
CREATE TABLE User (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'sender') NOT NULL,
    phone VARCHAR(15) NOT NULL
);

-- 2. สร้างตาราง Locations
CREATE TABLE Locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    name_place VARCHAR(100) NOT NULL
);

-- 3. สร้างตาราง Orders
CREATE TABLE Orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    sender_id INT, -- ปล่อยว่าง (NULL) ไว้ก่อนได้ ถ้ายังไม่มีคนรับงาน
    location_id INT NOT NULL,
    order_detail TEXT NOT NULL,
    errand_fee DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'รอผู้รับงาน',
    FOREIGN KEY (customer_id) REFERENCES User(user_id),
    FOREIGN KEY (sender_id) REFERENCES User(user_id),
    FOREIGN KEY (location_id) REFERENCES Locations(location_id)
);

-- 4. สร้างตาราง Payment
CREATE TABLE Payment (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    slip VARCHAR(255),
    status_payment VARCHAR(50) DEFAULT 'ยังไม่ชำระเงิน',
    FOREIGN KEY (order_id) REFERENCES Orders(order_id)
);