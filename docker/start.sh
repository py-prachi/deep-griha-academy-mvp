#!/bin/bash

echo "=== Starting container, PORT=${PORT:-8080} ==="

# ── 1. Start PHP-FPM in background ──
php-fpm &
sleep 2

# ── 2. Configure nginx port ──
sed -i "s/listen 80/listen ${PORT:-8080}/g" /etc/nginx/sites-available/default
nginx -t && echo "nginx config OK"

# ── 3. Start nginx NOW — /health returns 200 immediately (nginx-level, no php-fpm needed) ──
nginx -g 'daemon off;' &
echo "nginx started on port ${PORT:-8080}"

# ── 4. Wait for database ──
echo "Waiting for database..."
until php -r "
    try {
        \$dsn = 'mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: 3306) . ';dbname=' . getenv('DB_DATABASE');
        \$pdo = new PDO(\$dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'), [PDO::ATTR_TIMEOUT => 5]);
        echo 'ok';
        exit(0);
    } catch (Exception \$e) {
        exit(1);
    }
" 2>/dev/null | grep -q ok; do
    echo "DB not ready, retrying in 3s..."
    sleep 3
done
echo "DB ready"

# ── 5. Bootstrap Laravel ──
cd /var/www
php artisan package:discover --ansi || true
php artisan config:clear
php artisan config:cache
php artisan migrate --force

# ── 6. Seed if empty ──
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
    echo "Empty database — seeding..."
    php artisan db:seed --force
    echo "Seeding complete"
else
    echo "Database already seeded, skipping"
fi

# ── 7. Fix storage permissions ──
mkdir -p /var/www/storage/logs
touch /var/www/storage/logs/laravel.log
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo "=== App fully ready ==="

# Keep container alive (nginx + php-fpm are background processes)
wait
