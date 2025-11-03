<?php
session_start(); // Bắt đầu session

session_unset(); // Xóa tất cả các biến trong session

session_destroy(); // Hủy session

// Chuyển hướng về trang đăng nhập
header("Location: login.php");
exit;
?>