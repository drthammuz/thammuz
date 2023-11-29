#!/bin/bash

# Killing Vite and Laravel processes
echo "Killing Vite and Laravel processes..."
pkill -f vite
pkill -f 'php artisan serve'
pkill -f 'laravel-echo-server'
pkill -f 'redis-server'

# Navigating to the Laravel project directory
cd /var/paravel/my-ai-tools

# Clearing Laravel cache
echo "Clearing Laravel cache..."
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear

echo "Running Composer dump-autoload..."
composer dump-autoload

# Restarting Apache
echo "Restarting Apache server..."
sudo systemctl restart apache2

# Starting Laravel and Vite
echo "Starting Laravel and Vite in the background..."
nohup php artisan serve --host 0.0.0.0 --port 8000 > /dev/null 2>&1 &
nohup npm run dev > /dev/null 2>&1 &

# Starting Redis
echo "Starting Redis..."
sudo systemctl start redis

# Starting Laravel Echo Server
echo "Starting Laravel Echo Server..."
nohup laravel-echo-server start > /dev/null 2>&1 &

echo "Setup complete."
