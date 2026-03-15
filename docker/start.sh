#!/bin/bash

echo "=== DGA School - Starting up ==="

# Wait for MySQL to be ready (Railway DB can take a few seconds)
echo "Waiting for database..."
until php -r "
    \$conn = @mysqli_connect(
        getenv('DB_HOST'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD'),
        getenv('DB_DATABASE'),
        getenv('DB_PORT') ?: 3306
    );
    if (\$conn) { echo 'ok'; exit(0); }
    exit(1);
" 2>/dev/null | grep -q ok; do
    echo "Database not ready yet, retrying in 3s..."
    sleep 3
done
echo "Database is ready ✓"

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force
fi

# Cache config for performance
echo "Caching config..."
php artisan config:cache

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Run seeders (only if DB is empty - first deploy)
ROLE_COUNT=$(php -r "
    \$conn = mysqli_connect(
        getenv('DB_HOST'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD'),
        getenv('DB_DATABASE'),
        getenv('DB_PORT') ?: 3306
    );
    \$result = mysqli_query(\$conn, 'SELECT COUNT(*) as cnt FROM roles');
    \$row = mysqli_fetch_assoc(\$result);
    echo \$row['cnt'];
" 2>/dev/null)

if [ "$ROLE_COUNT" = "0" ] || [ -z "$ROLE_COUNT" ]; then
    echo "Fresh database detected - running seeders..."
    php artisan db:seed --force
    echo "Seeding complete ✓"
else
    echo "Database already seeded, skipping ✓"
fi

# Set storage permissions
echo "Setting permissions..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Create supervisor log directory
mkdir -p /var/log/supervisor

echo "=== Starting PHP-FPM + Nginx via supervisor ==="
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
