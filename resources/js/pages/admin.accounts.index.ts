import { init as checkAll } from '../features/checkall';
import { bind as bindConfirm } from '../features/confirm';

/**
 * Trang: Admin > Accounts > index
 * - Chuẩn hoá check-all cho bảng
 * - Gắn confirm cho nút xoá (dựa trên [data-confirm])
 */
export default async function init(root: HTMLElement): Promise<void> {
  // Check-all: hỗ trợ cả chuẩn mới (data-*) và fallback theo cấu trúc bảng cũ
  checkAll(root, {
    master: '[data-check-all], table thead input[type="checkbox"]',
    item: '[data-row-check], table tbody input[type="checkbox"]',
    onChange: (ids) => {
      // Bạn có thể bật/tắt nút bulk dựa trên ids.length ở đây nếu muốn
      // Ví dụ: toggle disabled class...
    }
  });

  // Confirm: click link/nút có data-confirm hoặc form có data-confirm
  bindConfirm(root, {
    selector: '[data-confirm]'
  });
}
