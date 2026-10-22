# Sử dụng PHP 8.2 với Apache
FROM php:8.2.35-apache

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Cài đặt các dependencies hệ thống
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libwebp-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libicu-dev \
    ffmpeg \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Cài đặt Node.js và Yarn để build front-end (Laravel Mix)
RUN apt-get update && apt-get install -y \
    ca-certificates \
    gnupg \
    bash \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g yarn \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Bật mod_rewrite cho Apache
RUN a2enmod rewrite

# Cấu hình Apache - Thay đổi DocumentRoot sang thư mục public
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
    && sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|<Directory /var/www/html>|<Directory /var/www/html/public>|g' /etc/apache2/sites-available/000-default.conf

# Cấu hình PHP
RUN echo "upload_max_filesize = 64M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini

# Cấu hình bảo mật PHP: không hiện lỗi ra trình duyệt, ghi lỗi ra log container, ẩn phiên bản PHP
RUN { \
    echo "expose_php = Off"; \
    echo "display_errors = Off"; \
    echo "display_startup_errors = Off"; \
    echo "log_errors = On"; \
    echo "error_log = /dev/stderr"; \
    echo "session.cookie_secure = 1"; \
    echo "session.cookie_httponly = 1"; \
    echo "session.use_strict_mode = 1"; \
    } > /usr/local/etc/php/conf.d/zz-security.ini

# Ẩn phiên bản Apache trong header Server và trang lỗi
RUN { echo "ServerTokens Prod"; echo "ServerSignature Off"; } > /etc/apache2/conf-available/zz-security.conf \
    && a2enconf zz-security

# Cấu hình OPcache cho production
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=2" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/opcache.ini

# Copy source code vào container
COPY . /var/www/html

# Cài đặt dependencies Composer
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

# Build front-end assets bằng Laravel Mix
RUN yarn install --non-interactive --frozen-lockfile && yarn run prod

# Copy file entrypoint vào container
COPY entrypoint.sh /usr/local/bin/
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

# Thiết lập Entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Expose port 80
EXPOSE 80

# Khởi động Apache
CMD ["apache2-foreground"]
