<?php
$host     = 'localhost';
$username = 'root';        // MAMP mặc định
$password = 'root';
$dbname   = 'vemaybay';


// Kết nối đến MySQL (chưa chọn database)
$conn = new mysqli($host, $username, $password);
if ($conn->connect_error) {
    die('Kết nối thất bại: ' . $conn->connect_error);
}

// Tạo database nếu chưa có
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");

// Chọn database
$conn->select_db($dbname);

// Tạo bảng chuyen_bay nếu chưa có
$conn->query("CREATE TABLE IF NOT EXISTS chuyen_bay (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ma_cb VARCHAR(20),
    diem_di VARCHAR(100),
    diem_den VARCHAR(100),
    ngay_di Date,
    gio_di TIME,
    gio_den TIME,
    hang_hang_khong VARCHAR(100),
    loai_may_bay VARCHAR(100),
    gia DECIMAL(10,2),
    tong_ghe INT DEFAULT 60
)");


// Tạo bảng ve nếu chưa có
$conn->query("CREATE TABLE IF NOT EXISTS ve (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_nguoi_dat VARCHAR(100),
    chuyen_bay_id INT,
    ghe_so VARCHAR(10),
    trang_thai_thanh_toan TINYINT DEFAULT 0,
    danh_xung VARCHAR(20),
    sdt VARCHAR(20),
    email VARCHAR(100),
    cccd VARCHAR(20),
    FOREIGN KEY (chuyen_bay_id) REFERENCES chuyen_bay(id)

)");



// Tạo bảng users nếu chưa có
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    role VARCHAR(20) DEFAULT 'user', -- Thêm role, mặc định là 'user'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

?>