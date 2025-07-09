<?php
session_start();
require_once "../database/db.php";
require_once "session.php";
checkLogin();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Thống kê doanh thu
// Doanh thu chỉ tính các vé đã thanh toán (trang_thai_thanh_toan = 1)
function getDoanhThu($conn, $groupBy)
{
    $sql = "SELECT 
                ";
    if ($groupBy === 'day') {
        $sql .= "DATE(ve.created_at) AS label, ";
    } elseif ($groupBy === 'month') {
        $sql .= "DATE_FORMAT(ve.created_at, '%Y-%m') AS label, ";
    } else { // year
        $sql .= "YEAR(ve.created_at) AS label, ";
    }
    $sql .= "SUM(cb.gia) AS doanh_thu
            FROM ve
            JOIN chuyen_bay cb ON ve.chuyen_bay_id = cb.id
            WHERE ve.trang_thai_thanh_toan = 1
            GROUP BY label
            ORDER BY label DESC";
    return $conn->query($sql);
}

$doanhthu_ngay = getDoanhThu($conn, 'day');
$doanhthu_thang = getDoanhThu($conn, 'month');
$doanhthu_nam = getDoanhThu($conn, 'year');
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Thống kê doanh thu</title>
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

        .back-link {
            display: inline-block;
            margin-top: 24px;
            color: #0077cc;
            text-decoration: underline;
            font-weight: bold;
        }

        .tab {
            display: inline-block;
            margin: 0 10px 20px 0;
            padding: 8px 18px;
            border-radius: 6px;
            background: #e3eafc;
            color: #0077cc;
            font-weight: bold;
            cursor: pointer;
        }

        .tab.active {
            background: #0077cc;
            color: #fff;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .chart-container {
            width: 100%;
            max-width: 700px;
            margin: 30px auto 0 auto;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab');
            const contents = document.querySelectorAll('.tab-content');
            tabs.forEach((tab, idx) => {
                tab.addEventListener('click', function() {
                    tabs.forEach(t => t.classList.remove('active'));
                    contents.forEach(c => c.classList.remove('active'));
                    tab.classList.add('active');
                    contents[idx].classList.add('active');
                    // Vẽ lại biểu đồ khi chuyển tab
                    if (window.drawTabChart) setTimeout(() => drawTabChart(idx), 100);
                });
            });
            // Mặc định tab đầu tiên active
            if (tabs.length) {
                tabs[0].classList.add('active');
                contents[0].classList.add('active');
            }

            // Chart rendering
            function renderChart(ctxId, labels, data, label) {
                new Chart(document.getElementById(ctxId), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: label,
                            data: data,
                            backgroundColor: '#0077cc'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value.toLocaleString() + ' đ';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Dữ liệu biểu đồ từ PHP
            const chartData = {
                ngay: <?php
                        $doanhthu_ngay->data_seek(0);
                        $arr = [];
                        while ($row = $doanhthu_ngay->fetch_assoc()) $arr[] = $row;
                        echo json_encode($arr);
                        ?>,
                thang: <?php
                        $doanhthu_thang->data_seek(0);
                        $arr = [];
                        while ($row = $doanhthu_thang->fetch_assoc()) $arr[] = $row;
                        echo json_encode($arr);
                        ?>,
                nam: <?php
                        $doanhthu_nam->data_seek(0);
                        $arr = [];
                        while ($row = $doanhthu_nam->fetch_assoc()) $arr[] = $row;
                        echo json_encode($arr);
                        ?>
            };

            window.drawTabChart = function(tabIdx) {
                let ctxId, dataArr, label;
                if (tabIdx === 0) {
                    ctxId = 'chart-ngay';
                    dataArr = chartData.ngay;
                    label = 'Doanh thu theo ngày';
                } else if (tabIdx === 1) {
                    ctxId = 'chart-thang';
                    dataArr = chartData.thang;
                    label = 'Doanh thu theo tháng';
                } else {
                    ctxId = 'chart-nam';
                    dataArr = chartData.nam;
                    label = 'Doanh thu theo năm';
                }
                if (dataArr && document.getElementById(ctxId)) {
                    renderChart(
                        ctxId,
                        dataArr.map(r => r.label),
                        dataArr.map(r => r.doanh_thu),
                        label
                    );
                }
            };

            // Vẽ biểu đồ cho tab đầu tiên khi load
            drawTabChart(0);
        });
    </script>
</head>

<body>
    <div class="container">
        <h2><i class="fa-solid fa-chart-line"></i> Thống kê doanh thu</h2>
        <div>
            <span class="tab">Theo ngày</span>
            <span class="tab">Theo tháng</span>
            <span class="tab">Theo năm</span>
        </div>
        <div class="tab-content">
            <div class="chart-container"><canvas id="chart-ngay"></canvas></div>
            <table>
                <tr>
                    <th>Ngày</th>
                    <th>Doanh thu</th>
                </tr>
                <?php $doanhthu_ngay->data_seek(0);
                while ($row = $doanhthu_ngay->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['label']) ?></td>
                        <td><?= number_format($row['doanh_thu'], 0, ',', '.') ?> đ</td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <div class="tab-content">
            <div class="chart-container"><canvas id="chart-thang"></canvas></div>
            <table>
                <tr>
                    <th>Tháng</th>
                    <th>Doanh thu</th>
                </tr>
                <?php $doanhthu_thang->data_seek(0);
                while ($row = $doanhthu_thang->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['label']) ?></td>
                        <td><?= number_format($row['doanh_thu'], 0, ',', '.') ?> đ</td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <div class="tab-content">
            <div class="chart-container"><canvas id="chart-nam"></canvas></div>
            <table>
                <tr>
                    <th>Năm</th>
                    <th>Doanh thu</th>
                </tr>
                <?php $doanhthu_nam->data_seek(0);
                while ($row = $doanhthu_nam->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['label']) ?></td>
                        <td><?= number_format($row['doanh_thu'], 0, ',', '.') ?> đ</td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <a href="sanphamadmin.php" class="back-link">← Quay lại quản lý chuyến bay</a>
    </div>
</body>

</html>