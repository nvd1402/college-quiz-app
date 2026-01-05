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

echo "MySQL is up - setting up Laravel..."

# Fix permissions for storage
chmod -R 775 /var/www/college-quiz-app/storage
chown -R www-data:www-data /var/www/college-quiz-app/storage
touch /var/www/college-quiz-app/storage/logs/laravel.log
chmod 664 /var/www/college-quiz-app/storage/logs/laravel.log
chown www-data:www-data /var/www/college-quiz-app/storage/logs/laravel.log

# Composer dependencies should already be installed in the image
# If vendor doesn't exist, it means we're using a fresh mount
# In that case, user should run: docker-compose exec backend composer install

# Clear and cache config
php artisan config:clear
php artisan config:cache

# Run migrations
php artisan migrate --force

echo "Backend ready! Starting PHP-FPM..."

# Start PHP-FPM only (nginx runs in separate container)
exec php-fpm

