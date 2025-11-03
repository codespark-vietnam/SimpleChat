<?php
// Bước 1: Bảo vệ trang
session_start();
require_once 'includes/auth_check.php'; // "Bảo vệ"
require_once 'includes/db_connect.php'; // Kết nối CSDL

// Lấy thông tin user từ session để sử dụng
$my_id = $_SESSION['user_id'];
$my_display_name = htmlspecialchars($_SESSION['display_name']); // Dùng htmlspecialchars để tránh lỗi XSS
$my_friend_code = htmlspecialchars($_SESSION['friend_code']);

// Khởi tạo các mảng
$pending_requests = [];
$friends = [];

try {
    // Bước 2: Lấy danh sách LỜI MỜI KẾT BẠN (người khác gửi cho mình)
    // 'u.user_id' là ID của người gửi (user1)
    $stmt_pending = $conn->prepare(
        "SELECT u.user_id, u.display_name 
         FROM users u
         JOIN friendships f ON u.user_id = f.user1_id
         WHERE f.user2_id = ? AND f.status = 'pending'"
    );
    $stmt_pending->execute([$my_id]);
    $pending_requests = $stmt_pending->fetchAll();

    // Bước 3: Lấy danh sách BẠN BÈ (đã 'accepted')
    // Câu query JOIN phức tạp để lấy bạn bè bất kể họ là user1 hay user2
    $stmt_friends = $conn->prepare(
        "SELECT u.user_id, u.display_name
         FROM users u
         JOIN friendships f ON (f.user1_id = u.user_id OR f.user2_id = u.user_id)
         WHERE (f.user1_id = ? OR f.user2_id = ?)
         AND f.status = 'accepted'
         AND u.user_id != ?"
    );
    $stmt_friends->execute([$my_id, $my_id, $my_id]);
    $friends = $stmt_friends->fetchAll();

} catch (PDOException $e) {
    // Xử lý lỗi nếu có
    echo "Lỗi khi tải dữ liệu: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimpleChat</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/main.js" defer></script>
</head>
<body class="chat-page"> <div class="chat-container">
        <div class="sidebar">
            
            <div class="user-profile">
                <h4><?php echo $my_display_name; ?></h4>
                <p>Mã kết bạn: <strong><?php echo $my_friend_code; ?></strong></p>
                <a href="logout.php" class="logout-btn">Đăng xuất</a>
            </div>

            <div class="add-friend-section">
                <h5>Thêm bạn</h5>
                <form id="add-friend-form">
                    <input type="text" id="friend-code-input" placeholder="Nhập mã kết bạn..." autocomplete="off">
                    <button type="submit">Thêm</button>
                </form>
                <div id="add-friend-status" class="status-message"></div>
            </div>

            <div class="friend-list-section">
                <h5>Lời mời đang chờ (<?php echo count($pending_requests); ?>)</h5>
                <ul id="pending-list">
                    <?php if (empty($pending_requests)): ?>
                        <li class="empty-list-msg">Không có lời mời nào.</li>
                    <?php else: ?>
                        <?php foreach ($pending_requests as $request): ?>
                            <li class="pending-item">
                                <span><?php echo htmlspecialchars($request['display_name']); ?></span>
                                <button class="accept-btn" data-sender-id="<?php echo $request['user_id']; ?>">
                                    Chấp nhận
                                </button>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="friend-list-section">
                <h5>Bạn bè (<?php echo count($friends); ?>)</h5>
                <ul id="friend-list">
                    <?php if (empty($friends)): ?>
                        <li class="empty-list-msg">Bạn chưa có bạn bè nào.</li>
                    <?php else: ?>
                        <?php foreach ($friends as $friend): ?>
                            <li class="friend-item" data-partner-id="<?php echo $friend['user_id']; ?>" data-partner-name="<?php echo htmlspecialchars($friend['display_name']); ?>">
                                <span><?php echo htmlspecialchars($friend['display_name']); ?></span>
                                </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

        </div>

        <div class="chat-area">
            
            <div id="chat-welcome-screen" class="chat-welcome">
                <h2>Chào mừng bạn đến với SimpleChat!</h2>
                <p>Chọn một người bạn từ danh sách bên trái để bắt đầu trò chuyện.</p>
            </div>

            <div id="chat-main-screen" class="chat-main" style="display: none;">
                <div class="chat-header">
                    <h3 id="chat-partner-name"></h3>
                </div>

                <div class="chat-messages" id="chat-messages-window">
                    <div class="load-more-container">
                        <button id="load-more-btn" style="display: none;">Tải thêm tin nhắn cũ</button>
                    </div>
                    </div>

                <form class="chat-input-form" id="chat-form">
                    <input type="hidden" id="chat-receiver-id" value="">
                    <input type="text" id="chat-message-input" placeholder="Nhập tin nhắn..." autocomplete="off">
                    <button type="submit">Gửi</button>
                </form>
            </div>

        </div>
    </div>

</body>
</html>