<?php
include '../database/db.php';
require_once "session.php";
session_start();

// Kiểm tra nếu chưa đăng nhập hoặc không phải admin thì chặn
checkAdmin();
$id = $_GET['id'];
$cb = $conn->query("SELECT * FROM chuyen_bay WHERE id=$id")->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   $stmt = $conn->prepare("UPDATE chuyen_bay SET ma_cb=?, diem_di=?, diem_den=?, ngay_di=?, gio_di=?, gio_den=?, hang_hang_khong=?, loai_may_bay=?, gia=? WHERE id=?");
$stmt->bind_param("ssssssssdi",
    $_POST['ma_cb'], $_POST['diem_di'], $_POST['diem_den'], $_POST['ngay_di'],
    $_POST['gio_di'], $_POST['gio_den'], $_POST['hang'],
    $_POST['loai'], $_POST['gia'], $id
);

    $stmt->execute();
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sửa chuyến bay</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 40px;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        h2 {
            color: #1e40af;
            margin-bottom: 20px;
            text-align: center;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: 500;
        }

        input[type="text"],
        input[type="number"],
        input[type="time"] {
            width: 100%;
            padding: 10px 12px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
            transition: 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #3b82f6;
            background: #f0f9ff;
        }

        button {
            margin-top: 25px;
            width: 100%;
            background-color: #3b82f6;
            color: white;
            border: none;
            padding: 12px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background-color: #2563eb;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #2563eb;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>✏️ Sửa thông tin chuyến bay</h2>
    <form method="post">
        <label>Mã chuyến bay</label>
        <input name="ma_cb" value="<?= $cb['ma_cb'] ?>" required>

        <label>Điểm đi</label>
        <input name="diem_di" value="<?= $cb['diem_di'] ?>" required>

        <label>Điểm đến</label>
        <input name="diem_den" value="<?= $cb['diem_den'] ?>" required>

        <label>Ngày đi</label>
        <input name="ngay_di" type="date" value="<?= $cb['ngay_di'] ?>" required>
        
        <label>Giờ đi</label>
        <input name="gio_di" type="time" value="<?= $cb['gio_di'] ?>" required>

        <label>Giờ đến</label>
        <input name="gio_den" type="time" value="<?= $cb['gio_den'] ?>" required>

        <label>Hãng hàng không</label>
        <input name="hang" value="<?= $cb['hang_hang_khong'] ?>" required>

        <label>Loại máy bay</label>
        <input name="loai" value="<?= $cb['loai_may_bay'] ?>" required>

        <label>Giá vé</label>
        <input name="gia" type="number" value="<?= $cb['gia'] ?>" required>

        <button type="submit">💾 Cập nhật chuyến bay</button>
    </form>
    <a class="back-link" href="../index.php">← Quay lại trang danh sách</a>
</div>
</body>
</html>
