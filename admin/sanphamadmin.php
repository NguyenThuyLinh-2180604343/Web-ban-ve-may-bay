<?php
session_start();
include '../database/db.php'; 
require_once "session.php";
checkLogin();



// Kiểm tra nếu chưa đăng nhập hoặc không phải admin thì chặn
checkAdmin();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quản lý chuyến bay</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 40px;
            color: #333;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }

        h2 {
            color: #0077cc;
            margin-bottom: 20px;
            text-align: center;
        }

        .add-btn {
            display: inline-block;
            margin-bottom: 20px;
            background-color: #28a745;
            color: white;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .add-btn:hover {
            background-color: #218838;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            background-color: #f1f3f5;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f9fbfd;
        }

        .btn {
            padding: 6px 12px;
            color: white;
            border-radius: 4px;
            font-weight: bold;
            text-decoration: none;
            margin: 0 2px;
            transition: background-color 0.3s;
        }

        .btn.edit {
            background-color: #007bff;
        }

        .btn.edit:hover {
            background-color: #0056b3;
        }

        .btn.delete {
            background-color: #dc3545;
        }

        .btn.delete:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📋 Danh sách chuyến bay</h2>
    <a href="them.php" class="add-btn">➕ Thêm chuyến bay</a>
    <table>
        <tr>
            <th>Mã CB</th><th>Điểm đi</th><th>Điểm đến</th><th>Giờ đi</th><th>Giờ đến</th>
            <th>Hãng</th><th>Loại</th><th>Giá</th><th>Hành động</th>
        </tr>
        <?php
        $result = $conn->query("SELECT * FROM chuyen_bay");
        while ($row = $result->fetch_assoc()):
        ?>
        <tr>
            <td><?= $row['ma_cb'] ?></td>
            <td><?= $row['diem_di'] ?></td>
            <td><?= $row['diem_den'] ?></td>
            <td><?= $row['gio_di'] ?></td>
            <td><?= $row['gio_den'] ?></td>
            <td><?= $row['hang_hang_khong'] ?></td>
            <td><?= $row['loai_may_bay'] ?></td>
            <td><?= number_format($row['gia'], 0, ',', '.') ?> đ</td>
            <td>
                <a href="sua.php?id=<?= $row['id'] ?>" class="btn edit">✏️</a>
                <a href="xoa.php?id=<?= $row['id'] ?>" class="btn delete" onclick="return confirm('Xoá chuyến bay này?')">🗑️</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
