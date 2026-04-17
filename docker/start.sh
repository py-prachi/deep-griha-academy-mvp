#!/bin/bash
set -x

echo "=== Starting container, PORT=${PORT:-8080} ==="

# ── Configure nginx port ──
sed -i "s/listen 80/listen ${PORT:-8080}/g" /etc/nginx/sites-available/default
nginx -t

# ── Run DB wait + Laravel bootstrap in background ──
(
    echo "[setup] Starting PHP-FPM..."
    php-fpm -D
    sleep 2

    echo "[setup] Waiting for database..."
    until php -r "
        \$conn = @mysqli_connect(
            getenv('DB_HOST'), getenv('DB_USERNAME'),
            getenv('DB_PASSWORD'), getenv('DB_DATABASE'),
            (int)(getenv('DB_PORT') ?: 3306)
        );
        if (\$conn) { echo 'ok'; exit(0); }
        exit(1);
    " 2>/dev/null | grep -q ok; do
        echo "[setup] DB not ready, retrying in 3s..."
        sleep 3
    done
    echo "[setup] DB ready"

    cd /var/www
    php artisan package:discover --ansi || true
    php artisan config:clear
    php artisan config:cache
    php artisan migrate --force

    ROLE_COUNT=$(php -r "
        \$conn = mysqli_connect(
            getenv('DB_HOST'), getenv('DB_USERNAME'),
            getenv('DB_PASSWORD'), getenv('DB_DATABASE'),
            (int)(getenv('DB_PORT') ?: 3306)
        );
        \$result = mysqli_query(\$conn, 'SELECT COUNT(*) as cnt FROM roles');
        \$row = mysqli_fetch_assoc(\$result);
        echo \$row['cnt'];
    " 2>/dev/null)

    if [ "$ROLE_COUNT" = "0" ] || [ -z "$ROLE_COUNT" ]; then
        echo "[setup] Empty DB — seeding..."
        php artisan db:seed --force
    fi

    mkdir -p /var/www/storage/logs
    touch /var/www/storage/logs/laravel.log
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

    echo "[setup] Done — app fully ready"
) &

# ── Start nginx in foreground — healthcheck passes as soon as this is up ──
echo "Starting nginx on port ${PORT:-8080}..."
exec nginx -g 'daemon off;'
