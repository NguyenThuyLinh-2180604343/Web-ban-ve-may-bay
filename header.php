<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>complete responsive tour and travel agency website design tutorial</title>

    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="style.css">

</head>
<body>
    
<!-- header section starts  -->

<header>

    <div id="menu-bar" class="fas fa-bars"></div>

    <a href="index.php" class="logo"><span>T</span>ravel</a>


    <nav class="navbar">
        <a href="#home">home</a>
        <a href="#book">book</a>
        <a href="#packages">packages</a>
        <a href="#services">services</a>
        <a href="#gallery">gallery</a>
        <a href="#review">review</a>
        <a href="#contact">contact</a>
    </nav>

    <div class="icons">
        <i class="fas fa-search" id="search-btn"></i>
       <div style="position: relative; display: inline-block;">
    <i class="fas fa-user" id="login-btn" style="cursor: pointer;"></i>
    
    <?php if (isset($_SESSION['username'])): ?>
        <div id="user-menu" style="display: none; position: absolute; top: 30px; right: 0; background: white; border: 1px solid #ccc; padding: 10px; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); z-index: 100;">
            👋 <strong><?= $_SESSION['username'] ?></strong>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <span style="color: red;">(admin)</span>
            <?php endif; ?>
            <br>
            <a href="logout.php">Đăng xuất</a>
        </div>
    <?php endif; ?>
</div>

    <form action="" class="search-bar-container">
        <input type="search" id="search-bar" placeholder="search here...">
        <label for="search-bar" class="fas fa-search"></label>
    </form>

</header>

<!-- header section ends -->

<!-- login form container  -->

<div class="login-form-container">

    <i class="fas fa-times" id="form-close"></i>

    <form action="">
        <h3>login</h3>
        <input type="email" class="box" placeholder="enter your email">
        <input type="password" class="box" placeholder="enter your password">
        <input type="submit" value="login now" class="btn">
        <input type="checkbox" id="remember">
        <label for="remember">remember me</label>
        <p>forget password? <a href="#">click here</a></p>
        <p>don't have and account? <a href="#">register now</a></p>
    </form>

</div>
<script>
    document.getElementById('login-btn').addEventListener('click', function () {
        const menu = document.getElementById('user-menu');
        if (menu) {
            menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
        }
    });

    // Ẩn khi click ngoài
    document.addEventListener('click', function (event) {
        const loginBtn = document.getElementById('login-btn');
        const menu = document.getElementById('user-menu');
        if (menu && !loginBtn.contains(event.target) && !menu.contains(event.target)) {
            menu.style.display = 'none';
        }
    });
</script>
