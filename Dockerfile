FROM php:8.2-fpm

# 1. Cài các thư viện hệ thống cần thiết
RUN apt-get update && apt-get install -y \
    git curl zip unzip gnupg2 \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    libpq-dev \
    ca-certificates lsb-release \
    && docker-php-ext-install pdo_pgsql pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Cài Node.js & npm (v18)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# 3. Cài Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 4. Thiết lập thư mục làm việc
WORKDIR /var/www

# 5. Copy project vào container (trừ node_modules nếu có)
COPY . .

# 6. Cài thư viện PHP
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# 7. Cài frontend & build Vite
RUN npm install && npm run build

# 8. Tạo thư mục cache và phân quyền
RUN mkdir -p storage/framework/{cache/data,sessions,views} bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# 9. Mở port cho php artisan serve và vite
# EXPOSE 8000 5173

# 10. Dùng CMD để boot app, hoặc dùng supervisord nếu cần chạy nhiều process
# CMD php artisan serve --host=0.0.0.0 --port=8000
