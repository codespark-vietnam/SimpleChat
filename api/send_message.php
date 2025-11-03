<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'] ?? '';
$message_text = trim($_POST['message_text'] ?? '');

if (empty($receiver_id) || empty($message_text)) {
    echo json_encode(['status' => 'error', 'message' => 'Tin nhắn hoặc người nhận rỗng.']);
    exit;
}

try {
    // (Kiểm tra thêm) Bạn có phải là bạn bè với người này không?
    // (Bỏ qua bước này cho đơn giản, nhưng nên có trong thực tế)

    // Chèn tin nhắn vào CSDL
    $stmt = $conn->prepare(
        "INSERT INTO messages (sender_id, receiver_id, message_text, is_read) 
         VALUES (?, ?, ?, 0)" // is_read = 0 (chưa đọc)
    );
    $stmt->execute([$sender_id, $receiver_id, $message_text]);

    echo json_encode(['status' => 'success', 'message' => 'Đã gửi.']);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>