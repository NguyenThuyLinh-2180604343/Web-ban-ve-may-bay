<?php
session_start();
include 'database/db.php'; // Sửa đường dẫn cho đúng

// Giả sử user đăng nhập bằng tên (ten_nguoi_dat) lưu trong $_SESSION['username']
if (!isset($_SESSION['username'])) {
    echo "<p style='color:red;'>Bạn cần đăng nhập để xem lịch sử vé.</p>";
    exit;
}

$username = $conn->real_escape_string($_SESSION['username']);
$sql = "SELECT ve.*, chuyen_bay.ma_cb, chuyen_bay.diem_di, chuyen_bay.diem_den
        FROM ve
        JOIN chuyen_bay ON ve.chuyen_bay_id = chuyen_bay.id
        WHERE ve.ten_nguoi_dat = '$username'
        ORDER BY ve.id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Lịch sử mua vé</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 40px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }

        h2 {
            color: #0077cc;
            margin-bottom: 20px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 10px;
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

        .paid {
            color: #e53935;
            font-weight: bold;
        }

        .unpaid {
            color: #ffd600;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2><i class="fa-solid fa-ticket"></i> Lịch sử mua vé</h2>
        <table>
            <tr>
                <th>Mã vé</th>
                <th>Chuyến bay</th>
                <th>Điểm đi</th>
                <th>Điểm đến</th>
                <th>Ghế</th>
                <th>Trạng thái</th>
            </tr>
            <?php if ($result->num_rows == 0): ?>
                <tr>
                    <td colspan="6">Bạn chưa mua vé nào.</td>
                </tr>
                <?php else: while ($ve = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $ve['id'] ?></td>
                        <td><?= htmlspecialchars($ve['ma_cb']) ?></td>
                        <td><?= htmlspecialchars($ve['diem_di']) ?></td>
                        <td><?= htmlspecialchars($ve['diem_den']) ?></td>
                        <td><?= htmlspecialchars($ve['ghe_so']) ?></td>
                        <td>
                            <?php if ($ve['trang_thai_thanh_toan'] == 1): ?>
                                <span class="paid"><i class="fa-solid fa-circle"></i> Đã thanh toán</span>
                            <?php else: ?>
                                <span class="unpaid"><i class="fa-solid fa-circle"></i> Chưa thanh toán</span>
                            <?php endif; ?>
                        </td>
                    </tr>
            <?php endwhile;
            endif; ?>
        </table>
        <br>
        <a href="index.php" style="color:#0077cc; text-decoration:underline; font-weight:bold;">← Quay lại trang chủ</a>
    </div>
</body>

</html>