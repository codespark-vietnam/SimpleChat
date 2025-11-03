<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';

// Báo cho PHP biết script này có thể chạy lâu
set_time_limit(40); // Cho phép chạy 40 giây (lâu hơn 30 giây ở dưới)
$my_id = $_SESSION['user_id'];

// Chúng ta sẽ lặp trong tối đa 30 giây
$start_time = time();
$end_time = $start_time + 30;

$response_data = []; // Dữ liệu sẽ trả về

try {
    while (time() < $end_time) {
        // 1. KIỂM TRA LỜI MỜI KẾT BẠN MỚI
        // (Người khác gửi cho mình)
        $stmt_pending = $conn->prepare(
            "SELECT u.user_id, u.display_name 
             FROM users u
             JOIN friendships f ON u.user_id = f.user1_id
             WHERE f.user2_id = ? AND f.status = 'pending' 
             AND f.created_at > DATE_SUB(NOW(), INTERVAL 35 SECOND)" // Chỉ lấy lời mời rất mới
        );
        $stmt_pending->execute([$my_id]);
        $new_request = $stmt_pending->fetch();

        if ($new_request) {
            $response_data = [
                'type' => 'new_friend_request',
                'sender_id' => $new_request['user_id'],
                'display_name' => $new_request['display_name']
            ];
            // Thoát vòng lặp ngay khi có dữ liệu
            break; 
        }

        // 2. KIỂM TRA TIN NHẮN MỚI
        // (Từ bất kỳ ai, mà mình chưa đọc)
        $stmt_msg = $conn->prepare(
            "SELECT * FROM messages 
             WHERE receiver_id = ? AND is_read = 0
             ORDER BY sent_at ASC"
        );
        $stmt_msg->execute([$my_id]);
        $new_messages = $stmt_msg->fetchAll();

        if (count($new_messages) > 0) {
            // Lấy tất cả tin nhắn mới và đánh dấu đã đọc
            $message_ids = array_map(fn($msg) => $msg['message_id'], $new_messages);
            $placeholders = implode(',', array_fill(0, count($message_ids), '?'));
            
            $stmt_update = $conn->prepare("UPDATE messages SET is_read = 1 WHERE message_id IN ($placeholders)");
            $stmt_update->execute($message_ids);

            $response_data = [
                'type' => 'new_messages',
                'messages' => $new_messages
            ];
            // Thoát vòng lặp ngay khi có dữ liệu
            break; 
        }

        // Nếu không có gì, ngủ 1 giây rồi lặp lại
        sleep(1);
    }
} catch (PDOException $e) {
    // Ghi lại lỗi nếu có, nhưng thường là không trả gì
}

// Trả về dữ liệu (có thể là mảng rỗng nếu timeout)
header('Content-Type: application/json');
echo json_encode($response_data);
exit;
?>