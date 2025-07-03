<?php
include 'database/db.php';

$diem_di = $_GET['diem_di'] ?? '';
$diem_den = $_GET['diem_den'] ?? '';
$ngay_di = $_GET['ngay_di'] ?? '';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kết quả tìm kiếm chuyến bay</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background:rgb(229, 236, 246);
            margin: 0;
            padding: 20px;
        }

        h2.heading {
            text-align: center;
            margin-bottom: 30px;
            color:rgb(40, 52, 65);
        }

        .container {
            display: flex;
            max-width: 1200px;
            margin: auto;
            gap: 20px;
        }

        .sidebar {
            width: 250px;
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            height: fit-content;
        }

        .sidebar h4 {
            margin-bottom: 10px;
            color: #0077cc;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar li {
            padding: 6px 0;
            font-size: 14px;
            color: #333;
        }

        .results {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
        }

        .box {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s;
        }

        .box:hover {
            transform: translateY(-5px);
        }

        .box .content {
            padding: 15px;
        }

        .box h3 {
            margin: 0 0 10px;
            font-size: 17px;
            color: #333;
        }

        .info-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 13.5px;
            color: #555;
            margin: 5px 0;
        }

        .info-row span {
            min-width: 120px;
        }

        .price-book-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 12px;
        }

        .price-book-row .price {
            font-size: 15px;
            color: #e91e63;
            font-weight: bold;
        }

        .price-book-row .btn {
            padding: 6px 14px;
            font-size: 14px;
            border-radius: 4px;
            background:rgb(47, 130, 219);
            color: white;
            text-decoration: none;
        }

        .price-book-row .btn:hover {
            background:rgb(54, 132, 215);
        }

        .message {
            text-align: center;
            color: red;
            font-size: 16px;
            margin-top: 20px;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 30px;
        }

    </style>
</head>
<body>

<h2 class="heading">Kết quả tìm kiếm chuyến bay</h2>

<div class="container">
    <div class="sidebar">
        <h4>✈️ Hãng hàng không</h4>
        <ul>
            <?php
            if ($diem_di && $diem_den && $ngay_di) {
                $sqlHang = "SELECT DISTINCT hang_hang_khong FROM chuyen_bay 
                            WHERE diem_di LIKE '%$diem_di%' 
                              AND diem_den LIKE '%$diem_den%' 
                              AND ngay_di = '$ngay_di'";
                $resHang = $conn->query($sqlHang);
                if ($resHang->num_rows > 0) {
                    while ($row = $resHang->fetch_assoc()) {
                        echo "<li>🚩 " . $row['hang_hang_khong'] . "</li>";
                    }
                } else {
                    echo "<li>Không có hãng nào</li>";
                }
            }
            ?>
        </ul>
    </div>

    <div class="results">
        <?php
        if ($diem_di && $diem_den && $ngay_di) {
            $sql = "SELECT * FROM chuyen_bay 
                    WHERE diem_di LIKE '%$diem_di%' 
                      AND diem_den LIKE '%$diem_den%' 
                      AND ngay_di = '$ngay_di'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($cb = $result->fetch_assoc()) {
                    echo '
                    <div class="box">
                        <div class="content">
                            <h3><i class="fas fa-map-marker-alt"></i> ' . $cb['diem_di'] . ' → ' . $cb['diem_den'] . '</h3>
                            <div class="info-row">
                                <span><strong>Mã:</strong> ' . $cb['ma_cb'] . '</span>
                                <span><strong>Ngày:</strong> ' . $cb['ngay_di'] . '</span>
                                <span><strong>Giờ:</strong> ' . $cb['gio_di'] . ' - ' . $cb['gio_den'] . '</span>
                            </div>
                            <div class="info-row">
                                <span><strong>Hãng:</strong> ' . $cb['hang_hang_khong'] . '</span>
                                <span><strong>Loại:</strong> ' . $cb['loai_may_bay'] . '</span>
                            </div>
                            <div class="price-book-row">
                                <div class="price">' . number_format($cb['gia'], 0, ',', '.') . ' đ</div>
                                <a href="chitiet.php?id=' . $cb['id'] . '" class="btn">✈️ Đặt vé</a>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                echo "<p class='message'>❌ Không tìm thấy chuyến bay phù hợp.</p>";
            }
        } else {
            echo "<p class='message'>⚠️ Vui lòng nhập đầy đủ thông tin tìm kiếm.</p>";
        }
        ?>
    </div>
</div>

<a href="index.php" class="back-link">← Quay lại trang chủ</a>

</body>
</html>
