# Hướng dẫn Development Mode

## 🚀 Quick Start

### Bắt đầu Development Mode:
```bash
# Cách 1: Dùng script (khuyến nghị)
./scripts/dev.sh

# Cách 2: Dùng docker-compose trực tiếp
docker-compose -f docker-compose.dev.yml up -d
```

### Dừng Development Mode:
```bash
# Cách 1: Dùng script
./scripts/dev-stop.sh

# Cách 2: Dùng docker-compose trực tiếp
docker-compose -f docker-compose.dev.yml down
```

## 📋 Services

Sau khi start, các services sẽ chạy tại:

- **Frontend (Vite Dev Server)**: http://localhost:3000
- **Backend API**: http://localhost/api
- **Nginx Proxy (Main Entry)**: http://localhost
- **phpMyAdmin**: http://localhost:8080

## ✨ Tính năng

### Hot Reload
- **Frontend**: Tự động reload khi sửa code trong `frontend/`
- **Backend**: Tự động reload khi sửa code trong `backend/` (cần restart container)

### Volume Mounts
- `./frontend` → `/app` trong frontend container
- `./backend` → `/var/www/college-quiz-app` trong backend container
- Code changes được sync ngay lập tức

## 🔧 Development Workflow

### 1. Sửa Frontend Code
```bash
# Sửa code trong frontend/
# Vite sẽ tự động reload
# Không cần rebuild!
```

### 2. Sửa Backend Code
```bash
# Sửa code trong backend/
# Restart backend container để áp dụng thay đổi:
docker-compose -f docker-compose.dev.yml restart backend
```

### 3. Xem Logs
```bash
# Tất cả logs
docker-compose -f docker-compose.dev.yml logs -f

# Chỉ frontend
docker-compose -f docker-compose.dev.yml logs -f frontend

# Chỉ backend
docker-compose -f docker-compose.dev.yml logs -f backend
```

### 4. Chạy Commands trong Container
```bash
# Backend (Laravel)
docker-compose -f docker-compose.dev.yml exec backend php artisan migrate
docker-compose -f docker-compose.dev.yml exec backend php artisan tinker

# Frontend (Node)
docker-compose -f docker-compose.dev.yml exec frontend npm install
docker-compose -f docker-compose.dev.yml exec frontend npm run build
```

## 🆚 So sánh Production vs Development

| Feature | Production | Development |
|---------|-----------|-------------|
| Frontend Build | Pre-built (dist/) | Vite Dev Server (hot reload) |
| Backend | Optimized | Development mode |
| Code Changes | Cần rebuild | Tự động reload |
| Build Time | ~2-3 phút | Instant |
| Hot Reload | ❌ | ✅ |

## 🐛 Troubleshooting

### Frontend không reload
```bash
# Kiểm tra Vite dev server
docker-compose -f docker-compose.dev.yml logs frontend

# Restart frontend container
docker-compose -f docker-compose.dev.yml restart frontend
```

### Backend không nhận thay đổi
```bash
# Restart backend container
docker-compose -f docker-compose.dev.yml restart backend

# Clear Laravel cache
docker-compose -f docker-compose.dev.yml exec backend php artisan cache:clear
docker-compose -f docker-compose.dev.yml exec backend php artisan config:clear
```

### Port đã được sử dụng
```bash
# Kiểm tra port nào đang dùng
netstat -ano | findstr :80
netstat -ano | findstr :3000

# Dừng service đang dùng port hoặc đổi port trong docker-compose.dev.yml
```

### Database connection error
```bash
# Kiểm tra MySQL container
docker-compose -f docker-compose.dev.yml ps mysql

# Xem MySQL logs
docker-compose -f docker-compose.dev.yml logs mysql
```

## 📝 Notes

- **Development mode** dùng `APP_ENV=local` (cho phép các lệnh như `migrate:fresh`)
- **Production mode** dùng `APP_ENV=production` (chặn các lệnh nguy hiểm)
- Frontend code được mount trực tiếp, không cần rebuild
- Backend code được mount trực tiếp, nhưng cần restart để áp dụng một số thay đổi

## 🔄 Chuyển đổi giữa Production và Development

### Từ Production → Development:
```bash
docker-compose down
docker-compose -f docker-compose.dev.yml up -d
```

### Từ Development → Production:
```bash
docker-compose -f docker-compose.dev.yml down
docker-compose up -d
```
