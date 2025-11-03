<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

$my_id = $_SESSION['user_id']; // Mình là người nhận (user2)
$sender_id = $_POST['sender_id'] ?? ''; // Người gửi (user1)

if (empty($sender_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Thiếu ID người gửi.']);
    exit;
}

try {
    // Cập nhật trạng thái 'pending' -> 'accepted'
    // Phải chắc chắn là HỌ gửi cho MÌNH
    $stmt = $conn->prepare(
        "UPDATE friendships 
         SET status = 'accepted' 
         WHERE user1_id = ? AND user2_id = ? AND status = 'pending'"
    );
    $stmt->execute([$sender_id, $my_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Kết bạn thành công.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy lời mời hoặc đã chấp nhận.']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>