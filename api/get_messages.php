<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

$my_id = $_SESSION['user_id'];
$partner_id = $_GET['partner_id'] ?? '';
$unread_only = isset($_GET['unread_only']) && $_GET['unread_only'] == 'true';

// Biến mới: Lấy tin nhắn CŨ HƠN message_id này
$before_id = $_GET['before_id'] ?? null; 

if (empty($partner_id)) {
    echo json_encode([]);
    exit;
}

$messages = [];

try {
    if ($unread_only) {
        // --- CHỈ LẤY TIN CHƯA ĐỌC (POLLING) --- (Giữ nguyên)
        $stmt = $conn->prepare(
            "SELECT * FROM messages 
             WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
             ORDER BY sent_at ASC"
        );
        $stmt->execute([$partner_id, $my_id]);
        $new_messages = $stmt->fetchAll();

        if (count($new_messages) > 0) {
            $stmt_update = $conn->prepare(
                "UPDATE messages SET is_read = 1 
                 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0"
            );
            $stmt_update->execute([$partner_id, $my_id]);
        }
        $messages_to_send = $new_messages;

    } else {
        // --- (NÂNG CẤP) LẤY LỊCH SỬ CHAT (Tải lần đầu hoặc Tải thêm) ---
        
        // Mặc định, tải 50 tin mới nhất
        $sql = "
            SELECT * FROM (
                SELECT * FROM messages 
                WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
                ORDER BY sent_at DESC
                LIMIT 50
            ) AS sub
            ORDER BY sub.sent_at ASC
        ";
        $params = [$my_id, $partner_id, $partner_id, $my_id];

        // Nếu có 'before_id', nghĩa là đang "Tải thêm"
        if ($before_id && is_numeric($before_id)) {
            $sql = "
                SELECT * FROM (
                    SELECT * FROM messages 
                    WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
                    AND message_id < ?  -- Tải tin CŨ HƠN ID này
                    ORDER BY sent_at DESC
                    LIMIT 50
                ) AS sub
                ORDER BY sub.sent_at ASC
            ";
            // Thêm $before_id vào mảng tham số
            $params = [$my_id, $partner_id, $partner_id, $my_id, $before_id];
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $all_messages = $stmt->fetchAll();
        
        // Chỉ đánh dấu đã đọc khi tải lần đầu (không phải "tải thêm")
        if ($before_id === null) {
            $stmt_update = $conn->prepare(
                "UPDATE messages SET is_read = 1 
                 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0"
            );
            $stmt_update->execute([$partner_id, $my_id]);
        }
        
        $messages_to_send = $all_messages;
    }

    // Thêm trường 'type' ('sent' or 'received')
    $final_messages = [];
    foreach ($messages_to_send as $msg) {
        $msg['type'] = ($msg['sender_id'] == $my_id) ? 'sent' : 'received';
        $final_messages[] = $msg;
    }
    
    echo json_encode($final_messages);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>