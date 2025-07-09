<?php
// Thêm hướng dẫn cài đặt thư viện mPDF nếu chưa có
// Chạy lệnh sau trong thư mục web-ve-may-bay để cài đặt mPDF:
// composer require mpdf/mpdf

// Đảm bảo đường dẫn đúng, nếu dùng Composer thì là vendor/autoload.php
require_once __DIR__ . '/vendor/autoload.php';
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
<style>
    body { font-family: "DejaVu Sans", Arial, sans-serif; color: #222; }
    .invoice-container {
        max-width: 700px;
        margin: 0 auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 0 10px rgba(0,0,0,0.08);
        padding: 24px 28px;
        font-size: 14px;
    }
    .invoice-header {
        text-align: center;
        margin-bottom: 18px;
    }
    .invoice-header h2 {
        color: #0077cc;
        margin-bottom: 6px;
        font-size: 22px;
    }
    .info-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 0;
        margin-bottom: 12px;
    }
    .info-block {
        flex: 1 1 20%;
        min-width: 120px;
        max-width: 20%;
        background: #f4f8fb;
        border-radius: 6px;
        padding: 7px 10px;
        margin-right: 10px;
        margin-bottom: 10px;
        box-sizing: border-box;
    }
    .info-block .label {
        font-weight: bold;
        color: #222;
        font-size: 12px;
    }
    .info-block .value {
        color: #222;
        font-size: 13px;
    }
    .invoice-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
        font-size: 13px;
    }
    .invoice-table th, .invoice-table td {
        border: 1px solid #e0e0e0;
        padding: 7px 6px;
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
    .status-paid { color: #388e3c; font-weight: bold; }
    .status-unpaid { color: #e53935; font-weight: bold; }
</style>
<div class="invoice-container">
    <div class="invoice-header">
        <span style="font-size:20px;">&#9992;</span>
        <h2>HÓA ĐƠN VÉ MÁY BAY</h2>
        <div>Mã vé: <strong>#'.$data['id'].'</strong></div>
    </div>
    <div class="info-row">
        <div class="info-block">
            <span class="label"> Ngày đặt</span><br>
            <span class="value">'.date('d/m/Y H:i', strtotime($data['created_at'])).'</span>
        </div>
        <div class="info-block">
            <span class="label"> Hành khách</span><br>
            <span class="value">'.htmlspecialchars($data['danh_xung'].' '.$data['ten_nguoi_dat']).'</span>
        </div>
        <div class="info-block">
            <span class="label"> SĐT</span><br>
            <span class="value">'.htmlspecialchars($data['sdt']).'</span>
        </div>
        <div class="info-block">
            <span class="label"> Email</span><br>
            <span class="value">'.htmlspecialchars($data['email']).'</span>
        </div>
        <div class="info-block">
            <span class="label"> CCCD</span><br>
            <span class="value">'.htmlspecialchars($data['cccd']).'</span>
        </div>
    </div>
    <div class="info-row">
        <div class="info-block">
            <span class="label"> Chuyến bay</span><br>
            <span class="value">'.htmlspecialchars($data['ma_cb']).'</span>
        </div>
        <div class="info-block">
            <span class="label"> Điểm đi</span><br>
            <span class="value">'.htmlspecialchars($data['diem_di']).'</span>
        </div>
        <div class="info-block">
            <span class="label"> Điểm đến</span><br>
            <span class="value">'.htmlspecialchars($data['diem_den']).'</span>
        </div>
        <div class="info-block">
            <span class="label"> Giờ đi</span><br>
            <span class="value">'.htmlspecialchars($data['gio_di']).'</span>
        </div>
        <div class="info-block">
            <span class="label"> Giờ đến</span><br>
            <span class="value">'.htmlspecialchars($data['gio_den']).'</span>
        </div>
        <div class="info-block">
            <span class="label"> Hãng</span><br>
            <span class="value">'.htmlspecialchars($data['hang_hang_khong']).'</span>
        </div>
        <div class="info-block">
            <span class="label"> Loại máy bay</span><br>
            <span class="value">'.htmlspecialchars($data['loai_may_bay']).'</span>
        </div>
    </div>
    <table class="invoice-table">
        <tr>
            <th>Ghế</th>
            <th>Giá vé</th>
            <th>Trạng thái</th>
        </tr>
        <tr>
            <td>'.htmlspecialchars($data['ghe_so']).'</td>
            <td>'.number_format($data['gia'], 0, ',', '.').' đ</td>
            <td>'.
                ($data['trang_thai_thanh_toan'] == 1
                    ? '<span class="status-paid">&#10004; Đã thanh toán</span>'
                    : '<span class="status-unpaid">&#10060; Chưa thanh toán</span>')
            .'</td>
        </tr>
        <tr class="total-row">
            <td colspan="2" style="text-align:right;">Tổng cộng:</td>
            <td>'.number_format($data['gia'], 0, ',', '.').' đ</td>
        </tr>
    </table>
</div>
';

$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML($html);
$mpdf->Output('hoa_don_ve_'.$ma_ve.'.pdf', 'I');
?>
