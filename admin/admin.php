<?php
session_start();
include 'database/db.php';

// Kiểm tra nếu chưa đăng nhập hoặc không phải admin
checkAdmin();


// Gán quyền admin cho người dùng cụ thể
$username = 'linh';

$stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE username = ?");   
$stmt->bind_param("s", $username);

if ($stmt->execute()) {
    echo "✅ Đã cập nhật quyền admin cho người dùng $username.";
} else {
    echo "❌ Lỗi khi cập nhật quyền.";
}
?>
