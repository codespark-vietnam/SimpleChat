<?php
// Chúng ta kiểm tra xem session đã được start chưa
// (để tránh lỗi nếu gọi session_start() nhiều lần)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Nếu không tồn tại 'user_id' trong session
if (!isset($_SESSION['user_id'])) {
    // Chuyển hướng người dùng về trang đăng nhập
    header("Location: login.php");
    exit; // Dừng script ngay lập tức
}

// Nếu đã đăng nhập, không làm gì cả, script gọi file này sẽ tiếp tục chạy
?>