<?php
include 'database/db.php';

$thong_bao = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Kiểm tra trùng tên đăng nhập trước khi thêm
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $thong_bao = "<div class='error'> Tên đăng nhập đã tồn tại.</div>";
    } else {
        // Mặc định role là 'user'
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            $thong_bao = "<div class='success'> Đăng ký thành công. <a href='login.php'>Đăng nhập</a></div>";
        } else {
            $thong_bao = "<div class='error'> Đã xảy ra lỗi khi đăng ký.</div>";
        } 
    }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* 1. Nền toàn trang là ảnh máy bay */
        body {
            margin: 0;
            padding: 0;
            height: 100vh;s
            font-family: Arial, sans-serif;
            background: url('image/B95AFAA3-B1F5-4C6F-AC8D-A558F9FCF859.jpeg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* 2. Khung form màu trắng đục */
        .form-container {
            background: rgba(252, 253, 254, 0.95);
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color:rgb(44, 50, 59);
        }

        .input-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 12px;
            background:rgb(57, 62, 69);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background:rgb(71, 87, 98);
        }

        .link {
            text-align: center;
            margin-top: 15px;
        }

        .link a {
            color:rgb(10, 91, 183);
            text-decoration: none;
        }

        .link a:hover {
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
    <h2> Đăng ký tài khoản</h2>

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

        <button type="submit"><i class="fas fa-user-plus"></i> Đăng ký</button>

        <div class="link">
            Đã có tài khoản? <a href="login.php">Đăng nhập</a>
        </div>
    </form>
</div>

</body>
</html>
