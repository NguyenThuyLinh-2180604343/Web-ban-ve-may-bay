<?php include 'database/db.php'; ?>
<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}
?>

<?php include 'header.php'; ?>


<body>
    <section class="home" id="home">

    <div class="content">
        <h3>adventure is worthwhile</h3>
        <p>dicover new places with us, adventure awaits</p>
        <a href="#" class="btn">discover more</a>
    </div>

    <div class="controls">
        <span class="vid-btn active" data-src="image/B95AFAA3-B1F5-4C6F-AC8D-A558F9FCF859.jpeg"></span>
        <span class="vid-btn" data-src="images/vid-2.mp4"></span>
        <span class="vid-btn" data-src="images/vid-3.mp4"></span>
        <span class="vid-btn" data-src="images/vid-4.mp4"></span>
        <span class="vid-btn" data-src="images/vid-5.mp4"></span>
    </div>

    <div class="video-container">
        <video src="images/vid-1.mp4" id="video-slider" loop autoplay muted></video>
    </div>

</section>

<section class="book" id="book">

    <h1 class="heading">
        <span>b</span>
        <span>o</span>
        <span>o</span>
        <span>k</span>
        <span class="space"></span>
        <span>n</span>
        <span>o</span>
        <span>w</span>
    </h1>

    <div class="row">

        <div class="image">
            <img src="images/book-img.svg" alt="">
        </div>

      <form action="showticket.php" method="get">
    <div class="inputBox">
        <h3>Điểm đi</h3>
        <input type="text" name="diem_di" placeholder="place name">
    </div>
    <div class="inputBox">
        <h3>Điểm đến</h3>
        <input type="text" name="diem_den" placeholder="place name">
    </div>
    <div class="inputBox">
        <h3>Ngày đi</h3>
        <input type="date" name="ngay_di">
    </div>
    
    <input type="submit" class="btn" value="Tìm chuyến bay">
</form>

    </div>

</section>
    <?php if (isset($_SESSION['username'])): ?>
    <p>👋 Xin chào, <strong><?= $_SESSION['username'] ?></strong> | <a href="logout.php">Đăng xuất</a></p>
    <?php endif; 
?>
    <h2>📋 Danh sách chuyến bay</h2>
    <form method="get">
    🔍 Tìm kiếm: 
    <input type="text" name="keyword" value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>" placeholder="Mã CB, điểm đi, hãng...">
    <button type="submit">Tìm</button>
</form>
<br>

     <a href="admin/sanphamadmin.php" class="btn">➕ admin</a>
    <br><br>
    <table>
        <tr>
            <th>Mã CB</th><th>Điểm đi</th><th>Điểm đến</th><th>Giờ đi</th><th>Giờ đến</th>
            <th>Hãng</th><th>Loại</th><th>Giá</th><th>Hành động</th>
        </tr>
        <?php
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
if ($keyword !== '') {
    $keyword = $conn->real_escape_string($keyword);
    $sql = "SELECT * FROM chuyen_bay 
            WHERE ma_cb LIKE '%$keyword%' 
            OR diem_di LIKE '%$keyword%' 
            OR diem_den LIKE '%$keyword%'
            OR hang_hang_khong LIKE '%$keyword%'";
} else {
    $sql = "SELECT * FROM chuyen_bay";
}
$result = $conn->query($sql);

        while ($row = $result->fetch_assoc()):
        ?>
        <tr>
            <td><?= $row['ma_cb'] ?></td>
            <td><?= $row['diem_di'] ?></td>
            <td><?= $row['diem_den'] ?></td>
            <td><?= $row['gio_di'] ?></td>
            <td><?= $row['gio_den'] ?></td>
            <td><?= $row['hang_hang_khong'] ?></td>
            <td><?= $row['loai_may_bay'] ?></td>
            <td><?= number_format($row['gia'], 0, ',', '.') ?> đ</td>
            <td><a href="chitiet.php?id=<?= $row['id'] ?>" class="btn">🔍</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>

<?php include 'flooter.php'; ?>
</html>
