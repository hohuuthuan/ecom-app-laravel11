# Ghi chú đề xuất chức năng bổ sung

Dự án: Website bán sách trực tuyến (Laravel 11)  
Mục đích file: Tổng hợp các chức năng có thể làm thêm ở giai đoạn hoàn thiện dự án.

---

## 1. Nhóm trải nghiệm mua sắm cho khách

### 1.1. Tìm kiếm & lọc nâng cao
- Bộ lọc sản phẩm:
  - Danh mục
  - Tác giả
  - Nhà xuất bản
  - Khoảng giá
  - Đánh giá (rating)
  - Tình trạng còn hàng
- Sắp xếp:
  - Bán chạy
  - Mới nhất
  - Giá tăng dần / giảm dần

### 1.2. Gợi ý sách liên quan / đề xuất
- Ở trang chi tiết sản phẩm:
  - Sách cùng tác giả
  - Sách cùng thể loại
- Ở trang chủ / giỏ hàng:
  - Gợi ý “Có thể bạn cũng thích” dựa trên sách bán chạy / sản phẩm liên quan.

### 1.3. Sản phẩm đã xem gần đây (Recently viewed)
- Lưu danh sách ID sản phẩm trong session/cookie.
- Hiển thị block “Sách bạn vừa xem”:
  - Ở trang chủ
  - Hoặc ở trang chi tiết sản phẩm.

### 1.4. Đánh giá & bình luận nâng cao
- User:
  - Viết review chi tiết (rating + nội dung + hình ảnh).
  - Có thể upload 1–3 ảnh minh họa.
- Tương tác:
  - Cho phép đánh dấu review hữu ích / không hữu ích.
- Admin:
  - Trang quản lý review (duyệt / ẩn / xóa).
  - Lọc theo sản phẩm / user / trạng thái.

---

## 2. Nhóm sau bán & dịch vụ khách hàng

### 2.1. Trang chi tiết đơn hàng riêng cho user
- Tách riêng khỏi màn hình admin.
- Cho phép user xem:
  - Thông tin giao hàng, thanh toán.
  - Danh sách sản phẩm & tổng tiền.
  - Timeline trạng thái đơn hàng (Đặt hàng → Xử lý → Đang giao → Hoàn thành / Hủy).

### 2.2. Tải hóa đơn / xuất PDF đơn hàng
- User:
  - Tải file PDF hóa đơn từ màn hình chi tiết đơn hàng.
- Admin:
  - In hóa đơn để đóng gói & lưu trữ.

### 2.3. Đặt lại đơn (Reorder)
- Nút “Mua lại đơn này” ở chi tiết đơn hàng.
- Logic:
  - Xóa giỏ hiện tại (hoặc merge hợp lý).
  - Thêm lại toàn bộ sản phẩm trong đơn cũ vào giỏ hàng (nếu vẫn còn bán).

---

## 3. Nhóm Admin & báo cáo

### 3.1. Dashboard doanh thu & thống kê nâng cao
- Chỉ số tổng quan:
  - Doanh thu theo ngày / tuần / tháng.
  - Số đơn theo trạng thái.
  - Tỷ lệ phương thức thanh toán (COD / MoMo / VNPay).
- Top list:
  - Sách bán chạy.
  - Danh mục / nhà xuất bản bán tốt.
- Biểu đồ:
  - Doanh thu theo thời gian.
  - Số đơn mới theo thời gian.

### 3.2. Xuất Excel
- Export Excel cho:
  - Danh sách đơn hàng.
  - Tồn kho.
  - Phiếu nhập.
- Dùng package Excel (ví dụ: maatwebsite/excel).

### 3.3. Nhật ký hoạt động (Activity log)
- Lưu các hành động quan trọng:
  - Tạo / sửa / xóa sản phẩm.
  - Cập nhật trạng thái đơn hàng.
  - Thao tác nhập / điều chỉnh kho.
- Trang riêng hiển thị log để dễ truy vết.

---

## 4. Nhóm Kho & vận hành

### 4.1. Cảnh báo tồn kho thấp
- Cấu hình “mức tồn tối thiểu” cho mỗi sản phẩm.
- Trang cảnh báo:
  - Danh sách sản phẩm sắp hết hàng / hết hàng.
- Tùy chọn gửi thông báo nội bộ / email khi tồn kho dưới ngưỡng.

### 4.2. Lịch sử tồn kho & phân tích xoay vòng
- Sử dụng bảng lịch sử nhập/xuất (stock movements):
  - Xem chi tiết nhập – xuất theo từng sản phẩm.
  - Báo cáo sản phẩm quay vòng nhanh / chậm.
  - Phát hiện sản phẩm tồn kho lâu (đề xuất giảm giá / xả hàng).

### 4.3. Phiếu kiểm kê kho
- Tạo phiếu kiểm kê:
  - Chọn kho, nhập số lượng thực tế.
  - So sánh với số lượng trên hệ thống.
- Sau khi duyệt:
  - Tự sinh các dòng điều chỉnh tồn kho.

---

## 5. Nhóm Bảo mật & hệ thống

### 5.1. Ghi log đăng nhập & bảo vệ tài khoản
- Bảng lưu lịch sử đăng nhập:
  - user_id, IP, user_agent, trạng thái (thành công / thất bại).
- User:
  - Xem lịch sử đăng nhập của mình (giúp phát hiện bất thường).
- Admin:
  - Thống kê đăng nhập, phát hiện IP lạ.

### 5.2. Hoàn thiện logic giới hạn mã giảm giá
- Giới hạn:
  - Tổng số lần sử dụng của 1 mã.
  - Số lần sử dụng tối đa trên mỗi user.
- Gắn order_id vào bảng usage của mã giảm giá sau khi đặt hàng thành công.
- Thống kê:
  - Doanh thu phát sinh từ từng mã giảm giá.
  - Mã nào hiệu quả / ít hiệu quả.

---

## 6. Gợi ý thứ tự ưu tiên triển khai

1. **Hoàn thiện mã giảm giá & chi tiết đơn hàng cho user**  
2. **Dashboard doanh thu & cảnh báo tồn kho**  
3. **Gợi ý sản phẩm + recently viewed**  
4. **Nhật ký hoạt động & log đăng nhập**  

File này có thể đặt tại: `docs/feature-notes.md` hoặc `notes/features-phase-final.md` trong project.
