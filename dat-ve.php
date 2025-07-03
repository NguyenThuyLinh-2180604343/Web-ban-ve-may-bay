<?php
require 'vendor/autoload.php';
use Aws\Sns\SnsClient;
include 'db.php';

$cb_id = $_GET['id'];
$cb = $conn->query("SELECT * FROM chuyen_bay WHERE id = $cb_id")->fetch_assoc();

// Lấy danh sách ghế đã đặt
$ghe_da_dat = [];
$result = $conn->query("SELECT ghe_so FROM ve WHERE chuyen_bay_id = $cb_id");
while ($row = $result->fetch_assoc()) {
    $ghe_da_dat[] = $row['ghe_so'];
}
// xử lý đặt vé 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten = $_POST['ten'];
    $ghe = $_POST['ghe_so'];

    // Kiểm tra ghế đã đặt chưa
    if (in_array($ghe, $ghe_da_dat)) {
        echo "<p style='color:red'>❌ Ghế $ghe đã có người đặt!</p>";
    } else {
        $stmt = $conn->prepare("INSERT INTO ve (ten_nguoi_dat, chuyen_bay_id, ghe_so) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $ten, $cb_id, $ghe);
        $stmt->execute();

$snsClient = new SnsClient([
    'region' => 'ap-southeast-1',
    'version' => 'latest',
    'credentials' => [
        'key' => 'AKIA6AOVVROIZDQSWIPL', // 🔒 Thay bằng Access Key thật
        'secret' => '7NgtllDlzwF899a6DD1x40FpE14i01mBeLsb51tZ' // 🔒 Thay bằng Secret Key thật
    ]
]);

$message = "📩 Khách hàng $ten vừa đặt vé chuyến {$cb['ma_cb']} từ {$cb['diem_di']} đến {$cb['diem_den']} – Ghế: $ghe.";

try {
    $snsClient->publish([
        'TopicArn' => 'arn:aws:sns:ap-southeast-1:963057650577:dat-ve-thanh-cong', // Thay ARN thật
        'Message' => $message
    ]);
} catch (Exception $e) {
    error_log("❌ SNS lỗi: " . $e->getMessage());
}

        echo "<p style='color:green'>✅ Đặt vé thành công! Ghế: $ghe</p>";
        // Cập nhật lại danh sách ghế
        $ghe_da_dat[] = $ghe;
    }
}
?>

<h3>✈️ Đặt vé chuyến <?= $cb['ma_cb'] ?> (<?= $cb['diem_di'] ?> → <?= $cb['diem_den'] ?>)</h3>
<form method="post">
    Họ tên: <input type="text" name="ten" required><br>
    Chọn ghế: 
    <select name="ghe_so" required>
        <?php
        $tong_ghe = $cb['tong_ghe'];
        for ($i = 1; $i <= $tong_ghe; $i++) {
            $ghe = 'G' . $i;
            $disabled = in_array($ghe, $ghe_da_dat) ? 'disabled' : '';
            echo "<option value='$ghe' $disabled>$ghe" . ($disabled ? ' (Đã đặt)' : '') . "</option>";
        }
        ?>
    </select><br>
    <button type="submit">Xác nhận</button>
    <h3>💳 Thanh toán vé</h3>
<form method="post">
    Nhập mã vé: <input type="number" name="ma_ve" required>
    <button type="submit" name="thanh_toan">Thanh toán</button>
</form>

</form>
<a href="index.php">← Quay lại</a>
