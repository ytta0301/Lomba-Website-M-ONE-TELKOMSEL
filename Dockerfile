# Gunakan image resmi PHP 8.2 dengan Apache
FROM php:8.2-apache

# Instal dependensi sistem dan ekstensi PHP yang dibutuhkan (mysqli, pdo_mysql)
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) mysqli pdo pdo_mysql gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Aktifkan modul Apache rewrite
RUN a2enmod rewrite

# Konfigurasi PHP (alokasi memori & limit upload untuk avatar)
RUN { \
    echo 'upload_max_filesize = 20M'; \
    echo 'post_max_size = 25M'; \
    echo 'memory_limit = 128M'; \
    echo 'max_execution_time = 60'; \
    echo 'display_errors = Off'; \
    echo 'log_errors = On'; \
} > /usr/local/etc/php/conf.d/kodingkids.ini

# Set direktori kerja
WORKDIR /var/www/html

# Salin source code aplikasi ke dalam container
COPY . /var/www/html/

# Pastikan permission file sesuai untuk web server Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Port default Apache
EXPOSE 80

# Jalankan Apache di foreground
CMD ["apache2-foreground"]
