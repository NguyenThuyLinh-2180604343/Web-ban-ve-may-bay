<?php
session_start();
include 'database/db.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo "<div style='color:red;'><i class='fas fa-exclamation-triangle'></i> Bạn không có quyền truy cập trang này.</div>";
    exit;
}

// Lấy dữ liệu lịch sử đặt vé
$sql = "
    SELECT ve.*, chuyen_bay.ma_cb, chuyen_bay.diem_di, chuyen_bay.diem_den, chuyen_bay.ngay_di 
    FROM ve 
    JOIN chuyen_bay ON ve.chuyen_bay_id = chuyen_bay.id 
    ORDER BY ve.created_at DESC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch sử đặt vé - Quản trị viên</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #1976d2;
            color: #fff;
        }
        h2 {
            color: #333;
        }
        .paid {
            color: green;
            font-weight: bold;
        }
        .unpaid {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h2><i class="fas fa-history"></i> Lịch sử đặt vé</h2>
    <table>
        <tr>
            <th><i class="fas fa-barcode"></i> Mã vé</th>
            <th><i class="fas fa-plane"></i> Chuyến bay</th>
            <th><i class="fas fa-user"></i> Hành khách</th>
            <th><i class="fas fa-chair"></i> Ghế</th>
            <th><i class="fas fa-circle-info"></i> Trạng thái</th>
            <th><i class="fas fa-calendar-alt"></i> Ngày đi</th>
            <th><i class="fas fa-clock"></i> Ngày đặt</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['ma_cb'] ?> (<?= $row['diem_di'] ?> &rarr; <?= $row['diem_den'] ?>)</td>
                    <td><?= htmlspecialchars($row['danh_xung'] . ' ' . $row['ten_nguoi_dat']) ?></td>
                    <td><?= $row['ghe_so'] ?></td>
                    <td>
                        <?php if ($row['trang_thai_thanh_toan'] == 1): ?>
                            <span class="paid"><i class="fas fa-check-circle"></i> Đã thanh toán</span>
                        <?php else: ?>
                            <span class="unpaid"><i class="fas fa-times-circle"></i> Chưa thanh toán</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $row['ngay_di'] ?></td>
                    <td><?= $row['created_at'] ?? '<i>Không có dữ liệu</i>' ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7"><i class="fas fa-info-circle"></i> Không có dữ liệu đặt vé nào.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>