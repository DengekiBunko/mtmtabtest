# =====================================================
# mTab Dockerfile - Hugging Face Spaces 优化版本
# 支持 TiDB Cloud SSL 连接
# =====================================================
FROM php:8.2-fpm

# 安装系统依赖
RUN apt-get update && apt-get install -y \
    nginx \
    redis-server \
    libpng-dev \
    libjpeg-dev \
    libfreetype-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    wget \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# 配置GD库
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# 安装PHP扩展
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    gd \
    zip \
    bcmath \
    opcache \
    mbstring \
    xml \
    json \
    ctype \
    session

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
