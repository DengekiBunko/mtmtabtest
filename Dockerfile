# =====================================================
# mTab Dockerfile - Hugging Face Spaces 优化版本
# 支持 TiDB Cloud SSL 连接
# =====================================================
FROM php:8.2-fpm

# 安装基础运行时工具
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    redis-server \
    unzip \
    git \
    curl \
    wget \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# 安装 PHP 编译依赖
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# 安装 PHP 扩展 - 第一阶段：数据库
RUN docker-php-ext-install -j$(nproc) pdo_mysql mysqli

# 安装 PHP 扩展 - 第二阶段：图形和压缩 (合并 configure 和 install)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip

# 安装 PHP 扩展 - 第三阶段：数学和进程
RUN docker-php-ext-install -j$(nproc) bcmath pcntl

# 安装 Redis 扩展
RUN pecl install redis && docker-php-ext-enable redis

# 安装 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 设置工作目录
WORKDIR /app

# 复制应用代码
COPY . /app

# 安装依赖
RUN composer install --no-dev --optimize-autoloader

# 配置nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/http.d/default.conf

# 创建必要目录
RUN mkdir -p /run/nginx /var/www/html /app/runtime /app/public/static

# 设置权限
RUN chown -R www-data:www-data /app

# 复制启动脚本
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# 暴露端口
EXPOSE 7860

# 启动命令
CMD ["/bin/bash", "/start.sh"]
