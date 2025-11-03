# SimpleChat - Ứng dụng chat Real-time bằng PHP & MySQL

Một dự án ứng dụng chat 1-1 đơn giản, được xây dựng từ đầu bằng PHP, MySQL và JavaScript, sử dụng kỹ thuật Long Polling để cập nhật dữ liệu (tin nhắn, lời mời kết bạn) theo thời gian thực mà không cần tải lại trang.

## 🚀 Tính năng nổi bật

* **Xác thực người dùng:** Đăng ký và Đăng nhập an toàn (sử dụng `password_hash`).
* **Hệ thống Bạn bè:**
    * Mỗi người dùng có một "Mã kết bạn" (Friend Code) duy nhất.
    * Gửi, Chấp nhận lời mời kết bạn.
* **Chat Real-time:**
    * Nhắn tin 1-1 với bạn bè.
    * Sử dụng **Long Polling** (JavaScript) để nhận tin nhắn mới và lời mời kết bạn mới ngay lập tức.
    * Hiển thị thông báo (chấm xanh) khi có tin nhắn mới từ người bạn mà bạn không đang chat.
* **Tối ưu hóa hiệu suất:**
    * Chỉ tải 50 tin nhắn mới nhất khi mở cửa sổ chat.
    * Tính năng "Tải thêm tin nhắn cũ" khi cuộn lên đầu.
    * Sử dụng **Database Indexing** để tăng tốc độ truy vấn CSDL.

## 🛠️ Công nghệ sử dụng

* **Backend:** PHP 8+
* **Database:** MySQL (Sử dụng PDO)
* **Web Server:** Apache (Khuyến nghị sử dụng **XAMPP** để có môi trường đầy đủ)
* **Frontend:**
    * JavaScript (ES6+, `async/await`)
    * AJAX (`fetch` API)
    * HTML5
    * CSS3

## 📦 Cài đặt & Khởi chạy

### Yêu cầu
* **XAMPP** (hoặc MAMP, WAMP) đã được cài đặt.

### Hướng dẫn

1.  **Clone Repository:**
    Mở Terminal hoặc Git Bash, `cd` vào thư mục `htdocs` của XAMPP (thường là `C:\xampp\htdocs`).
    ```bash
    git clone [https://github.com/codespark-vietnam/SimpleChat.git](https://github.com/codespark-vietnam/SimpleChat.git)
    ```
    *(Nếu bạn tải file ZIP, hãy giải nén và đặt tên thư mục là `SimpleChat`)*

2.  **Khởi động Server:**
    Mở XAMPP Control Panel và khởi động (Start) **Apache** và **MySQL**.

3.  **Cài đặt Cơ sở dữ liệu (Database):**
    Đây là bước quan trọng nhất.
    * Mở trình duyệt của bạn và truy cập:
        ```
        http://localhost/SimpleChat/install.php
        ```
    * Trang này sẽ tự động tạo CSDL tên là `simplechat` và 3 bảng: `users`, `friendships`, `messages`.

4.  **(QUAN TRỌNG) Tối ưu hóa DB:**
    * Sau khi cài đặt xong, hãy truy cập:
        ```
        http://localhost/SimpleChat/optimize_db.php
        ```
    * Trang này sẽ thêm các "Chỉ mục" (Index) vào CSDL để tăng tốc độ truy vấn.

5.  **(CỰC KỲ QUAN TRỌNG) Xóa file cài đặt:**
    Vì lý do bảo mật, sau khi chạy xong 2 file trên, bạn **PHẢI XÓA** 2 file sau khỏi thư mục dự án:
    * `install.php`
    * `optimize_db.php`

6.  **Truy cập ứng dụng:**
    Mở trình duyệt và truy cập trang đăng ký:
    ```
    http://localhost/SimpleChat/register.php
    ```

## 🎮 Cách sử dụng

1.  Mở 2 trình duyệt khác nhau (ví dụ: Chrome và Firefox, hoặc Chrome và Chế độ ẩn danh).
2.  **Tài khoản 1 (Chrome):** Truy cập `register.php` và tạo tài khoản (ví dụ: `user1`).
3.  **Tài khoản 2 (Firefox):** Truy cập `register.php` và tạo tài khoản (ví dụ: `user2`).
4.  **Lấy Mã kết bạn:**
    * `user1` đăng nhập, bạn sẽ thấy "Mã kết bạn" của mình (ví dụ: `A1B2C3D4`).
    * `user2` đăng nhập, bạn sẽ thấy "Mã kết bạn" của `user2` (ví dụ: `E5F6G7H8`).
5.  **Gửi lời mời:**
    * `user1` nhập mã `E5F6G7H8` của `user2` vào ô "Thêm bạn" và bấm Thêm.
6.  **Chấp nhận (Real-time):**
    * Ngay lập tức, bên trình duyệt của `user2` (Firefox), lời mời từ `user1` sẽ tự động xuất hiện trong danh sách "Lời mời đang chờ" mà **không cần F5**.
    * `user2` bấm "Chấp nhận".
7.  **Chat:**
    * Cả hai người dùng sẽ thấy tên nhau trong danh sách "Bạn bè".
    * Bấm vào tên bạn bè để bắt đầu chat. Tin nhắn sẽ được gửi và nhận ngay lập tức.

---

## ✍️ Tác giả

* **Trần Ngọc Minh Thông**
* **Email:** thongtnmfct31178@gmail.com

Bản quyền thuộc về **CodeSpark Việt Nam**
