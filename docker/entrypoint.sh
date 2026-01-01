#!/bin/sh

set -e

echo "Waiting for MySQL to be ready..."

# Wait for MySQL to be ready
max_attempts=30
attempt=0

while [ $attempt -lt $max_attempts ]; do
    if php -r "
    try {
        \$pdo = new PDO('mysql:host=mysql;port=3306', 'admin', '123456789');
        \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo 'MySQL is ready!';
        exit(0);
    } catch (Exception \$e) {
        exit(1);
    }
    " 2>/dev/null; then
        break
    fi
    
    attempt=$((attempt + 1))
    echo "MySQL is unavailable - waiting... (attempt $attempt/$max_attempts)"
    sleep 2
done

if [ $attempt -eq $max_attempts ]; then
    echo "MySQL did not become ready in time. Continuing anyway..."
fi

echo "MySQL is up - executing migrations and seeders"

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:your-app-key-here" ]; then
    echo "Generating APP_KEY..."
    NEW_KEY=$(php artisan key:generate --show 2>/dev/null || php -r "echo 'base64:' . base64_encode(random_bytes(32));")
    export APP_KEY="$NEW_KEY"
    echo "APP_KEY=$NEW_KEY" >> /var/www/college-quiz-app/.env
    php artisan config:clear
fi

# Set environment variables for admin seeder
export ADMIN_FIRST_NAME=${ADMIN_FIRST_NAME:-Admin}
export ADMIN_LAST_NAME=${ADMIN_LAST_NAME:-System}
export ADMIN_EMAIL=${ADMIN_EMAIL:-admin@example.com}
export ADMIN_BIRTH_DATE=${ADMIN_BIRTH_DATE:-1990-01-01}
export ADMIN_GENDER=${ADMIN_GENDER:-male}
export ADMIN_ADDRESS=${ADMIN_ADDRESS:-Default Address}
export ADMIN_PASSWORD=${ADMIN_PASSWORD:-admin123}

# Fix permissions for storage
chmod -R 775 /var/www/college-quiz-app/storage
chown -R www-data:www-data /var/www/college-quiz-app/storage
touch /var/www/college-quiz-app/storage/logs/laravel.log
chmod 664 /var/www/college-quiz-app/storage/logs/laravel.log
chown www-data:www-data /var/www/college-quiz-app/storage/logs/laravel.log

# Clear and cache config
php artisan config:clear
php artisan config:cache

# Run migrations
php artisan migrate --force

# Run seeders (ignore errors if data already exists)
php artisan db:seed --force || true

echo "Database setup completed!"
echo "Admin credentials:"
echo "  Email: ${ADMIN_EMAIL}"
echo "  Password: ${ADMIN_PASSWORD}"

# Start PHP-FPM and Nginx
exec sh -c "php-fpm & nginx -g 'daemon off;' & crond -f"

