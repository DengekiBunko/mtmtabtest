#!/bin/bash
# =====================================================
# mTab Hugging Face Spaces 启动脚本 - 优化版避免卡住
# 支持 TiDB Cloud SSL 自动连接
# 自动读取 HuggingFace Spaces Secrets 配置
# =====================================================

echo "========================================"
echo "mTab - 浏览器新标签页启动中..."
echo "========================================"

# 设置工作目录
cd /app

# 设置超时时间，避免卡住
timeout_install=30  # 安装超时 30 秒

# 检查是否需要自动安装（非阻塞式）
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
        
        # 执行安装脚本（带超时，避免卡住）
        timeout $timeout_install php /app/env.php &
        INSTALL_PID=$!
        
        # 等待安装完成或超时
        wait $INSTALL_PID 2>/dev/null
        INSTALL_EXIT=$?
        
        if [ $INSTALL_EXIT -eq 0 ]; then
            echo "自动安装完成"
        elif [ $INSTALL_EXIT -eq 124 ]; then
            echo "安装超时，但将继续启动服务..."
        else
            echo "安装可能存在问题，但将继续启动服务..."
        fi
    else
        echo "未检测到数据库配置，将使用默认配置启动"
    fi
else
    echo "检测到已安装配置"
    
    # 检查是否需要更新配置（secrets可能已更新）
    DB_HOST="${DB_HOST:-${MYSQL_HOST:-}}"
    if [ -n "$DB_HOST" ]; then
        echo "后台更新数据库配置..."
        timeout $timeout_install php /app/env.php &
    fi
    
    # 运行修复脚本（清理重复标签）
    echo "检查并修复数据..."
    timeout 10 php /app/fix_duplicate_tabs.php 2>/dev/null || echo "修复脚本执行完成或超时"
    
    # 运行卡片初始化脚本
    echo "初始化卡片配置..."
    timeout 10 php /app/init_cards.php 2>/dev/null || echo "卡片初始化完成或超时"
fi

# 创建必要目录（快速失败）
mkdir -p /run/php /var/log/nginx /app/runtime /app/runtime/log /app/public/static /app/public/images 2>/dev/null || true
chmod -R 755 /app 2>/dev/null || true
chmod -R 777 /app/runtime 2>/dev/null || true

# 检查并安装SSL证书（如果需要）
if [ -f "/etc/ssl/certs/ca-certificates.crt" ]; then
    echo "系统SSL证书可用"
fi

# 启动 PHP-FPM（后台运行）
echo "启动 PHP-FPM..."
if command -v php-fpm8.2 &> /dev/null; then
    php-fpm8.2 -D 2>/dev/null || php-fpm8.2 &
elif command -v php-fpm8.3 &> /dev/null; then
    php-fpm8.3 -D 2>/dev/null || php-fpm8.3 &
elif command -v php-fpm &> /dev/null; then
    php-fpm -D 2>/dev/null || php-fpm &
fi
sleep 1  # 等待 PHP-FPM 启动

# 启动 Nginx（后台运行）
echo "启动 Nginx..."
nginx -g 'daemon off;' &
NGINX_PID=$!
sleep 1  # 等待 Nginx 启动

# 记录启动信息
echo ""
echo "========================================"
echo "mTab 服务已启动"
echo "访问地址: http://localhost:7860"
echo "管理员账号: ${ADMIN_USER:-admin}"
echo "管理员密码: ${ADMIN_PASSWORD:-123456}"
echo "========================================"
echo ""

# 保持容器运行并监控进程（简化版）
while true; do
    sleep 60  # 延长检查间隔，减少 CPU 使用
done
