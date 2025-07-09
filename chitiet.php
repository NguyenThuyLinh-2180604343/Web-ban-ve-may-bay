<?php
session_start();
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$_SESSION['role'] = 'admin'; // Khi đăng nhập là admin

include 'database/db.php';

if (!isset($_GET['id'])) {
    echo "<i class='fas fa-times-circle'></i> Không tìm thấy chuyến bay.";
    exit;
}

$id = (int)$_GET['id'];
$result = $conn->query("SELECT * FROM chuyen_bay WHERE id = $id");
if ($result->num_rows === 0) {
    echo "<i class='fas fa-times-circle'></i> Không tồn tại chuyến bay.";
    exit;
}

$row = $result->fetch_assoc();

$ghe_da_dat = [];
$ghe_da_thanh_toan = [];
// Ghế đã thanh toán
$r = $conn->query("SELECT ghe_so FROM ve WHERE chuyen_bay_id = $id AND trang_thai_thanh_toan = 1");
while ($g = $r->fetch_assoc()) {
    $ghe_da_thanh_toan[] = $g['ghe_so'];
}
// Ghế đã đặt nhưng chưa thanh toán
$r2 = $conn->query("SELECT ghe_so FROM ve WHERE chuyen_bay_id = $id AND trang_thai_thanh_toan = 0");
while ($g = $r2->fetch_assoc()) {
    $ghe_da_dat[] = $g['ghe_so'];
}

if (isset($_POST['thanh_toan']) && isset($_POST['ma_ve']) && isset($_POST['phuong_thuc'])) {
    $ma_ve = (int)$_POST['ma_ve'];
    $phuong_thuc = $_POST['phuong_thuc'];

    $check = $conn->query("SELECT * FROM ve WHERE id = $ma_ve AND chuyen_bay_id = $id");
    if ($check->num_rows > 0) {
        $conn->query("UPDATE ve SET trang_thai_thanh_toan = 1 WHERE id = $ma_ve");
        $ten_pt = $phuong_thuc === 'momo' ? 'Ví Momo' : 'VNPay';
        $thongbao = "<i class='fas fa-money-bill-wave'></i> Thanh toán thành công bằng <strong>$ten_pt</strong> cho vé mã số <strong>$ma_ve</strong>!";
    } else {
        $thongbao = "<i class='fas fa-times-circle'></i> Vé không tồn tại hoặc không thuộc chuyến bay này.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ten']) && isset($_POST['ghe_so'])) {
    $danh_xung = $_POST['danh_xung'] ?? '';
    $ten = $_POST['ten'] ?? '';
    $sdt = $_POST['sdt'] ?? '';
    $email = $_POST['email'] ?? '';
    $cccd = $_POST['cccd'] ?? '';
    $ghe_list = explode(',', $_POST['ghe_so']);
    $_SESSION['ten_nguoi_dung'] = $ten;

    $success = [];
    $failed = [];
    foreach ($ghe_list as $ghe) {
        $ghe = trim($ghe);
        if ($ghe === '') continue;
        if (in_array($ghe, $ghe_da_dat)) {
            $failed[] = $ghe;
        } else {
            $stmt = $conn->prepare("INSERT INTO ve (ten_nguoi_dat, chuyen_bay_id, ghe_so, danh_xung, sdt, email, cccd) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sisssss", $ten, $id, $ghe, $danh_xung, $sdt, $email, $cccd);
            $stmt->execute();
            $ma_dat_ve = $stmt->insert_id;
            $success[] = ['ghe' => $ghe, 'ma' => $ma_dat_ve];
        }
    }

    $thongbao = '';
    if ($success) {
        $thongbao .= "<strong style='color:green;'><i class='fas fa-check-circle'></i> Đặt vé thành công cho các ghế:</strong><ul>";
        foreach ($success as $item) {
            $thongbao .= "<li><strong>Ghế:</strong> {$item['ghe']} - <strong>Mã vé:</strong> <span style='color:blue;'>{$item['ma']}</span></li>";
        }
        $thongbao .= "</ul>";
    }
    if ($failed) {
        $thongbao .= "<div style='color:red;'><i class='fas fa-times-circle'></i> Các ghế đã được đặt hoặc thanh toán: " . implode(', ', $failed) . "</div>";
    }
}


// Xử lý xoá vé (admin hoặc user)
if (isset($_POST['xoa_ve'])) {
    $ma_ve = (int)$_POST['xoa_ve'];

    if ($isAdmin) {
        // Admin được xoá tất cả vé
        $conn->query("DELETE FROM ve WHERE id = $ma_ve AND chuyen_bay_id = $id");
        $thongbao = "
        <div class='notification' style='color: #d32f2f; background: #ffebee; border-left: 6px solid #d32f2f; padding:10px; margin:10px 0;'>
            <i class='fas fa-user-shield'></i> Admin đã <strong>xóa</strong> vé mã số <strong>$ma_ve</strong> (bao gồm cả vé đã thanh toán).
        </div>";
    } else {
        // Người dùng chỉ xoá được vé chưa thanh toán
        $deleted = $conn->query("DELETE FROM ve WHERE id = $ma_ve AND chuyen_bay_id = $id AND trang_thai_thanh_toan = 0");

        if ($conn->affected_rows > 0) {
            $thongbao = "
            <div class='notification' style='color: #f57c00; background: #fff3e0; border-left: 6px solid #f57c00; padding:10px; margin:10px 0;'>
                <i class='fas fa-trash-alt'></i> Bạn đã <strong>xóa</strong> vé mã số <strong>$ma_ve</strong> (chưa thanh toán).
            </div>";
        } else {
            $thongbao = "
            <div class='notification' style='color: #c62828; background: #fdecea; border-left: 6px solid #c62828; padding:10px; margin:10px 0;'>
                <i class='fas fa-exclamation-circle'></i> Không thể xóa vé mã <strong>$ma_ve</strong> vì đã thanh toán hoặc không tồn tại.
            </div>";
        }
    }
}

// Người dùng tự huỷ vé chưa thanh toán (nếu bạn tách thành 2 nút xoá riêng)
if (isset($_POST['huy_ve'])) {
    $ma_ve = (int)$_POST['huy_ve'];

    $sql_chk = "SELECT * FROM ve WHERE id = $ma_ve AND chuyen_bay_id = $id AND trang_thai_thanh_toan = 0";
    $r_chk = $conn->query($sql_chk);

    if ($r_chk->num_rows > 0) {
        $conn->query("DELETE FROM ve WHERE id = $ma_ve");

        $thongbao = "
        <div class='notification' style='color: #1976d2; background: #e3f2fd; border-left: 6px solid #1976d2; padding:10px; margin:10px 0;'>
            <i class='fas fa-ban'></i> Bạn đã <strong>huỷ</strong> vé mã <strong>$ma_ve</strong> thành công.
        </div>";
    } else {
        $thongbao = "
        <div class='notification' style='color: #c62828; background: #fdecea; border-left: 6px solid #c62828; padding:10px; margin:10px 0;'>
            <i class='fas fa-exclamation-circle'></i> Vé không tồn tại hoặc đã thanh toán – không thể huỷ.
        </div>";
    }
}
$conn->query("DELETE FROM ve 
              WHERE trang_thai_thanh_toan = 0 
              AND created_at <= NOW() - INTERVAL 15 MINUTE");

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Chi tiết chuyến bay</title>
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        .flight-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px 20px;
            margin-top: 15px;
            margin-bottom: 20px;
        }

        .flight-grid .item {
            padding: 8px 12px;
            background: #f9f9f9;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 15px;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: rgb(204, 218, 240);
            margin: 0;
            padding: 20px;
            color: #333;
        }

        h2,
        h3 {
            color: rgb(3, 60, 100);
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        ul li {
            padding: 6px 0;
        }

        .seat {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 48px;
            margin: 6px;
            background: rgb(192, 199, 195);
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
            font-weight: bold;
            position: relative;
            font-size: 15px;
        }

        .seat i.fa-chair {
            font-size: 24px;
            margin-bottom: 2px;
        }

        .seat-label {
            font-size: 11px;
            color: #222;
            opacity: 0.8;
            line-height: 1;
        }

        .seat.booked {
            background: #ffd600;
            /* vàng */
            cursor: not-allowed;
            color: #333;
        }

        .seat.paid {
            background: #e53935;
            /* đỏ */
            cursor: not-allowed;
            color: #fff;
        }

        .seat.selected {
            background: #4caf50;
            /* xanh lá */
            color: white;
        }

        button {
            background: rgb(56, 135, 220);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background: #0056b3;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        table td,
        table th {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }

        table th {
            background: #f0f0f0;
        }

        a {
            text-decoration: none;
            color: #0077cc;
            margin-left: 10px;
        }

        a:hover {
            text-decoration: underline;
        }

        form.inline {
            display: inline;
        }

        .notification {
            background: #e6f4ea;
            color: #1b5e20;
            padding: 12px;
            border: 1px solid #a5d6a7;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: flex-start;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 10px;
        }

        .form-group label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group select {
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            min-width: 160px;
        }

        .seats-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px 12px;
            margin-bottom: 12px;
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="flight-card">
            <h2><i class="fas fa-plane-departure"></i> Chi tiết chuyến bay: <span><?= $row['ma_cb'] ?></span></h2>

            <div class="flight-grid">
                <div class="item"><i class="fas fa-map-marker-alt"></i> <strong>Điểm đi:</strong>&nbsp;<?= $row['diem_di'] ?></div>
                <div class="item"><i class="fas fa-bullseye"></i> <strong>Điểm đến:</strong>&nbsp;<?= $row['diem_den'] ?></div>
                <div class="item"><i class="fas fa-clock"></i> <strong>Giờ đi:</strong>&nbsp;<?= $row['gio_di'] ?></div>
                <div class="item"><i class="fas fa-hourglass-end"></i> <strong>Giờ đến:</strong>&nbsp;<?= $row['gio_den'] ?></div>
                <div class="item"><i class="fas fa-building"></i> <strong>Hãng hàng không:</strong>&nbsp;<?= $row['hang_hang_khong'] ?></div>
                <div class="item"><i class="fas fa-plane"></i> <strong>Loại máy bay:</strong>&nbsp;<?= $row['loai_may_bay'] ?></div>
                <div class="item"><i class="fas fa-sack-dollar"></i> <strong>Giá vé:</strong>&nbsp;<?= number_format($row['gia'], 0, ',', '.') ?> đ</div>
            </div>

            <?php if (isset($thongbao)) echo "<div class='notification'>$thongbao</div>"; ?>


            <h3><i class="fas fa-chair"></i> Chọn ghế & đặt vé</h3>
            <form method="post" id="bookingForm">
                <div class="form-row">
                    <div class="form-group">
                        <label>Danh xưng:</label>
                        <select name="danh_xung" required>
                            <option value="">-- Chọn --</option>
                            <option value="Anh">Anh</option>
                            <option value="Chị">Chị</option>
                            <option value="Ông">Ông</option>
                            <option value="Bà">Bà</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Họ tên:</label>
                        <input type="text" name="ten" required>
                    </div>

                    <div class="form-group">
                        <label>Số điện thoại:</label>
                        <input type="text" name="sdt" required>
                    </div>

                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label>CCCD:</label>
                        <input type="text" name="cccd" required>
                    </div>
                </div>

                <input type="hidden" name="ghe_so" id="ghe_so_hidden">

                <br>
                <div id="seats" class="seats-grid">
                    <?php
                    // Sinh 80 ghế: A1-A6 ... N1-N6 (14 hàng x 6 ghế = 84), O1-O2 (2 ghế) => lấy 80 ghế đầu
                    $rows = range('A', 'N'); // A-N: 14 hàng
                    $cols = 6;
                    $seat_count = 0;
                    foreach ($rows as $r) {
                        for ($i = 1; $i <= $cols; $i++) {
                            $seat = $r . $i;
                            if (++$seat_count > 80) break 2;
                            $class = 'seat';
                            $disabled = '';
                            if (in_array($seat, $ghe_da_thanh_toan)) {
                                $class .= ' paid';
                                $disabled = 'data-disabled="1"';
                            } elseif (in_array($seat, $ghe_da_dat)) {
                                $class .= ' booked';
                                $disabled = 'data-disabled="1"';
                            }
                            echo "<div class='$class' data-seat='$seat' title='$seat' $disabled>
                                    <i class='fas fa-chair'></i>
                                    <span class='seat-label'>$seat</span>
                                  </div>";
                        }
                    }
                    if ($seat_count < 80) {
                        for ($i = 1; $i <= 2 && $seat_count < 80; $i++) {
                            $seat = 'O' . $i;
                            $class = 'seat';
                            $disabled = '';
                            if (in_array($seat, $ghe_da_thanh_toan)) {
                                $class .= ' paid';
                                $disabled = 'data-disabled="1"';
                            } elseif (in_array($seat, $ghe_da_dat)) {
                                $class .= ' booked';
                                $disabled = 'data-disabled="1"';
                            }
                            echo "<div class='$class' data-seat='$seat' title='$seat' $disabled>
                                    <i class='fas fa-chair'></i>
                                    <span class='seat-label'>$seat</span>
                                  </div>";
                            $seat_count++;
                        }
                    }
                    ?>
                </div>
                <br>
                <button type="submit">Đặt vé</button>
            </form>

            <hr>

            <h3><i class="fa-solid fa-ticket"></i> Vé bạn đã đặt</h3>
            <table>
                <tr>
                    <th><i class="fa-solid fa-barcode"></i> Mã vé</th>
                    <th><i class="fa-solid fa-user"></i> Họ tên</th>
                    <th><i class="fa-solid fa-chair"></i> Ghế</th>
                    <th><i class="fa-solid fa-circle-info"></i> Trạng thái</th>
                    <th><i class="fa-solid fa-gears"></i> Hành động</th>
                </tr>
                <?php
                if (isset($_SESSION['ten_nguoi_dung'])) {
                    $ten = $conn->real_escape_string($_SESSION['ten_nguoi_dung']);
                    $ves = $conn->query("SELECT * FROM ve WHERE chuyen_bay_id = $id AND ten_nguoi_dat = '$ten' ORDER BY id DESC");

                    if ($ves->num_rows === 0) {
                        echo "<tr><td colspan='5'><i class='fa-solid fa-magnifying-glass'></i> Bạn chưa đặt vé nào cho chuyến bay này.</td></tr>";
                    } else {
                        while ($ve = $ves->fetch_assoc()) {
                            $da_thanh_toan = $ve['trang_thai_thanh_toan'] == 1;
                            $tt = $da_thanh_toan
                                ? "<span style='color:green;'><i class='fa-solid fa-check-circle'></i> Đã thanh toán</span>"
                                : "<span style='color:red;'><i class='fa-solid fa-times-circle'></i> Chưa thanh toán</span>";

                            echo "<tr>
                    <td>{$ve['id']}</td>
                    <td>{$ve['ten_nguoi_dat']}</td>
                    <td>{$ve['ghe_so']}</td>
                    <td>$tt</td>
                    <td>";

                            if ($da_thanh_toan) {
                                // Nếu đã thanh toán
                                echo "
                        <i class='fa-solid fa-circle-check' style='color:green;'></i> Đã thanh toán<br>
                        <a href='hoa_don.php?id={$ve['id']}' target='_blank'>
                            <i class='fa-solid fa-receipt'></i> Hóa đơn
                        </a>
                        <a href='xuat_pdf.php?id={$ve['id']}' target='_blank'>
                            <i class='fa-solid fa-file-pdf'></i> In PDF
                        </a>
                    ";
                            } else {
                                // Nếu chưa thanh toán
                                echo "
                        <form method='post' class='inline' style='margin-bottom: 5px;'>
                            <input type='hidden' name='ma_ve' value='{$ve['id']}'>
                            <select name='phuong_thuc' required>
                                <option value='momo'>Ví Momo</option>
                                <option value='vnpay'>VNPay</option>
                            </select>
                            <button type='submit' name='thanh_toan'>
                                <i class='fa-solid fa-money-bill'></i> Thanh toán
                            </button>
                        </form>
                    ";

                                // Nếu là Admin => Hiển thị nút xoá luôn
                                if (isset($isAdmin) && $isAdmin) {
                                    echo "
                        <form method='post' style='display:inline;' onsubmit=\"return confirm('Admin xác nhận xoá vé này?');\">
                            <input type='hidden' name='xoa_ve' value='{$ve['id']}'>
                            <button class='btn btn-danger btn-sm'>
                                <i class='fas fa-user-shield'></i> Xoá
                            </button>
                        </form>";
                                } else {
                                    // Người dùng thường: chỉ xoá nếu chưa thanh toán
                                    echo "
                        <form method='post' style='display:inline;' onsubmit=\"return confirm('Bạn có chắc muốn huỷ vé này?');\">
                            <input type='hidden' name='xoa_ve' value='{$ve['id']}'>
                            <button class='btn btn-warning btn-sm'>
                                <i class='fas fa-ban'></i> Huỷ vé
                            </button>
                        </form>";
                                }
                            }

                            echo "</td></tr>";
                        }
                    }
                } else {
                    echo "<tr><td colspan='5'><i class='fa-solid fa-circle-info'></i> Vui lòng đặt vé trước để xem danh sách của bạn.</td></tr>";
                }
                ?>
            </table>





            <br><a href="index.php">← Quay lại danh sách</a>

            <script>
                // Cho phép chọn nhiều ghế, chỉ chọn ghế chưa đặt/chưa thanh toán
                const seats = document.querySelectorAll('.seat');
                const gheHidden = document.getElementById('ghe_so_hidden');
                let selectedSeats = [];

                seats.forEach(seat => {
                    // Không cho chọn ghế đã đặt hoặc đã thanh toán
                    if (seat.dataset.disabled === "1") return;
                    seat.addEventListener('click', () => {
                        const seatCode = seat.dataset.seat;
                        if (seat.classList.contains('selected')) {
                            seat.classList.remove('selected');
                            selectedSeats = selectedSeats.filter(s => s !== seatCode);
                        } else {
                            seat.classList.add('selected');
                            selectedSeats.push(seatCode);
                        }
                        gheHidden.value = selectedSeats.join(',');
                    });
                });

                document.getElementById('bookingForm').addEventListener('submit', function(e) {
                    if (selectedSeats.length === 0) {
                        alert('Vui lòng chọn ít nhất một ghế!');
                        e.preventDefault();
                    }
                });
            </script>
        </div>
</body>

</html>