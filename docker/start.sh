#!/bin/bash
# =====================================================
# mTab Hugging Face Spaces 启动脚本
# 支持 TiDB Cloud SSL 自动连接
# 自动读取 HuggingFace Spaces Secrets 配置
# =====================================================

set -e

echo "========================================"
echo "mTab - 浏览器新标签页启动中..."
echo "========================================"

# 设置工作目录
cd /app

# 检查是否需要自动安装
if [ ! -f "/app/public/installed.lock" ] || [ ! -f "/app/.env" ]; then
    echo "首次启动或配置缺失，正在检查数据库配置..."
    
    # 从环境变量获取数据库配置（HuggingFace Spaces Secrets）
    DB_HOST="${DB_HOST:-${MYSQL_HOST:-}}"
    DB_PORT="${DB_PORT:-${MYSQL_PORT:-4000}}"
    DB_USER="${DB_USER:-${MYSQL_USER:-}}"
    DB_PASSWORD="${DB_PASSWORD:-${MYSQL_PASSWORD:-}}"
    DB_NAME="${DB_NAME:-${MYSQL_DATABASE:-mtab}}"
    ADMIN_USER="${ADMIN_USER:-admin}"
    ADMIN_PASSWORD="${ADMIN_PASSWORD:-123456}"
    
    if [ -n "$DB_HOST" ] && [ -n "$DB_USER" ] && [ -n "$DB_PASSWORD" ]; then
        echo "检测到数据库配置，开始自动安装..."
        echo "数据库主机: $DB_HOST:$DB_PORT"
        echo "数据库名称: $DB_NAME"
        
        # 执行安装脚本
        php /app/env.php
        
        if [ $? -eq 0 ]; then
            echo "自动安装完成"
        else
            echo "自动安装失败，请检查数据库配置"
        fi
    else
        echo "未检测到数据库配置"
        echo "请在 HuggingFace Spaces Secrets 中设置以下环境变量:"
        echo "  - DB_HOST: TiDB主机地址"
        echo "  - DB_PORT: TiDB端口 (默认4000)"
        echo "  - DB_USER: 数据库用户名"
        echo "  - DB_PASSWORD: 数据库密码"
        echo "  - DB_NAME: 数据库名称"
    fi
else
    echo "检测到已安装配置"
    
    # 检查是否需要更新配置（secrets可能已更新）
    DB_HOST="${DB_HOST:-${MYSQL_HOST:-}}"
    if [ -n "$DB_HOST" ]; then
        echo "更新数据库配置..."
        php /app/env.php
    fi
fi

# 创建必要目录
mkdir -p /run/php /var/www/html /app/runtime /app/public/static /app/public/images 2>/dev/null || true
chmod -R 755 /app 2>/dev/null || true

# 检查并安装SSL证书（如果需要）
if [ -f "/etc/ssl/certs/ca-certificates.crt" ]; then
    echo "系统SSL证书可用"
fi

# 启动 PHP-FPM
echo "启动 PHP-FPM..."
if command -v php-fpm8.2 &> /dev/null; then
    php-fpm8.2 &
elif command -v php-fpm8.3 &> /dev/null; then
    php-fpm8.3 &
elif command -v php-fpm &> /dev/null; then
    php-fpm &
fi

# 启动 Nginx
echo "启动 Nginx..."
nginx &

# 记录启动信息
echo ""
echo "========================================"
echo "mTab 服务已启动"
echo "访问地址: http://localhost:7860"
echo "管理员账号: ${ADMIN_USER:-admin}"
echo "管理员密码: ${ADMIN_PASSWORD:-123456}"
echo "========================================"
echo ""

# 保持容器运行并监控进程
while true; do
    sleep 30
    
    # 检查进程状态
    if command -v pgrep &> /dev/null; then
        if ! pgrep -x "php-fpm" > /dev/null 2>&1; then
            echo "Warning: PHP-FPM not running, restarting..."
            (php-fpm8.2 || php-fpm8.3 || php-fpm) &
        fi
        
        if ! pgrep -x "nginx" > /dev/null 2>&1; then
            echo "Warning: Nginx not running, restarting..."
            nginx &
        fi
    fi
    
    # 检查证书是否需要更新（每小时检查一次）
    DB_SSL_CA_PEM="${DB_SSL_CA_PEM:-}"
    if [ -n "$DB_SSL_CA_PEM" ] && [ -f "/app/runtime/tidb_ca.pem" ]; then
        CURRENT_CERT=$(cat /app/runtime/tidb_ca.pem 2>/dev/null || echo "")
        if [ "$CURRENT_CERT" != "$DB_SSL_CA_PEM" ]; then
            echo "检测到SSL证书更新，正在更新..."
            echo "$DB_SSL_CA_PEM" > /app/runtime/tidb_ca.pem
            echo "SSL证书已更新"
        fi
    fi
done
