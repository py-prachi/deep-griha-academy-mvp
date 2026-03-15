#!/bin/bash
set -e

echo "=== Starting container, PORT=${PORT} ==="

# Fix PHP-FPM to use TCP
sed -i 's|listen = /run/php/php7.4-fpm.sock|listen = 127.0.0.1:9000|g' /etc/php/7.4/fpm/pool.d/www.conf

# Start PHP-FPM
/usr/sbin/php-fpm7.4 -D
sleep 1

echo "PHP-FPM check:"
ss -tlnp | grep 9000 || echo "ERROR: PHP-FPM not listening on 9000"

# Set port in nginx config BEFORE starting nginx
sed -i "s/listen 80/listen ${PORT:-8080}/g" /etc/nginx/sites-available/default

echo "Nginx config port check:"
grep "listen" /etc/nginx/sites-available/default

# Test nginx config
nginx -t

# Wait for DB
echo "Waiting for database..."
until php -r "
    \$conn = @mysqli_connect(
        getenv('DB_HOST'), getenv('DB_USERNAME'),
        getenv('DB_PASSWORD'), getenv('DB_DATABASE'),
        getenv('DB_PORT') ?: 3306
    );
    if (\$conn) { echo 'ok'; exit(0); }
    exit(1);
" 2>/dev/null | grep -q ok; do
    echo "DB not ready, retrying in 3s..."
    sleep 3
done
echo "DB ready"

cd /var/www
php artisan config:clear
php artisan config:cache
php artisan migrate --force

chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo "Starting nginx on port ${PORT:-8080}..."
exec nginx -g 'daemon off;'