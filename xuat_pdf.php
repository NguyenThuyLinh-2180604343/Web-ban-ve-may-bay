<?php
require_once __DIR__ . '/mpdf/vendor/autoload.php'; // Đảm bảo đúng đường dẫn
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

$html = '
<h2 style="color:#0077cc;">🧾 HOÁ ĐƠN VÉ MÁY BAY</h2>
<p><strong>Mã vé:</strong> '.$data['id'].'</p>
<p><strong>Họ tên:</strong> '.$data['ten_nguoi_dat'].'</p>
<p><strong>Ghế:</strong> '.$data['ghe_so'].'</p>
<p><strong>Trạng thái:</strong> '.($data['trang_thai_thanh_toan'] ? '✅ Đã thanh toán' : '❌ Chưa thanh toán').'</p>
<hr>
<p><strong>Chuyến bay:</strong> '.$data['ma_cb'].'</p>
<p><strong>Điểm đi:</strong> '.$data['diem_di'].'</p>
<p><strong>Điểm đến:</strong> '.$data['diem_den'].'</p>
<p><strong>Giờ đi:</strong> '.$data['gio_di'].'</p>
<p><strong>Giờ đến:</strong> '.$data['gio_den'].'</p>
<p><strong>Hãng:</strong> '.$data['hang_hang_khong'].'</p>
<p><strong>Loại máy bay:</strong> '.$data['loai_may_bay'].'</p>
<p><strong>Giá vé:</strong> '.number_format($data['gia'], 0, ',', '.').' đ</p>
';

$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML($html);
$mpdf->Output('hoa_don_ve_'.$ma_ve.'.pdf', 'I'); // I = inline, D = download
