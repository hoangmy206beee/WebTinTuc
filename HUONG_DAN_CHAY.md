# Web Tin Tức – Hướng dẫn chạy

## ⚠️ QUAN TRỌNG: Không dùng "Go Live" / Live Server
Đây là project **PHP + MySQL**, không phải web tĩnh HTML/CSS/JS.
Extension "Live Server" trong VS Code chỉ phục vụ được file tĩnh,
KHÔNG chạy được code PHP. Nếu bấm "Go Live" bạn sẽ thấy code PHP hiện ra
dạng chữ thô, hoặc lỗi không kết nối được database.

## Cách chạy đúng

### Bước 1: Cài XAMPP
Tải tại: https://www.apachefriends.org/ → cài đặt bình thường.

### Bước 2: Copy project vào htdocs
- Copy toàn bộ thư mục này vào: `C:\xampp\htdocs\webtintuc`
  (đổi tên thư mục, tránh dùng dấu tiếng Việt/khoảng trắng trong tên thư mục)

### Bước 3: Bật Apache + MySQL
Mở XAMPP Control Panel → bấm **Start** ở dòng Apache và MySQL.

### Bước 4: Tạo database
1. Mở trình duyệt, vào `http://localhost/phpmyadmin`
2. Tạo database mới tên: `tinnhanh`
3. Vào tab "Import" → chọn file `tinnhanh.sql` trong project → bấm "Go" / "Thực hiện"

### Bước 5: Truy cập web
Mở trình duyệt: `http://localhost/webtintuc/index.php`

## Các lỗi đã sửa trong bản này
1. **`config/ketnoi.php`**: file gốc bị kết nối database **2 lần** với 2 tên khác nhau
   (`db_tintuc` và `tinnhanh`), gây lỗi/lãng phí. Đã gộp lại còn 1 kết nối, dùng đúng tên `tinnhanh` khớp với file `.sql`.
2. **`includes/functions.php`**: file này được gọi (`require_once`) ở nhiều trang nhưng
   **không hề tồn tại** trong project gốc → gây lỗi Fatal Error ngay khi mở trang.
   Đã tạo lại đầy đủ các hàm: `clean()`, `formatDate()`, `formatViews()`, `tangLuotXem()`.
3. **`index.php`**: bản gốc dùng dữ liệu giả lập viết cứng trong code (không lấy từ
   database), không đồng bộ giao diện với các trang khác. Đã viết lại để lấy dữ liệu
   thật từ bảng `posts`, dùng chung layout/menu với toàn site.
4. **`binhluan.php`**: lỗi bảo mật nghiêm trọng — nối chuỗi SQL trực tiếp từ dữ liệu
   người dùng nhập (SQL Injection). Đã chuyển sang dùng prepared statement.
5. **`baiviet.php`**: đóng kết nối database (`$conn->close()`) trước khi include
   `binhluan.php`, trong khi file này vẫn cần dùng `$conn` → đã sửa lại thứ tự.
6. **`dangky.php` và `dangnhap.php`**: toàn bộ nội dung file bị dán lặp lại 2 lần
   (lỗi copy-paste), gây lỗi khai báo trùng. Đã dọn lại còn đúng 1 bản, đồng thời
   chuyển các câu lệnh SQL thô sang prepared statement để chống SQL Injection.
7. Tạo thêm thư mục `uploads/posts/` còn thiếu (các trang bài viết tham chiếu tới
   thư mục này để hiển thị ảnh, nhưng project gốc chỉ có thư mục `upload/`).

## Lưu ý
- Phần `admin/` (quản lý bài viết, danh mục, bình luận, tài khoản) project gốc khá
  lớn (4 file, hơn 1200 dòng) — mình chưa rà soát kỹ phần này, nếu bạn cần dùng tới
  trang quản trị thì báo lại để mình kiểm tra tiếp.
- Để có dữ liệu hiển thị trên trang chủ, bạn cần thêm bài viết (status = 'published')
  vào bảng `posts` qua phpMyAdmin hoặc qua trang admin.
