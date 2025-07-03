<?php
include '../database/db.php';
require_once "session.php";

session_start();

// Kiểm tra nếu chưa đăng nhập hoặc không phải admin thì chặn
checkAdmin();
$id = $_GET['id'];
$conn->query("DELETE FROM chuyen_bay WHERE id = $id");
header("Location: ../index.php");
exit;
