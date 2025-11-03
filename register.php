<?php
// Bắt đầu session (luôn đặt ở đầu file)
session_start();

// Nếu người dùng đã đăng nhập, đẩy về trang index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Gọi file kết nối CSDL
require_once 'includes/db_connect.php';

// Khởi tạo các biến để chứa thông báo
$error_message = '';
$success_message = '';

// Kiểm tra xem người dùng đã bấm nút 'submit' (POST) chưa
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Lấy dữ liệu từ form và làm sạch cơ bản
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $display_name = trim($_POST['display_name']);

    // 2. Xác thực dữ liệu (Validation)
    if (empty($username) || empty($password) || empty($display_name)) {
        $error_message = "Vui lòng nhập đầy đủ thông tin.";
    } 
    elseif (strlen($username) < 4) {
        $error_message = "Tên đăng nhập phải có ít nhất 4 ký tự.";
    }
    elseif (strlen($password) < 6) {
        $error_message = "Mật khẩu phải có ít nhất 6 ký tự.";
    }
    else {
        // 3. Kiểm tra xem 'username' đã tồn tại chưa
        try {
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
            $stmt->execute([$username]);

            if ($stmt->rowCount() > 0) {
                // Tên đăng nhập đã tồn tại
                $error_message = "Tên đăng nhập này đã được sử dụng.";
            } else {
                // 4. Tên đăng nhập hợp lệ -> Bắt đầu tạo người dùng mới

                // Băm mật khẩu
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // 5. Tạo 'friend_code' duy nhất
                $friend_code = '';
                $is_code_unique = false;
                while (!$is_code_unique) {
                    $friend_code = strtoupper(bin2hex(random_bytes(4)));
                    $stmt_code = $conn->prepare("SELECT user_id FROM users WHERE friend_code = ?");
                    $stmt_code->execute([$friend_code]);
                    
                    if ($stmt_code->rowCount() == 0) {
                        $is_code_unique = true; // Mã này là duy nhất
                    }
                }

                // 6. Chèn người dùng mới vào CSDL
                $stmt_insert = $conn->prepare(
                    "INSERT INTO users (username, password, display_name, friend_code) 
                     VALUES (?, ?, ?, ?)"
                );
                $stmt_insert->execute([
                    $username, 
                    $hashed_password, 
                    $display_name, 
                    $friend_code
                ]);

                // Ghi nhận thành công
                $success_message = "Đăng ký thành công! Mã kết bạn của bạn là: $friend_code. 
                                    Bây giờ bạn có thể <a href='login.php'>đăng nhập</a>.";

            }
        } catch(PDOException $e) {
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
    <title>Đăng ký - SimpleChat</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/main.js" defer></script>
</head>
<body class="auth-page"> <div class="auth-container">
        
        <div class="auth-box">
            <h2>Đăng Ký Tài Khoản</h2>

            <form action="register.php" method="POST">
                <div class="form-group">
                    <label for="username">Tên đăng nhập:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="display_name">Tên hiển thị:</label>
                    <input type="text" id="display_name" name="display_name" required>
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <?php if ($error_message): ?>
                    <div class="message error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div class="message success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if (empty($success_message)): ?>
                    <button type="submit" class="btn">Đăng Ký</button>
                <?php endif; ?>
            </form>

            <p>
                Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
            </p>
        </div>

    </div>

</body>
</html>