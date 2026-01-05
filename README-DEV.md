# 🚀 Development Mode - Quick Start

## Bắt đầu Development Mode

```bash
# Windows (PowerShell)
docker-compose -f docker-compose.dev.yml up -d

# Linux/Mac
./scripts/dev.sh
```

## Dừng Development Mode

```bash
# Windows (PowerShell)
docker-compose -f docker-compose.dev.yml down

# Linux/Mac
./scripts/dev-stop.sh
```

## ✨ Tính năng

- ✅ **Hot Reload Frontend**: Sửa code trong `frontend/` → Tự động reload
- ✅ **Live Backend**: Sửa code trong `backend/` → Restart container để áp dụng
- ✅ **No Rebuild**: Không cần rebuild Docker image khi sửa code

## 📍 URLs

- **Main App**: http://localhost
- **Frontend Dev Server**: http://localhost:3000
- **Backend API**: http://localhost/api
- **phpMyAdmin**: http://localhost:8080

## 🔧 Commands

```bash
# Xem logs
docker-compose -f docker-compose.dev.yml logs -f

# Restart backend (sau khi sửa backend code)
docker-compose -f docker-compose.dev.yml restart backend

# Chạy Laravel commands
docker-compose -f docker-compose.dev.yml exec backend php artisan migrate
```

Xem `DEVELOPMENT.md` để biết thêm chi tiết!

