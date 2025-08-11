# Flight Booking System - Node.js Version

Hệ thống đặt vé máy bay được chuyển đổi từ PHP sang Node.js với Express framework và cấu trúc MVC chuyên nghiệp.

## Tính năng

- **Người dùng:**
  - Đăng ký/Đăng nhập
  - Tìm kiếm chuyến bay
  - Đặt vé và chọn ghế
  - Xem lịch sử đặt vé
  - Chatbot hỗ trợ

- **Admin:**
  - Dashboard thống kê
  - Quản lý chuyến bay (thêm/xóa)
  - Xem danh sách vé đã đặt
  - Quản lý người dùng

## Cài đặt

### 1. Cài đặt dependencies

```bash
npm install
```

### 2. Cấu hình database

Mở file `app.js` và cập nhật thông tin database:

```javascript
const dbConfig = {
    host: 'your-rds-endpoint',
    user: 'admin',
    password: 'your-password', // Thêm password RDS
    database: 'vemaybay'
};
```

### 3. Chạy ứng dụng

**Development mode:**
```bash
npm run dev
```

**Production mode:**
```bash
npm start
```

**Test mode (old version):**
```bash
npm test
```

Ứng dụng sẽ chạy tại: http://localhost:3000

## Cấu trúc thư mục

```
├── src/                    # Source code chính
│   ├── app.js             # Entry point
│   ├── config/            # Cấu hình
│   │   └── database.js    # Database config
│   ├── controllers/       # Controllers (MVC)
│   │   ├── authController.js
│   │   ├── flightController.js
│   │   └── adminController.js
│   ├── middleware/        # Middleware
│   │   └── auth.js        # Authentication
│   ├── routes/           # Routes
│   │   └── index.js      # Main routes
│   └── utils/            # Utilities
├── views/                # EJS templates
│   ├── index.ejs         # Trang chủ
│   ├── login.ejs         # Đăng nhập
│   ├── register.ejs      # Đăng ký
│   ├── showticket.ejs    # Kết quả tìm kiếm
│   ├── chitiet.ejs       # Chi tiết chuyến bay
│   ├── lichsuve.ejs      # Lịch sử đặt vé
│   ├── 404.ejs           # Error page
│   ├── admin/            # Admin templates
│   └── partials/         # Shared components
├── public/               # Static files
│   ├── style.css
│   ├── script.js
│   └── image/
├── backup/               # Backup files
│   └── php-old/          # Old PHP files
├── aws-lambda/           # AWS Lambda functions
├── package.json          # Dependencies
├── .env.example          # Environment variables
└── README.md
```

## API Endpoints

### Public Routes
- `GET /` - Trang chủ (yêu cầu đăng nhập)
- `GET /login` - Trang đăng nhập
- `POST /login` - Xử lý đăng nhập
- `GET /register` - Trang đăng ký
- `POST /register` - Xử lý đăng ký
- `GET /logout` - Đăng xuất

### User Routes
- `GET /showticket` - Tìm kiếm chuyến bay
- `GET /chitiet/:id` - Chi tiết chuyến bay
- `POST /dat-ve` - Đặt vé
- `GET /lichsuve` - Lịch sử đặt vé

### Admin Routes
- `GET /admin` - Dashboard admin
- `GET /admin/sanphamadmin` - Quản lý chuyến bay
- `POST /admin/add-flight` - Thêm chuyến bay
- `DELETE /admin/delete-flight/:id` - Xóa chuyến bay

## Database Schema

### Bảng `users`
- `id` - Primary key
- `username` - Tên đăng nhập (unique)
- `password` - Mật khẩu đã hash
- `role` - Vai trò (user/admin)
- `created_at` - Thời gian tạo

### Bảng `chuyen_bay`
- `id` - Primary key
- `ma_cb` - Mã chuyến bay
- `diem_di` - Điểm đi
- `diem_den` - Điểm đến
- `ngay_di` - Ngày bay
- `gio_di` - Giờ cất cánh
- `gio_den` - Giờ hạ cánh
- `hang_hang_khong` - Hãng hàng không
- `loai_may_bay` - Loại máy bay
- `gia` - Giá vé
- `tong_ghe` - Tổng số ghế

### Bảng `ve`
- `id` - Primary key
- `ten_nguoi_dat` - Tên người đặt
- `chuyen_bay_id` - Foreign key đến bảng chuyến bay
- `ghe_so` - Số ghế
- `trang_thai_thanh_toan` - Trạng thái thanh toán
- `danh_xung` - Danh xưng
- `sdt` - Số điện thoại
- `email` - Email
- `cccd` - CCCD/CMND
- `created_at` - Thời gian đặt vé

## Tài khoản mặc định

Sau khi chạy lần đầu, bạn có thể tạo tài khoản admin bằng cách:

1. Đăng ký tài khoản bình thường
2. Vào database và thay đổi `role` từ 'user' thành 'admin'

## Lưu ý

- Đảm bảo MySQL/RDS đang chạy và có thể kết nối
- Cập nhật thông tin database trong `app.js`
- Các bảng sẽ được tạo tự động khi chạy lần đầu
- Static files được serve từ thư mục `public/`
- Session được lưu trong memory (production nên dùng Redis)

## Công nghệ sử dụng

- **Backend:** Node.js, Express.js
- **Database:** MySQL
- **Template Engine:** EJS
- **Authentication:** bcryptjs, express-session
- **Frontend:** HTML, CSS, JavaScript (Vanilla)
- **Icons:** Font Awesome

## Tác giả

Web Team - Flight Booking System