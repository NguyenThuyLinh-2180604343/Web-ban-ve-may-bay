<?php include 'database/db.php'; ?>
<?php
session_start();
// Nếu chưa đăng nhập, chuyển hướng sang trang đăng nhập (vd: login.php)
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>

<?php include 'header.php'; ?>


<body>
    <section class="home" id="home">

        <!-- Bỏ phần content giới thiệu -->

        <!-- Slider ảnh tự động chuyển đổi, kích thước vừa phải khớp với trang -->
        <div class="image-slider" style="position:relative; width:100%; max-width:1200px; height:600px; margin:0 auto; overflow:hidden;">
            <img src="image/B95AFAA3-B1F5-4C6F-AC8D-A558F9FCF859.jpeg" alt="Slide 1" class="slide-img" style="width:100%; height:600px; object-fit:cover; display:block; border-radius:16px;">
        </div>
        <script>
            // Slider ảnh tự động chuyển đổi, không có nút điều khiển
            const slides = document.querySelectorAll('.slide-img');
            let current = 0;
            function showSlide(idx) {
                slides.forEach((img, i) => {
                    img.style.display = i === idx ? 'block' : 'none';
                });
                current = idx;
            }
            setInterval(() => {
                showSlide((current + 1) % slides.length);
            }, 3500);
            showSlide(0);
        </script>
    </section>

    <section class="book" id="book">

        <h1 class="heading" style="background:#e3f2fd; color:#222; letter-spacing:2px; font-size:2.2em; text-align:center; margin-bottom:24px; border-radius:10px; padding:12px 0;">
            <span style="color:#222; background:none;">b</span>
            <span style="color:#222; background:none;">o</span>
            <span style="color:#222; background:none;">o</span>
            <span style="color:#222; background:none;">k</span>
            <span class="space" style="background:none;"></span>
            <span style="color:#222; background:none;">n</span>
            <span style="color:#222; background:none;">o</span>
            <span style="color:#222; background:none;">w</span>
        </h1>

        <div class="row" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:center; gap:40px;">
            <div class="image" style="flex:1 1 360px; min-width:300px; max-width:360px; text-align:center; display:flex; align-items:center; justify-content:center; height:320px;">
                <img src="image/1605158418719.jpg" alt="" style="width:100%; height:100%; object-fit:cover; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
            </div>

            <form action="showticket.php" method="get" style="flex:1 1 420px; min-width:320px; background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.07); padding:36px 32px; display:flex; flex-direction:column; gap:32px; font-size:1.55em;">
                <div style="display:flex; gap:18px;">
                    <div class="inputBox" style="flex:1;">
                        <h3 style="color:#222; font-size:1.22em; margin-bottom:12px;">From</h3>
                        <input type="text" name="diem_di" placeholder="Enter departure" style="padding:16px 20px; border:1px solid #ccc; border-radius:6px; font-size:1.08em; color:#222; width:100%;">
                    </div>
                    <div class="inputBox" style="flex:1;">
                        <h3 style="color:#222; font-size:1.22em; margin-bottom:12px;">To</h3>
                        <input type="text" name="diem_den" placeholder="Enter destination" style="padding:16px 20px; border:1px solid #ccc; border-radius:6px; font-size:1.08em; color:#222; width:100%;">
                    </div>
                </div>
                <div class="inputBox" style="margin-bottom:0;">
                    <h3 style="color:#222; font-size:1.22em; margin-bottom:12px;">Departure date</h3>
                    <input type="date" name="ngay_di" style="padding:16px 20px; border:1px solid #ccc; border-radius:6px; font-size:1.08em; color:#222; width:100%;">
                </div>
                <input type="submit" class="btn" value="Search flights" style="background:#0077cc; color:#fff; border:none; border-radius:6px; padding:18px 0; font-size:1.18em; font-weight:bold; cursor:pointer; margin-top:10px;">
            </form>
        </div>

    </section>


    <?php include 'flooter.php'; ?>

    </html>