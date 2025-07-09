<?php
session_start();
include 'database/db.php';

if (!isset($_GET['id'])) {
    echo "<div style='color:red; padding:20px;'>Không tìm thấy mã vé.</div>";
    exit;
}
$id = (int)$_GET['id'];
$sql = "SELECT ve.*, chuyen_bay.ma_cb, chuyen_bay.diem_di, chuyen_bay.diem_den, chuyen_bay.gio_di, chuyen_bay.gio_den, chuyen_bay.hang_hang_khong, chuyen_bay.loai_may_bay, chuyen_bay.gia
        FROM ve
        JOIN chuyen_bay ON ve.chuyen_bay_id = chuyen_bay.id
        WHERE ve.id = $id";
$result = $conn->query($sql);
if ($result->num_rows === 0) {
    echo "<div style='color:red; padding:20px;'>Không tìm thấy vé.</div>";
    exit;
}
$ve = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hóa đơn vé máy bay #<?= $ve['id'] ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: rgb(204, 218, 240);
            margin: 0;
            padding: 0;
        }
        .invoice-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
            padding: 32px 36px;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 24px;
        }
        .invoice-header h2 {
            color: #0077cc;
            margin-bottom: 8px;
        }
        .invoice-header .fa-ticket {
            color: #0077cc;
            font-size: 2em;
        }
        .invoice-info, .flight-info, .customer-info {
            margin-bottom: 18px;
        }
        .invoice-info span,
        .flight-info span,
        .customer-info span {
            display: inline-block;
            min-width: 120px;
            font-weight: bold;
            color: #333;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .invoice-table th, .invoice-table td {
            border: 1px solid #e0e0e0;
            padding: 10px 8px;
            text-align: left;
        }
        .invoice-table th {
            background: #f0f0f0;
            color: #333;
        }
        .total-row td {
            font-weight: bold;
            color: #0077cc;
            background: #f4f8fb;
        }
        .status-paid {
            color: #388e3c;
            font-weight: bold;
        }
        .status-unpaid {
            color: #e53935;
            font-weight: bold;
        }
        .back-link {
            display: inline-block;
            margin-top: 18px;
            color: #0077cc;
            text-decoration: underline;
            font-weight: bold;
        }
        .back-link:hover {
            color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <i class="fa-solid fa-ticket"></i>
            <h2>HÓA ĐƠN VÉ MÁY BAY</h2>
            <div>Mã vé: <strong>#<?= $ve['id'] ?></strong></div>
        </div>
        <!-- Thông tin hóa đơn xếp hàng ngang, khung nhỏ và chữ về màu đen -->
        <div style="display: flex; flex-wrap: wrap; gap: 12px 18px; margin-bottom: 18px;">
            <div style="flex:1 1 140px; min-width:120px; background:#f4f8fb; border-radius:6px; padding:8px 10px;">
                <span style="font-weight:bold; color:#222;"><i class="fa-solid fa-calendar"></i> Ngày đặt</span><br>
                <span style="color:#222;"><?= date('d/m/Y H:i', strtotime($ve['created_at'])) ?></span>
            </div>
            <div style="flex:1 1 140px; min-width:120px; background:#f4f8fb; border-radius:6px; padding:8px 10px;">
                <span style="font-weight:bold; color:#222;"><i class="fa-solid fa-user"></i> Hành khách</span><br>
                <span style="color:#222;"><?= htmlspecialchars($ve['danh_xung'] . ' ' . $ve['ten_nguoi_dat']) ?></span>
            </div>
            <div style="flex:1 1 140px; min-width:120px; background:#f4f8fb; border-radius:6px; padding:8px 10px;">
                <span style="font-weight:bold; color:#222;"><i class="fa-solid fa-phone"></i> SĐT</span><br>
                <span style="color:#222;"><?= htmlspecialchars($ve['sdt']) ?></span>
            </div>
            <div style="flex:1 1 140px; min-width:120px; background:#f4f8fb; border-radius:6px; padding:8px 10px;">
                <span style="font-weight:bold; color:#222;"><i class="fa-solid fa-envelope"></i> Email</span><br>
                <span style="color:#222;"><?= htmlspecialchars($ve['email']) ?></span>
            </div>
            <div style="flex:1 1 140px; min-width:120px; background:#f4f8fb; border-radius:6px; padding:8px 10px;">
                <span style="font-weight:bold; color:#222;"><i class="fa-solid fa-id-card"></i> CCCD</span><br>
                <span style="color:#222;"><?= htmlspecialchars($ve['cccd']) ?></span>
            </div>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 12px 18px; margin-bottom: 18px;">
            <div style="flex:1 1 110px; min-width:100px; background:#f4f8fb; border-radius:6px; padding:8px 10px;">
                <span style="font-weight:bold; color:#222;"><i class="fa-solid fa-plane-departure"></i> Chuyến bay</span><br>
                <span style="color:#222;"><?= htmlspecialchars($ve['ma_cb']) ?></span>
            </div>
            <div style="flex:1 1 110px; min-width:100px; background:#f4f8fb; border-radius:6px; padding:8px 10px;">
                <span style="font-weight:bold; color:#222;"><i class="fa-solid fa-map-marker-alt"></i> Điểm đi</span><br>
                <span style="color:#222;"><?= htmlspecialchars($ve['diem_di']) ?></span>
            </div>
            <div style="flex:1 1 110px; min-width:100px; background:#f4f8fb; border-radius:6px; padding:8px 10px;">
                <span style="font-weight:bold; color:#222;"><i class="fa-solid fa-bullseye"></i> Điểm đến</span><br>
                <span style="color:#222;"><?= htmlspecialchars($ve['diem_den']) ?></span>
            </div>
            <div style="flex:1 1 110px; min-width:100px; background:#f4f8fb; border-radius:6px; padding:8px 10px;">
                <span style="font-weight:bold; color:#222;"><i class="fa-solid fa-clock"></i> Giờ đi</span><br>
                <span style="color:#222;"><?= htmlspecialchars($ve['gio_di']) ?></span>
            </div>
            <div style="flex:1 1 110px; min-width:100px; background:#f4f8fb; border-radius:6px; padding:8px 10px;">
                <span style="font-weight:bold; color:#222;"><i class="fa-solid fa-hourglass-end"></i> Giờ đến</span><br>
                <span style="color:#222;"><?= htmlspecialchars($ve['gio_den']) ?></span>
            </div>
            <div style="flex:1 1 110px; min-width:100px; background:#f4f8fb; border-radius:6px; padding:8px 10px;">
                <span style="font-weight:bold; color:#222;"><i class="fa-solid fa-building"></i> Hãng</span><br>
                <span style="color:#222;"><?= htmlspecialchars($ve['hang_hang_khong']) ?></span>
            </div>
            <div style="flex:1 1 110px; min-width:100px; background:#f4f8fb; border-radius:6px; padding:8px 10px;">
                <span style="font-weight:bold; color:#222;"><i class="fa-solid fa-plane"></i> Loại máy bay</span><br>
                <span style="color:#222;"><?= htmlspecialchars($ve['loai_may_bay']) ?></span>
            </div>
        </div>
        <table class="invoice-table">
            <tr>
                <th>Ghế</th>
                <th>Giá vé</th>
                <th>Trạng thái</th>
            </tr>
            <tr>
                <td><?= htmlspecialchars($ve['ghe_so']) ?></td>
                <td><?= number_format($ve['gia'], 0, ',', '.') ?> đ</td>
                <td>
                    <?php if ($ve['trang_thai_thanh_toan'] == 1): ?>
                        <span class="status-paid"><i class="fa-solid fa-circle-check"></i> Đã thanh toán</span>
                    <?php else: ?>
                        <span class="status-unpaid"><i class="fa-solid fa-circle-xmark"></i> Chưa thanh toán</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr class="total-row">
                <td colspan="2" style="text-align:right;">Tổng cộng:</td>
                <td><?= number_format($ve['gia'], 0, ',', '.') ?> đ</td>
            </tr>
        </table>
        <a href="javascript:window.close()" class="back-link"><i class="fa-solid fa-arrow-left"></i> Đóng hóa đơn</a>
    </div>
</body>
</html>
