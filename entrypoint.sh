#!/usr/bin/env bash
set -e

echo "Running application setup..."

# Tự động tạo các thư mục Laravel cần thiết nếu thiếu
mkdir -p /var/www/html/storage/app
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/public/upload

# Gán quyền sở hữu và quyền ghi
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public/upload
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public/upload

# Các lệnh Laravel
composer dump-autoload
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Thực hiện lệnh gốc của Docker (Khởi động Apache)
exec "$@"