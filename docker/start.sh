#!/bin/bash
# =====================================================
# mTab Hugging Face Spaces 启动脚本
# 支持 TiDB Cloud SSL 连接
# =====================================================

set -e

echo "========================================"
echo "mTab - 浏览器新标签页启动中..."
echo "========================================"

# 设置工作目录
cd /app

# 加载环境变量
if [ -f "/app/.env" ]; then
    echo "加载配置文件..."
else
    echo "生成配置文件..."
    
    # TiDB 连接配置
    TIDB_HOST="${TIDB_HOST:-${MYSQL_HOST}}"
    TIDB_PORT="${TIDB_PORT:-${MYSQL_PORT:-4000}}"
    TIDB_USER="${TIDB_USER:-${MYSQL_USER}}"
    TIDB_PASSWORD="${TIDB_PASSWORD:-${MYSQL_PASSWORD}}"
    TIDB_DATABASE="${TIDB_DATABASE:-${MYSQL_DATABASE:-mtab}}"
    
    # 管理员配置
    ADMIN_USER="${ADMIN_USER:-admin}"
    ADMIN_PASSWORD="${ADMIN_PASSWORD:-123456}"
    
    # 生成 .env 文件
    cat > /app/.env << EOF
APP_DEBUG = false

[APP]

[DATABASE]
TYPE = mysql
HOSTNAME = ${TIDB_HOST}
DATABASE = ${TIDB_DATABASE}
USERNAME = ${TIDB_USER}
PASSWORD = ${TIDB_PASSWORD}
HOSTPORT = ${TIDB_PORT}
CHARSET = utf8mb4
DEBUG = false

[CACHE]
DRIVER = file

EOF
    echo ".env 配置文件已生成"
fi

# 检查数据库连接并初始化
if [ ! -f "/app/public/installed.lock" ]; then
    echo "首次启动，正在初始化数据库..."
    php /app/env.php
    
    if [ $? -eq 0 ]; then
        echo "数据库初始化完成"
    else
        echo "数据库初始化失败，请检查 TiDB 连接配置"
    fi
else
    echo "检测到已安装，跳过初始化..."
fi

# 启动 PHP-FPM
echo "启动 PHP-FPM..."
php-fpm8.2 &

# 启动 Nginx
echo "启动 Nginx..."
nginx &

# 记录启动时间
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
    if ! pgrep -x "php-fpm" > /dev/null; then
        echo "Warning: PHP-FPM not running, restarting..."
        php-fpm8.2 &
    fi
    
    if ! pgrep -x "nginx" > /dev/null; then
        echo "Warning: Nginx not running, restarting..."
        nginx &
    fi
done
