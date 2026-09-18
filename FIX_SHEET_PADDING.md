# Fix Bug Padding/Margin Sheet "Soạn email mở đầu"

## Vấn đề
Sheet "Soạn email mở đầu" ở trang `/admin/buyers/[id]` bị dính sát mép (không có padding/margin).

**Nguyên nhân:**
- `SheetContent` có `gap-4` nhưng không có `p-6`, và body content không có `px-4`
- `SheetHeader` và `SheetFooter` có `p-4` nhưng body (contextHints + form) là direct child không có padding
- Khi render, body chạm sát mép trái/phải

## Giải pháp

### 1. Fix generic `components/ui/sheet.tsx` (vexim-frontend)
- Thêm prop `overlayClassName` để support backdrop nhẹ (`bg-black/20`) khi cần đọc nội dung phía sau
- Đổi `SheetContent` default từ `gap-4` không padding sang `gap-4 p-6 overflow-y-auto` để mặc định đã có padding, không bao giờ dính mép
- Đổi `SheetHeader` từ `p-4` sang `shrink-0` (không padding cứng, dựa vào parent p-6) để tránh double padding
- Đổi `SheetFooter` từ `p-4` sang `mt-auto pt-2 shrink-0` tương tự
- Cho phép override bằng `p-0 gap-0 overflow-hidden` khi cần layout phức tạp

### 2. Fix cụ thể `requirement-email-composer.tsx` (vexim-bridge)
**Before (buggy):**
```tsx
<SheetContent side="right" className="w-full overflow-y-auto sm:max-w-2xl" overlayClassName="bg-black/20">
  <SheetHeader>...</SheetHeader>
  {contextHints}  // <- dính sát mép, không có px-4
  {body}          // <- dính sát mép
  <SheetFooter>...</SheetFooter>
</SheetContent>
```

**After (fixed):**
```tsx
<SheetContent
  side="right"
  className="w-full sm:max-w-2xl p-0 gap-0 flex flex-col overflow-hidden"
  overlayClassName="bg-black/20"
>
  <SheetHeader className="p-6 pb-4 shrink-0">...</SheetHeader>

  <div className="flex-1 overflow-y-auto px-6 py-4 space-y-4">
    {contextHints}
    {body}
  </div>

  <SheetFooter className="p-6 pt-4 border-t shrink-0 flex-row justify-end gap-2">
    {actions}
  </SheetFooter>
</SheetContent>
```

**Key fixes:**
- `p-0 gap-0 flex flex-col overflow-hidden` ở SheetContent để reset và cho phép inner scroll
- Header: `p-6 pb-4 shrink-0`
- Body wrapper: `flex-1 overflow-y-auto px-6 py-4 space-y-4` - đây là phần chính fix dính sát mép
- Footer: `p-6 pt-4 border-t shrink-0 flex-row justify-end gap-2`

### 3. Kết quả
- Content không còn dính sát mép
- Header/footer cố định, body scroll độc lập
- Backdrop nhẹ giúp vẫn đọc được analysis bên cạnh khi soạn email
- Responsive: sm:max-w-2xl đủ rộng để viết email

## Files đã sửa
- `components/ui/sheet.tsx` - generic fix
- `components/admin/requirement-email-composer.tsx` - fixed example (copy sang vexim-bridge để áp dụng)

## Test
1. Mở `/admin/buyers/[id]` -> tab Phân tích -> click "Soạn email mở đầu"
2. Kiểm tra Sheet bên phải có padding đều 24px (p-6)
3. Context hints và form không dính mép
4. Scroll body khi content dài, header/footer vẫn cố định
