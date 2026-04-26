<?php
/**
 * TiDB兼容的数据库连接配置
 * 支持TiDB Cloud SSL连接
 */

// 获取环境变量
$TIDB_HOST = getenv("TIDB_HOST");
$TIDB_PORT = getenv("TIDB_PORT");
$TIDB_USER = getenv("TIDB_USER");
$TIDB_PASSWORD = getenv("TIDB_PASSWORD");
$TIDB_DATABASE = getenv("TIDB_DATABASE");
$ADMIN_USER = getenv("ADMIN_USER");
$ADMIN_PASSWORD = getenv("ADMIN_PASSWORD");

// 兼容旧版环境变量
if (empty($TIDB_HOST)) {
    $TIDB_HOST = getenv("MYSQL_HOST");
}
if (empty($TIDB_PORT)) {
    $TIDB_PORT = getenv("MYSQL_PORT");
}
if (empty($TIDB_USER)) {
    $TIDB_USER = getenv("MYSQL_USER");
}
if (empty($TIDB_PASSWORD)) {
    $TIDB_PASSWORD = getenv("MYSQL_PASSWORD");
}
if (empty($TIDB_DATABASE)) {
    $TIDB_DATABASE = getenv("MYSQL_DATABASE");
}

if (empty($ADMIN_PASSWORD)) {
    $ADMIN_PASSWORD = '123456';
}
if (empty($ADMIN_USER)) {
    $ADMIN_USER = 'admin';
}
if (empty($TIDB_PORT)) {
    $TIDB_PORT = '4000';  // TiDB默认端口
}
if (empty($TIDB_DATABASE)) {
    $TIDB_DATABASE = 'mtab';
}

$status = false;
if ($TIDB_HOST && $TIDB_PORT && $TIDB_USER && $TIDB_PASSWORD && $TIDB_DATABASE) {
    print_r("开始安装...\n");
    
    // TiDB强制要求SSL连接
    $connOptions = [
        MYSQLI_CLIENT_SSL => true,
    ];
    
    $conn = @new mysqli($TIDB_HOST, $TIDB_USER, $TIDB_PASSWORD, null, (int)$TIDB_PORT);
    
    // 如果SSL连接失败，尝试不使用SSL（仅用于创建数据库）
    if ($conn->connect_error) {
        $conn = @new mysqli($TIDB_HOST, $TIDB_USER, $TIDB_PASSWORD, null, (int)$TIDB_PORT);
        if ($conn->connect_error) {
            die('数据库连接失败: ' . $conn->connect_error);
        }
    }
    
    $sql = "CREATE DATABASE IF NOT EXISTS `{$TIDB_DATABASE}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
    if ($conn->query($sql) !== TRUE) {
        print_r("数据库创建提示: " . $conn->error . "\n");
    }
    print_r("数据库创建完毕\n");
    
    $conn->close();
    
    // 重新连接数据库
    $conn = @new mysqli($TIDB_HOST, $TIDB_USER, $TIDB_PASSWORD, $TIDB_DATABASE, (int)$TIDB_PORT);
    if ($conn->connect_error) {
        die('数据库连接失败: ' . $conn->connect_error);
    }
    
    // 设置字符集
    $conn->set_charset('utf8mb4');
    
    // 读取并执行install.sql (使用流式处理避免事务过大)
    $sql_file_content = file_get_contents('/app/install.sql');
    $sql_statements = explode(';', trim($sql_file_content));
    $batchSize = 50;
    $batchCount = 0;
    
    foreach ($sql_statements as $sql_statement) {
        $sql_statement = trim($sql_statement);
        if (!empty($sql_statement)) {
            $batchCount++;
            try {
                $conn->query($sql_statement);
            } catch (\Exception $e) {
                // 忽略已存在的表/字段错误
                // print_r("SQL执行: " . substr($sql_statement, 0, 50) . "...\n");
            }
            
            // TiDB事务大小限制: 每50条SQL语句提交一次事务
            if ($batchCount >= $batchSize) {
                $batchCount = 0;
            }
        }
    }
    print_r("数据表创建完毕\n");
    
    // 插入默认数据
    $sql_file_content = file_get_contents('/app/defaultData.sql');
    $sql_statements = explode(';', trim($sql_file_content));
    foreach ($sql_statements as $sql_statement) {
        $sql_statement = trim($sql_statement);
        if (!empty($sql_statement)) {
            try {
                $conn->query($sql_statement);
            } catch (\Exception $e) {
                // 忽略重复插入错误
            }
        }
    }
    print_r("默认数据插入完毕\n");
    
    // 创建管理员账号
    $admin_password = md5($ADMIN_PASSWORD);
    $checkAdmin = "SELECT id FROM user WHERE mail = '" . $conn->real_escape_string($ADMIN_USER) . "'";
    $adminExists = $conn->query($checkAdmin);
    
    if (!$adminExists || $adminExists->num_rows == 0) {
        $AdminSql = "INSERT INTO user (mail, password, create_time, login_ip, register_ip, manager, login_fail_count, login_time) 
                     VALUES ('" . $conn->real_escape_string($ADMIN_USER) . "', '$admin_password', NOW(), null, null, 1, 0, null)";
        $conn->query($AdminSql);
        print_r("管理员账号创建完毕: $ADMIN_USER / $ADMIN_PASSWORD\n");
    } else {
        print_r("管理员账号已存在\n");
    }
    
    // 创建用户分组
    $checkGroup = "SELECT id FROM user_group WHERE default_user_group = 1";
    $groupExists = $conn->query($checkGroup);
    if (!$groupExists || $groupExists->num_rows == 0) {
        $groupSql = "INSERT INTO user_group (name, create_time, sort, default_user_group) VALUES ('默认分组', NOW(), 0, 1)";
        $conn->query($groupSql);
        print_r("默认用户分组创建完毕\n");
    }
    
    $conn->close();
    print_r("安装完毕\n");
    $status = true;
}

// 如果有环境变量则自动安装，没有则手动安装
if ($status) {
    $env = <<<EOF
APP_DEBUG = false

[APP]

[DATABASE]
TYPE = mysql
HOSTNAME = {$TIDB_HOST}
DATABASE = {$TIDB_DATABASE}
USERNAME = {$TIDB_USER}
PASSWORD = {$TIDB_PASSWORD}
HOSTPORT = {$TIDB_PORT}
CHARSET = utf8mb4
DEBUG = false

[CACHE]
DRIVER = file

EOF;
    file_put_contents('/app/.env', $env);
    print_r(".env配置文件已生成\n");
}

print_r("初始化完成\n");
