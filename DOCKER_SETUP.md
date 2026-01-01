# Hướng dẫn chạy dự án với Docker

## Yêu cầu
- Docker
- Docker Compose

## Cấu hình

Dự án đã được cấu hình để chạy với:
- **MySQL 8.0** (thay vì MariaDB)
- **phpMyAdmin** (quản lý database)
- **Redis** (cache)
- **Nginx + PHP-FPM** (web server)

## Thông tin đăng nhập mặc định

### MySQL Database
- **Host**: localhost (hoặc `mysql` trong Docker network)
- **Port**: 3306
- **Database**: college_quiz_app
- **Username**: admin
- **Password**: 123456789
- **Root Password**: rootpassword

### phpMyAdmin
- **URL**: http://localhost:8080
- **Username**: admin
- **Password**: 123456789

### Admin Account (tự động tạo khi chạy seeder)
- **Email**: admin@example.com
- **Password**: admin123

## Cách chạy

### 1. Build và khởi động containers

```bash
docker-compose up -d --build
```

### 2. Kiểm tra logs

```bash
docker-compose logs -f nginx-server
```

### 3. Truy cập ứng dụng

- **Ứng dụng**: http://localhost
- **phpMyAdmin**: http://localhost:8080

## Tùy chỉnh Admin Account

Bạn có thể thay đổi thông tin admin mặc định bằng cách sửa các biến môi trường trong `docker-compose.yml`:

```yaml
environment:
  ADMIN_FIRST_NAME: Admin
  ADMIN_LAST_NAME: System
  ADMIN_EMAIL: admin@example.com
  ADMIN_BIRTH_DATE: 1990-01-01
  ADMIN_GENDER: male
  ADMIN_ADDRESS: Default Address
  ADMIN_PASSWORD: admin123
```

Sau đó rebuild container:

```bash
docker-compose down
docker-compose up -d --build
```

## Database Seeder

Database seeder sẽ tự động chạy khi container khởi động lần đầu. Nó sẽ:
1. Chờ MySQL sẵn sàng
2. Chạy migrations
3. Chạy seeders (bao gồm AdminSeeder với thông tin mặc định)

## Các lệnh hữu ích

### Xem logs
```bash
docker-compose logs -f
```

### Dừng containers
```bash
docker-compose down
```

### Dừng và xóa volumes (xóa database)
```bash
docker-compose down -v
```

### Rebuild lại từ đầu
```bash
docker-compose down -v
docker-compose up -d --build
```

### Chạy artisan commands
```bash
docker-compose exec nginx-server php artisan [command]
```

### Truy cập vào container
```bash
docker-compose exec nginx-server sh
```

## Lưu ý

- Dữ liệu MySQL được lưu trong thư mục `./mysql-data` (tự động tạo)
- Dữ liệu Redis được lưu trong thư mục `./redis-data` (tự động tạo)
- Nếu muốn reset database, xóa thư mục `mysql-data` và khởi động lại

