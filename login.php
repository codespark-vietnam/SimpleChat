<?php
// LUÔN LUÔN BẮT ĐẦU SESSION Ở ĐẦU
session_start();

// Nếu người dùng đã đăng nhập rồi, tự động chuyển họ đến trang chat chính
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit; // Dừng script
}

// Gọi file kết nối CSDL
require_once 'includes/db_connect.php';

$error_message = '';

// Kiểm tra xem form đã được gửi (POST) chưa
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Xác thực cơ bản
    if (empty($username) || empty($password)) {
        $error_message = "Vui lòng nhập cả tên đăng nhập và mật khẩu.";
    } else {
        try {
            // 1. Tìm người dùng bằng 'username'
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            
            $user = $stmt->fetch(); 

            // 2. Kiểm tra xem người dùng có tồn tại KHÔNG
            // VÀ kiểm tra mật khẩu có khớp VỚI HASH trong CSDL không
            if ($user && password_verify($password, $user['password'])) {
                
                // 3. Đăng nhập thành công!
                // Lưu thông tin quan trọng vào SESSION
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['display_name'] = $user['display_name'];
                $_SESSION['friend_code'] = $user['friend_code'];

                // 4. Chuyển hướng người dùng đến trang chat chính
                header("Location: index.php");
                exit; 

            } else {
                // Đăng nhập thất bại
                $error_message = "Tên đăng nhập hoặc mật khẩu không chính xác.";
            }

        } catch (PDOException $e) {
            $error_message = "Lỗi hệ thống: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - SimpleChat</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/main.js" defer></script>
</head>
<body class="auth-page"> <div class="auth-container"> 
        
        <div class="auth-box">
            <h2>Đăng Nhập</h2>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="username">Tên đăng nhập:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mật khẩu:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <?php if ($error_message): ?>
                    <div class="message error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <button type="submit" class="btn">Đăng Nhập</button>
            </form>

            <p>
                Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
            </p>
        </div>
        
    </div>

</body>
</html>