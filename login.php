<?php
require_once 'database/db.php';
require_once "admin/session.php";
session_start();

$thong_bao = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
    
            setSession($user); // Sử dụng hàm setSession để lưu thông tin người dùng vào phiên
            header("Location: index.php");
            exit;
        } else {
            $thong_bao = "<div class='error'>❌ Sai mật khẩu.</div>";
        }
    } else {
        $thong_bao = "<div class='error'>❌ Không tìm thấy người dùng.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background:rgb(207, 228, 232);
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color:rgb(76, 95, 114);
        }

        .form-container .input-group {
            margin-bottom: 15px;
        }

        .form-container label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        .form-container input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-container button {
            width: 100%;
            padding: 12px;
            background:rgb(57, 71, 85);
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
        }

        .form-container button:hover {
            background:rgb(63, 75, 88);
        }

        .form-container .link {
            text-align: center;
            margin-top: 15px;
        }

        .form-container .link a {
            color:rgb(40, 121, 201);
            text-decoration: none;
        }

        .form-container .link a:hover {
            text-decoration: underline;
        }

        .success, .error {
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Đăng nhập</h2>

    <?= $thong_bao ?>

    <form method="post">
        <div class="input-group">
            <label for="username"><i class="fas fa-user"></i> Tên đăng nhập</label>
            <input type="text" name="username" id="username" required>
        </div>

        <div class="input-group">
            <label for="password"><i class="fas fa-lock"></i> Mật khẩu</label>
            <input type="password" name="password" id="password" required>
        </div>

        <button type="submit"><i class="fas fa-sign-in-alt"></i> Đăng nhập</button>

        <div class="link">
            Chưa có tài khoản? <a href="register.php">Đăng ký</a>
        </div>
    </form>
</div>

</body>
</html>
