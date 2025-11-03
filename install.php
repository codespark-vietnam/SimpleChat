<?php
// Thông tin XAMPP
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "simplechat"; // Tên CSDL chúng ta muốn tạo

try {
    // Bước 1: Kết nối tới MySQL Server (chưa chọn CSDL)
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Bước 2: Tạo CSDL 'simplechat' nếu nó chưa tồn tại
    // Dùng utf8mb4_unicode_ci để hỗ trợ đầy đủ Tiếng Việt và Emoji
    $conn->exec("CREATE DATABASE IF NOT EXISTS `$dbname` 
                 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    
    // Bước 3: Sử dụng CSDL vừa tạo
    $conn->exec("USE `$dbname`;");

    echo "<h3>Đã tạo/kết nối CSDL '$dbname' thành công!</h3>";

    // Bước 4: Tạo bảng 'users'
    $sql_users = "
    CREATE TABLE IF NOT EXISTS `users` (
      `user_id` INT AUTO_INCREMENT PRIMARY KEY,
      `username` VARCHAR(50) NOT NULL UNIQUE,
      `password` VARCHAR(255) NOT NULL,
      `display_name` VARCHAR(100) NOT NULL,
      `friend_code` VARCHAR(20) NOT NULL UNIQUE,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->exec($sql_users);
    echo "Đã tạo bảng 'users' thành công.<br>";

    // Bước 5: Tạo bảng 'friendships'
    $sql_friendships = "
    CREATE TABLE IF NOT EXISTS `friendships` (
      `friendship_id` INT AUTO_INCREMENT PRIMARY KEY,
      `user1_id` INT NOT NULL,
      `user2_id` INT NOT NULL,
      `status` ENUM('pending', 'accepted', 'blocked') NOT NULL DEFAULT 'pending',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (user1_id) REFERENCES users(user_id) ON DELETE CASCADE,
      FOREIGN KEY (user2_id) REFERENCES users(user_id) ON DELETE CASCADE,
      UNIQUE KEY `unique_friendship` (`user1_id`, `user2_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->exec($sql_friendships);
    echo "Đã tạo bảng 'friendships' thành công.<br>";

    // Bước 6: Tạo bảng 'messages'
    $sql_messages = "
    CREATE TABLE IF NOT EXISTS `messages` (
      `message_id` INT AUTO_INCREMENT PRIMARY KEY,
      `sender_id` INT NOT NULL,
      `receiver_id` INT NOT NULL,
      `message_text` TEXT NOT NULL,
      `is_read` TINYINT(1) DEFAULT 0,
      `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
      FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->exec($sql_messages);
    echo "Đã tạo bảng 'messages' thành công.<br>";

    echo "<hr><h2>Cài đặt hoàn tất!</h2>";
    echo "<p style='color:red; font-weight:bold;'>BÂY GIỜ HÃY XÓA FILE 'install.php' NÀY NGAY LẬP TỨC VÌ LÝ DO BẢO MẬT!</p>";

} catch(PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}

// Đóng kết nối
$conn = null;
?>