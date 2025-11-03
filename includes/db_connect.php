<?php
// Tên CSDL mà chúng ta sẽ tạo và sử dụng
$dbname = "simplechat"; 
// Thông tin đăng nhập XAMPP mặc định
$servername = "localhost";
$username = "root";
$password = "";

try {
    // Tạo đối tượng PDO (PHP Data Objects)
    // 'mysql:host=...' là chuỗi kết nối (DSN)
    // 'charset=utf8mb4' RẤT QUAN TRỌNG: để hỗ trợ lưu trữ Emoji
    $conn = new PDO(
        "mysql:host=$servername;dbname=$dbname;charset=utf8mb4", 
        $username, 
        $password
    );

    // Thiết lập chế độ báo lỗi của PDO thành Exception
    // Điều này có nghĩa là nếu có lỗi SQL, script sẽ dừng lại và báo lỗi
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Đặt chế độ lấy dữ liệu mặc định là mảng kết hợp (associative array)
    // Nghĩa là khi SELECT, bạn sẽ lấy dữ liệu bằng tên cột (vd: $row['username'])
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    // Nếu kết nối thất bại, hiện thông báo lỗi và thoát
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}

// Nếu kết nối thành công, file này sẽ không 'echo' ra bất cứ thứ gì.
// Biến $conn sẽ sẵn sàng để các file khác sử dụng.
?>