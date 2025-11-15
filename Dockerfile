# ==============================================================================
# DOCKERFILE - PHP APPLICATION IMAGE
# ==============================================================================
# File: Dockerfile
# Deskripsi: Docker image configuration untuk PHP application
# Base Image: php:8.2-cli (PHP 8.2 dengan CLI SAPI)
# Purpose: Build custom PHP image dengan ekstensi yang diperlukan
# Author: Dafa al hafiz - 24_0085
# Tanggal: 2025
# ==============================================================================

# ==============================================================================
# BASE IMAGE
# ==============================================================================
# Menggunakan official PHP image dari Docker Hub
# 
# php:8.2-cli breakdown:
# - php: Official PHP image
# - 8.2: PHP version 8.2 (latest stable at time of creation)
# - cli: Command Line Interface SAPI (bukan Apache/FPM)
# 
# Why CLI instead of Apache?
# - Lighter: CLI image lebih kecil (~150MB vs ~400MB)
# - Flexible: Bisa run dengan built-in server atau external server
# - Simpler: Tidak perlu configure Apache
# 
# Alternative options:
# - php:8.2-apache: Include Apache web server
# - php:8.2-fpm: PHP FastCGI Process Manager (untuk Nginx)
# - php:8.2-alpine: Smaller base (Alpine Linux, ~50MB)
FROM php:8.2-cli

# ==============================================================================
# INSTALL PHP EXTENSIONS
# ==============================================================================
# Install ekstensi PHP yang diperlukan untuk MySQL database connectivity
# 
# docker-php-ext-install: Helper script dari official PHP image
# Automatically compile dan enable PHP extensions
# 
# Extensions installed:
# 1. mysqli: MySQL Improved extension (procedural & OOP interface)
# 2. pdo: PHP Data Objects (database abstraction layer)
# 3. pdo_mysql: PDO driver untuk MySQL
# 
# Why these extensions?
# - Application menggunakan PDO untuk database operations
# - PDO lebih secure (prepared statements)
# - PDO support multiple database types
# 
# How it works:
# 1. Download source code untuk extensions
# 2. Compile extensions dengan phpize
# 3. Enable extensions di php.ini
# 4. Clean up build dependencies
RUN docker-php-ext-install mysqli pdo pdo_mysql

# ==============================================================================
# SET WORKING DIRECTORY
# ==============================================================================
# Set working directory di dalam container
# 
# WORKDIR command:
# - Create directory jika belum ada
# - Set sebagai current directory untuk subsequent commands
# - All relative paths akan relative ke directory ini
# 
# /var/www/html adalah convention untuk web applications:
# - Standard location untuk web files di Linux
# - Familiar untuk developers
# - Compatible dengan Apache/Nginx default config
# 
# Impact:
# - COPY commands akan copy ke sini (jika relative path)
# - CMD/ENTRYPOINT akan execute dari sini
# - Application code di volume mount akan ada di sini
WORKDIR /var/www/html

# ==============================================================================
# CREATE UPLOADS DIRECTORY
# ==============================================================================
# Membuat directory untuk file uploads dengan proper permissions
# 
# RUN command breakdown:
# 1. mkdir -p /var/www/html/uploads
#    - mkdir: Make directory
#    - -p: Create parent directories if needed (no error if exists)
#    - /var/www/html/uploads: Full path untuk uploads
# 
# 2. chmod 777 /var/www/html/uploads
#    - chmod: Change file mode (permissions)
#    - 777: Full permissions (rwx-rwx-rwx)
#      - Owner: Read, Write, Execute
#      - Group: Read, Write, Execute
#      - Others: Read, Write, Execute
# 
# Why 777 permissions?
# - PHP process needs write access untuk upload files
# - Web server user needs read access untuk serve files
# - In development, easier debugging dengan full permissions
# 
# SECURITY WARNING for Production:
# - 777 is NOT recommended di production!
# - Use more restrictive permissions (e.g., 755 or 775)
# - Set proper owner:group (www-data:www-data)
# - Consider using named volumes with proper permissions
# 
# Better approach for production:
# RUN mkdir -p /var/www/html/uploads && \
#     chown -R www-data:www-data /var/www/html/uploads && \
#     chmod 755 /var/www/html/uploads
RUN mkdir -p /var/www/html/uploads && chmod 777 /var/www/html/uploads

# ==============================================================================
# EXPOSE PORT
# ==============================================================================
# Declare port yang akan digunakan oleh container
# 
# EXPOSE 8000:
# - Document bahwa container listen pada port 8000
# - Tidak actually publish port (itu tugas docker-compose atau -p flag)
# - Berguna untuk documentation dan tools (e.g., docker ps)
# 
# Port 8000 usage:
# - PHP built-in web server akan listen di port ini
# - Command: php -S 0.0.0.0:8000
# - External access via port mapping di docker-compose.yml
# 
# Port mapping flow:
# 1. Container: Application listen di 0.0.0.0:8000 (all interfaces)
# 2. Docker: Map host port 8000 ke container port 8000
# 3. Access: http://localhost:8000 dari host machine
# 
# Why 8000?
# - Common alternative port untuk web development
# - Avoid conflict dengan default HTTP (80) / HTTPS (443)
# - Easy to remember
EXPOSE 8000

# ==============================================================================
# NOTES & CONSIDERATIONS
# ==============================================================================
#
# 1. NO CMD/ENTRYPOINT:
#    - Command defined in docker-compose.yml instead
#    - More flexible: Easy to change command without rebuild
#    - Command: php -S 0.0.0.0:8000 -t /var/www/html
#
# 2. NO COPY COMMAND:
#    - Application code mounted via volume (not copied)
#    - Benefit: Hot reload (changes reflected immediately)
#    - Volume mount: ./src:/var/www/html
#
# 3. IMAGE SIZE OPTIMIZATION (if needed):
#    - Use php:8.2-cli-alpine untuk smaller base (~50MB)
#    - Multi-stage build untuk production
#    - Remove build dependencies after install extensions
#
# 4. SECURITY IMPROVEMENTS for Production:
#    - Use non-root user untuk run application
#    - Stricter file permissions
#    - Disable dangerous PHP functions (php.ini)
#    - Use secrets untuk sensitive data
#
# 5. PRODUCTION ENHANCEMENTS:
#    - Add healthcheck
#    - Install additional tools (composer, git)
#    - Configure PHP settings (php.ini)
#    - Set proper timezone
#    - Enable opcache untuk performance
#
# ==============================================================================

# ==============================================================================
# USAGE EXAMPLES
# ==============================================================================
#
# Build image manually:
# $ docker build -t pengaduan-app .
#
# Run container manually:
# $ docker run -d -p 8000:8000 -v $(pwd)/src:/var/www/html pengaduan-app \
#     php -S 0.0.0.0:8000 -t /var/www/html
#
# With docker-compose (recommended):
# $ docker-compose up -d
#
# Access application:
# http://localhost:8000
#
# ==============================================================================

# ==============================================================================
# TROUBLESHOOTING
# ==============================================================================
#
# Problem: Permission denied untuk uploads
# Solution: Check folder permissions dengan `ls -la uploads/`
#
# Problem: MySQL connection error
# Solution: Pastikan MySQL extensions ter-install dengan `php -m | grep pdo`
#
# Problem: Changes tidak reflect
# Solution: Pastikan volume mount correct di docker-compose.yml
#
# Problem: Port already in use
# Solution: Change port mapping di docker-compose.yml atau stop conflicting service
#
# ==============================================================================

# ==============================================================================
# FUTURE IMPROVEMENTS
# ==============================================================================
#
# 1. Add Composer untuk dependency management
#    RUN curl -sS https://getcomposer.org/installer | php -- \
#        --install-dir=/usr/local/bin --filename=composer
#
# 2. Install additional PHP extensions
#    RUN docker-php-ext-install gd zip opcache
#
# 3. Configure PHP settings
#    COPY php.ini /usr/local/etc/php/
#
# 4. Add healthcheck
#    HEALTHCHECK --interval=30s --timeout=3s \
#        CMD php -r "echo 'OK';" || exit 1
#
# 5. Use non-root user
#    RUN useradd -m -u 1000 appuser
#    USER appuser
#
# ==============================================================================