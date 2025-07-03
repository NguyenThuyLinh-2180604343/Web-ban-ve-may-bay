<?php
include 'database/db.php';

if (!isset($_GET['id'])) {
    die("❌ Không tìm thấy mã vé.");
}

$ma_ve = (int)$_GET['id'];

$ve = $conn->query("SELECT ve.*, chuyen_bay.* FROM ve 
    JOIN chuyen_bay ON ve.chuyen_bay_id = chuyen_bay.id 
    WHERE ve.id = $ma_ve");

if ($ve->num_rows === 0) {
    die("❌ Vé không tồn tại.");
}

$data = $ve->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hoá đơn vé</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: auto; padding: 20px; }
        h2 { color: #0077cc; }
        .info { margin-bottom: 10px; }
        .label { font-weight: bold; width: 150px; display: inline-block; }
    </style>
</head>
<body>
    <h2>🧾 HOÁ ĐƠN VÉ MÁY BAY</h2>
    <div class="info"><span class="label">Mã vé:</span> <?= $data['id'] ?></div>
    <div class="info"><span class="label">Tên người đặt:</span> <?= $data['ten_nguoi_dat'] ?></div>
    <div class="info"><span class="label">Ghế:</span> <?= $data['ghe_so'] ?></div>
    <div class="info"><span class="label">Trạng thái:</span> <?= $data['trang_thai_thanh_toan'] ? '✅ Đã thanh toán' : '❌ Chưa thanh toán' ?></div>
    <hr>
    <div class="info"><span class="label">Chuyến bay:</span> <?= $data['ma_cb'] ?></div>
    <div class="info"><span class="label">Điểm đi:</span> <?= $data['diem_di'] ?></div>
    <div class="info"><span class="label">Điểm đến:</span> <?= $data['diem_den'] ?></div>
    <div class="info"><span class="label">Giờ đi:</span> <?= $data['gio_di'] ?></div>
    <div class="info"><span class="label">Giờ đến:</span> <?= $data['gio_den'] ?></div>
    <div class="info"><span class="label">Hãng:</span> <?= $data['hang_hang_khong'] ?></div>
    <div class="info"><span class="label">Loại máy bay:</span> <?= $data['loai_may_bay'] ?></div>
    <div class="info"><span class="label">Giá vé:</span> <?= number_format($data['gia'], 0, ',', '.') ?> đ</div>
</body>
</html>
