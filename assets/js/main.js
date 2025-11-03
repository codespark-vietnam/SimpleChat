document.addEventListener('DOMContentLoaded', () => {

    // --- Biến toàn cục ---
    let currentChatPartnerId = null;
    let oldestMessageId = null; // ID của tin nhắn cũ nhất đang hiển thị
    let isLoadingMore = false; // Cờ để tránh bấm "Tải thêm" nhiều lần

    // --- Lấy các phần tử HTML (DOM Elements) ---
    const addFriendForm = document.getElementById('add-friend-form');
    const friendCodeInput = document.getElementById('friend-code-input');
    const addFriendStatus = document.getElementById('add-friend-status');

    const pendingList = document.getElementById('pending-list');
    const friendList = document.getElementById('friend-list');
    
    const chatWelcomeScreen = document.getElementById('chat-welcome-screen');
    const chatMainScreen = document.getElementById('chat-main-screen');
    const chatPartnerName = document.getElementById('chat-partner-name');
    const chatMessagesWindow = document.getElementById('chat-messages-window');
    
    const chatForm = document.getElementById('chat-form');
    const chatReceiverIdInput = document.getElementById('chat-receiver-id');
    const chatMessageInput = document.getElementById('chat-message-input');

    const loadMoreBtn = document.getElementById('load-more-btn');
    const loadMoreContainer = document.querySelector('.load-more-container');

    // ==========================================================
    // CHỨC NĂNG 1: THÊM BẠN (ADD FRIEND) - Không đổi
    // ==========================================================
    if (addFriendForm) {
        addFriendForm.addEventListener('submit', async (e) => {
            e.preventDefault(); 
            const friendCode = friendCodeInput.value.trim();
            if (!friendCode) return;
            addFriendStatus.textContent = 'Đang xử lý...';
            addFriendStatus.className = 'status-message';
            const formData = new FormData();
            formData.append('friend_code', friendCode);
            try {
                const response = await fetch('api/add_friend.php', { method: 'POST', body: formData });
                const result = await response.json(); 
                addFriendStatus.textContent = result.message;
                addFriendStatus.className = `status-message ${result.status}`;
                friendCodeInput.value = ''; 
            } catch (error) {
                addFriendStatus.textContent = 'Lỗi kết nối. Vui lòng thử lại.';
                addFriendStatus.className = 'status-message error';
            }
        });
    }

    // ==========================================================
    // CHỨC NĂNG 2: CHẤP NHẬN BẠN (ACCEPT FRIEND) - Không đổi
    // ==========================================================
    if (pendingList) {
        pendingList.addEventListener('click', async (e) => {
            if (e.target.classList.contains('accept-btn')) {
                e.preventDefault();
                const senderId = e.target.dataset.senderId;
                const listItem = e.target.closest('.pending-item');
                try {
                    const formData = new FormData();
                    formData.append('sender_id', senderId);
                    const response = await fetch('api/accept_friend.php', { method: 'POST', body: formData });
                    const result = await response.json();
                    if (result.status === 'success') {
                        listItem.remove();
                        checkEmptyList(pendingList, 'Không có lời mời nào.');
                        addFriendToUI(result.new_friend.user_id, result.new_friend.display_name);
                    } else {
                        alert(result.message);
                    }
                } catch (error) {
                    alert('Lỗi kết nối.');
                }
            }
        });
    }

    // ==========================================================
    // CHỨC NĂNG 3: BẮT ĐẦU CHAT (CLICK VÀO BẠN BÈ) - NÂNG CẤP
    // ==========================================================
    if (friendList) {
        friendList.addEventListener('click', (e) => {
            const friendItem = e.target.closest('.friend-item');
            if (friendItem) {
                const partnerId = friendItem.dataset.partnerId;
                const partnerName = friendItem.dataset.partnerName;
                const notifDot = friendItem.querySelector('.notification-dot');
                if (notifDot) notifDot.remove();
                document.querySelectorAll('.friend-item.active').forEach(item => item.classList.remove('active'));
                friendItem.classList.add('active');
                openChatWindow(partnerId, partnerName);
            }
        });
    }

    async function openChatWindow(partnerId, partnerName) {
        currentChatPartnerId = partnerId;
        oldestMessageId = null; // Reset khi mở chat mới
        isLoadingMore = false;
        loadMoreBtn.style.display = 'none'; // Ẩn nút

        chatWelcomeScreen.style.display = 'none';
        chatMainScreen.style.display = 'flex'; 
        chatPartnerName.textContent = partnerName;
        chatReceiverIdInput.value = partnerId;
        chatMessagesWindow.innerHTML = ''; // Xóa sạch cửa sổ
        chatMessagesWindow.appendChild(loadMoreContainer); // Gắn lại nút tải thêm
        loadMoreContainer.insertAdjacentHTML('afterend', '<p class="system-message">Đang tải tin nhắn...</p>');

        // Tải LỊCH SỬ tin nhắn lần đầu tiên (beforeId = null)
        await loadMessageHistory(partnerId, null, false); // false = không phải đang "tải thêm"
    }

    /**
     * Hàm tải LỊCH SỬ chat
     * @param {string} partnerId - ID bạn chat
     * @param {string|null} beforeId - ID tin nhắn cũ nhất (null nếu là lần đầu)
     * @param {boolean} isLoadMore - Báo cho hàm biết đây là hành động "tải thêm"
     */
    async function loadMessageHistory(partnerId, beforeId, isLoadMore = false) {
        if (!partnerId || isLoadingMore) return;
        isLoadingMore = true;
        if (isLoadMore) loadMoreBtn.textContent = 'Đang tải...';

        try {
            let url = `api/get_messages.php?partner_id=${partnerId}`;
            if (beforeId) {
                url += `&before_id=${beforeId}`;
            }
            
            const response = await fetch(url);
            const messages = await response.json();

            // Xóa "Đang tải..." nếu là lần đầu
            if (!isLoadMore) {
                const systemMsg = chatMessagesWindow.querySelector('.system-message');
                if (systemMsg) systemMsg.remove();
            }

            // Ghi lại vị trí cuộn cũ (chỉ khi đang "tải thêm")
            const oldScrollHeight = chatMessagesWindow.scrollHeight;
            
            // Xử lý tin nhắn
            if (messages.length > 0) {
                // Cập nhật ID tin nhắn cũ nhất
                oldestMessageId = messages[0].message_id; 
                
                // Thêm tin nhắn vào cửa sổ
                messages.forEach(msg => {
                    // isLoadMore = true -> Thêm lên ĐẦU
                    // isLoadMore = false -> Thêm xuống CUỐI
                    prependMessage(msg.message_text, msg.type, isLoadMore);
                });

                // Hiển thị nút Tải thêm nếu còn tin (API trả về đủ 50)
                if (messages.length === 50) {
                    loadMoreBtn.style.display = 'block';
                } else {
                    loadMoreBtn.style.display = 'none'; // Đã hết tin
                }

            } else {
                // Không còn tin nhắn cũ
                loadMoreBtn.style.display = 'none';
                if (isLoadMore) {
                    // Thêm thông báo "Hết tin nhắn"
                    prependMessage('Đã hết tin nhắn cũ', 'system', true);
                }
            }

            // Khôi phục vị trí cuộn
            if (isLoadMore) {
                chatMessagesWindow.scrollTop = chatMessagesWindow.scrollHeight - oldScrollHeight;
            } else {
                scrollToBottom(); // Cuộn xuống đáy (chỉ khi tải lần đầu)
            }

        } catch (error) {
            console.error('Lỗi tải lịch sử tin nhắn:', error);
        }
        isLoadingMore = false;
        loadMoreBtn.textContent = 'Tải thêm tin nhắn cũ';
    }

    // ==========================================================
    // CHỨC NĂNG 5: GỬI TIN NHẮN (SEND MESSAGE) - NÂNG CẤP
    // ==========================================================
    if (chatForm) {
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const messageText = chatMessageInput.value.trim();
            const receiverId = chatReceiverIdInput.value;
            if (!messageText || !receiverId) return;
            
            // Thêm tin nhắn xuống CUỐI (append)
            prependMessage(messageText, 'sent', false); 
            scrollToBottom();
            
            const tempMessageText = messageText; 
            chatMessageInput.value = '';

            const formData = new FormData();
            formData.append('receiver_id', receiverId);
            formData.append('message_text', messageText);
            try {
                const response = await fetch('api/send_message.php', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.status !== 'success') {
                    alert('Lỗi gửi tin nhắn: ' + result.message);
                    chatMessageInput.value = tempMessageText;
                }
            } catch (error) {
                alert('Lỗi kết nối khi gửi tin nhắn.');
                chatMessageInput.value = tempMessageText;
            }
        });
    }

    // ==========================================================
    // CHỨC NĂNG 6: LONG POLLING (MỚI) - NÂNG CẤP
    // ==========================================================
    async function startLongPolling() {
        try {
            const response = await fetch('api/get_updates.php');
            const data = await response.json();
            if (data.type === 'new_friend_request') {
                addPendingRequestToUI(data.sender_id, data.display_name);
            } 
            else if (data.type === 'new_messages') {
                handleNewMessages(data.messages);
            }
            startLongPolling();
        } catch (error) {
            console.error('Lỗi Long Polling:', error);
            setTimeout(startLongPolling, 5000);
        }
    }

    function handleNewMessages(messages) {
        messages.forEach(msg => {
            if (msg.sender_id == currentChatPartnerId) {
                // Đang chat -> Thêm xuống CUỐI (append)
                prependMessage(msg.message_text, 'received', false); 
                scrollToBottom();
            } 
            else {
                const friendItem = friendList.querySelector(`li[data-partner-id='${msg.sender_id}']`);
                if (friendItem && !friendItem.querySelector('.notification-dot')) {
                    const dot = document.createElement('span');
                    dot.className = 'notification-dot';
                    friendItem.appendChild(dot);
                }
            }
        });
    }

    // ==========================================================
    // CHỨC NĂNG 7: NÚT TẢI THÊM (MỚI)
    // ==========================================================
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', () => {
            if (currentChatPartnerId && oldestMessageId) {
                loadMessageHistory(currentChatPartnerId, oldestMessageId, true); // true = đang "tải thêm"
            }
        });
    }


    // --- HÀM HỖ TRỢ (MỚI VÀ CŨ) ---

    /**
     * Thêm 1 bong bóng chat vào cửa sổ
     * @param {string} text - Nội dung tin nhắn
     * @param {'sent' | 'received' | 'system'} type - Loại tin nhắn
     * @param {boolean} prepend - true: Thêm lên ĐẦU, false: Thêm xuống CUỐI
     */
    function prependMessage(text, type, prepend = false) {
        const messageEl = document.createElement('div');
        if(type === 'system') {
            messageEl.className = 'system-message';
        } else {
            messageEl.classList.add('message-bubble', type);
        }
        messageEl.textContent = text;
        
        if (prepend) {
            // Thêm vào sau nút "Tải thêm"
            loadMoreContainer.insertAdjacentElement('afterend', messageEl);
        } else {
            chatMessagesWindow.appendChild(messageEl);
        }
    }
    
    function scrollToBottom() {
        chatMessagesWindow.scrollTop = chatMessagesWindow.scrollHeight;
    }

    function checkEmptyList(listElement, emptyMessage) {
        const items = listElement.querySelectorAll('li');
        if (items.length === 0) {
            listElement.innerHTML = `<li class="empty-list-msg">${emptyMessage}</li>`;
        } else if (listElement.querySelector('.empty-list-msg')) {
             listElement.querySelector('.empty-list-msg').remove();
        }
    }

    function addFriendToUI(id, name) {
        checkEmptyList(friendList, 'Bạn chưa có bạn bè nào.');
        const li = document.createElement('li');
        li.className = 'friend-item';
        li.dataset.partnerId = id;
        li.dataset.partnerName = name;
        li.innerHTML = `<span>${name}</span>`;
        friendList.appendChild(li);
    }

    function addPendingRequestToUI(id, name) {
        checkEmptyList(pendingList, 'Không có lời mời nào.');
        const li = document.createElement('li');
        li.className = 'pending-item';
        li.innerHTML = `
            <span>${name}</span>
            <button class="accept-btn" data-sender-id="${id}">
                Chấp nhận
            </button>
        `;
        pendingList.appendChild(li);
    }

    // --- BẮT ĐẦU CHẠY ---
    // Kiểm tra xem body có class 'auth-page' không
    // (Chúng ta sẽ thêm class này vào <body> của login/register)
    if (!document.body.classList.contains('auth-page')) {
         startLongPolling();
    }

});

