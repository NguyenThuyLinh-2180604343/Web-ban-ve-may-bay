<?php


// Hàm kiểm tra xem người dùng đã đăng nhập hay chưa
function checkLogin()
{
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit();
    }
}


// Hàm kiểm tra xem người dùng có phải là admin hay không
function checkAdmin()
{
    checkLogin(); // Kiểm tra đăng nhập trước
    if ($_SESSION["role"] !== "admin") {
        header("Location: unauthorized.php");
        exit();
    }
}

// Hàm thiết lập các biến phiên
function setSession($user)
{
    $_SESSION["user_id"] = $user["id"];
    $_SESSION["username"] = $user["username"];
    $_SESSION["role"] = $user["role"];
    $_SESSION["user"] = $user; // Thêm dòng này để lưu toàn bộ thông tin người dùng
}
?>
