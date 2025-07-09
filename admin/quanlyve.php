<?php
session_start();
require_once "../database/db.php";
require_once "session.php";
checkLogin();

// Chỉ cho phép admin truy cập
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Xử lý hủy vé
if (isset($_POST['huy_ve'])) {
    $ma_ve = (int)$_POST['huy_ve'];
    $conn->query("DELETE FROM ve WHERE id = $ma_ve");
    $thongbao = "<div style='color:red;'><i class='fas fa-ban'></i> Đã hủy vé mã <strong>$ma_ve</strong> thành công.</div>";
}

// Lấy danh sách vé
$sql = "SELECT ve.*, chuyen_bay.ma_cb, chuyen_bay.diem_di, chuyen_bay.diem_den 
        FROM ve 
        JOIN chuyen_bay ON ve.chuyen_bay_id = chuyen_bay.id
        ORDER BY ve.id DESC";
$ves = $conn->query($sql);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Quản lý vé - Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 40px;
        }

        .container {
            max-width: 1100px;
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

        .btn-huy {
            background: #e53935;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 6px 12px;
            cursor: pointer;
        }

        .btn-huy:hover {
            background: #b71c1c;
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
        <h2><i class="fa-solid fa-ticket"></i> Quản lý vé đã đặt</h2>
        <?php if (isset($thongbao)) echo $thongbao; ?>
        <table>
            <tr>
                <th>Mã vé</th>
                <th>Chuyến bay</th>
                <th>Điểm đi</th>
                <th>Điểm đến</th>
                <th>Họ tên</th>
                <th>Ghế</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
            <?php while ($ve = $ves->fetch_assoc()): ?>
                <tr>
                    <td><?= $ve['id'] ?></td>
                    <td><?= $ve['ma_cb'] ?></td>
                    <td><?= $ve['diem_di'] ?></td>
                    <td><?= $ve['diem_den'] ?></td>
                    <td><?= htmlspecialchars($ve['ten_nguoi_dat']) ?></td>
                    <td><?= $ve['ghe_so'] ?></td>
                    <td>
                        <?php if ($ve['trang_thai_thanh_toan'] == 1): ?>
                            <span class="paid"><i class="fa-solid fa-circle"></i> Đã thanh toán</span>
                        <?php else: ?>
                            <span class="unpaid"><i class="fa-solid fa-circle"></i> Đã đặt</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Bạn chắc chắn muốn hủy vé này?');">
                            <input type="hidden" name="huy_ve" value="<?= $ve['id'] ?>">
                            <button type="submit" class="btn-huy"><i class="fa-solid fa-ban"></i> Hủy vé</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
        <br>
        <a href="sanphamadmin.php">← Quay lại quản lý chuyến bay</a>
    </div>
</body>

</html>