<?php
/**
 * TiDB兼容的数据库连接配置
 * 支持 HuggingFace Spaces 自动安装
 * 
 * HuggingFace Spaces secrets 环境变量:
 * - DB_HOST: TiDB主机地址
 * - DB_PORT: TiDB端口 (默认4000)
 * - DB_USER: 数据库用户名
 * - DB_PASSWORD: 数据库密码
 * - DB_NAME: 数据库名称
 * - ADMIN_USER: 管理员账号 (可选，默认admin)
 * - ADMIN_PASSWORD: 管理员密码 (可选，默认123456)
 * - DB_SSL_CA_PEM: SSL证书内容 (可选，自动使用系统证书)
 */

// 获取HuggingFace Spaces secrets环境变量
$TIDB_HOST = getenv("DB_HOST") ?: getenv("TIDB_HOST") ?: getenv("MYSQL_HOST");
$TIDB_PORT = getenv("DB_PORT") ?: getenv("TIDB_PORT") ?: getenv("MYSQL_PORT") ?: '4000';
$TIDB_USER = getenv("DB_USER") ?: getenv("TIDB_USER") ?: getenv("MYSQL_USER");
$TIDB_PASSWORD = getenv("DB_PASSWORD") ?: getenv("TIDB_PASSWORD") ?: getenv("MYSQL_PASSWORD");
$TIDB_DATABASE = getenv("DB_NAME") ?: getenv("TIDB_DATABASE") ?: getenv("MYSQL_DATABASE") ?: 'mtab';
$ADMIN_USER = getenv("ADMIN_USER") ?: 'admin';
$ADMIN_PASSWORD = getenv("ADMIN_PASSWORD") ?: '123456';
$DB_SSL_CA_PEM = getenv("DB_SSL_CA_PEM"); // SSL证书内容

// HuggingFace Spaces环境标识
$IS_HF_SPACES = getenv("HF_SPACE") === "true" || getenv("SPACE_ID") !== false;

// 确保端口是整数
$TIDB_PORT = (int)$TIDB_PORT;

print_r("========================================\n");
print_r("mTab 新标签页 - TiDB自动安装程序\n");
print_r("========================================\n");

$status = false;
$install_lock_file = '/app/public/installed.lock';
$env_file = '/app/.env';

// 如果已安装，直接更新.env配置（secrets可能已更新）
if (file_exists($install_lock_file)) {
    print_r("检测到已安装配置，正在检查数据库连接...\n");
    
    if ($TIDB_HOST && $TIDB_USER) {
        // 更新.env配置
        $env = generateEnvContent($TIDB_HOST, $TIDB_PORT, $TIDB_USER, $TIDB_PASSWORD, $TIDB_DATABASE, $DB_SSL_CA_PEM);
        file_put_contents($env_file, $env);
        print_r(".env配置文件已更新\n");
    }
    
    // 检查数据库连接
    if (testDbConnection($TIDB_HOST, $TIDB_PORT, $TIDB_USER, $TIDB_PASSWORD, $TIDB_DATABASE, $DB_SSL_CA_PEM)) {
        print_r("数据库连接正常\n");
    } else {
        print_r("警告: 数据库连接失败，请检查secrets配置\n");
    }
    
    print_r("初始化完成 - 跳过安装页面\n");
    print_r("========================================\n");
    exit(0);
}

// 执行全新安装
if ($TIDB_HOST && $TIDB_USER && $TIDB_PASSWORD && $TIDB_DATABASE) {
    print_r("检测到数据库配置，开始自动安装...\n");
    print_r("数据库主机: $TIDB_HOST:$TIDB_PORT\n");
    print_r("数据库名称: $TIDB_DATABASE\n");
    print_r("========================================\n");
    
    // 尝试连接数据库
    $conn = createDbConnection($TIDB_HOST, $TIDB_PORT, $TIDB_USER, $TIDB_PASSWORD, null, $DB_SSL_CA_PEM);
    
    if ($conn && !$conn->connect_error) {
        print_r("数据库连接成功\n");
        
        // 创建数据库
        $sql = "CREATE DATABASE IF NOT EXISTS `{$TIDB_DATABASE}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
        if ($conn->query($sql) !== TRUE) {
            print_r("数据库创建提示: " . $conn->error . "\n");
        }
        print_r("数据库创建完毕\n");
        $conn->close();
        
        // 重新连接数据库
        $conn = createDbConnection($TIDB_HOST, $TIDB_PORT, $TIDB_USER, $TIDB_PASSWORD, $TIDB_DATABASE, $DB_SSL_CA_PEM);
        
        if ($conn && !$conn->connect_error) {
            $conn->set_charset('utf8mb4');
            print_r("执行建表SQL...\n");
            
            // 执行install.sql
            $sql_file_content = file_get_contents('/app/install.sql');
            $sql_statements = explode(';', trim($sql_file_content));
            
            foreach ($sql_statements as $sql_statement) {
                $sql_statement = trim($sql_statement);
                if (!empty($sql_statement) && !preg_match('/^--/', $sql_statement)) {
                    try {
                        $conn->query($sql_statement);
                    } catch (\Exception $e) {
                        // 忽略已存在的表/字段错误
                    }
                }
            }
            print_r("数据表创建完毕\n");
            
            // 插入默认数据
            $sql_file_content = file_get_contents('/app/defaultData.sql');
            $sql_statements = explode(';', trim($sql_file_content));
            foreach ($sql_statements as $sql_statement) {
                $sql_statement = trim($sql_statement);
                if (!empty($sql_statement) && !preg_match('/^--/', $sql_statement)) {
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
                // 使用雪花ID创建管理员
                $snowflakeId = generateSnowflakeId();
                $AdminSql = "INSERT INTO user (id, mail, password, create_time, login_ip, register_ip, manager, login_fail_count, login_time) 
                             VALUES ($snowflakeId, '" . $conn->real_escape_string($ADMIN_USER) . "', '$admin_password', NOW(), null, null, 1, 0, null)";
                try {
                    $conn->query($AdminSql);
                    print_r("管理员账号创建完毕: $ADMIN_USER / $ADMIN_PASSWORD\n");
                } catch (\Exception $e) {
                    print_r("管理员创建提示: " . $e->getMessage() . "\n");
                }
            } else {
                print_r("管理员账号已存在\n");
            }
            
            // 创建用户分组
            $checkGroup = "SELECT id FROM user_group WHERE default_user_group = 1";
            $groupExists = $conn->query($checkGroup);
            if (!$groupExists || $groupExists->num_rows == 0) {
                $groupSnowflakeId = generateSnowflakeId();
                $groupSql = "INSERT INTO user_group (id, name, create_time, sort, default_user_group) VALUES ($groupSnowflakeId, '默认分组', NOW(), 0, 1)";
                try {
                    $conn->query($groupSql);
                    print_r("默认用户分组创建完毕\n");
                } catch (\Exception $e) {
                    // 忽略重复错误
                }
            }
            
            $conn->close();
        } else {
            print_r("连接数据库失败: " . ($conn ? $conn->connect_error : "未知错误") . "\n");
        }
    } else {
        print_r("连接数据库失败: " . ($conn ? $conn->connect_error : "未知错误") . "\n");
    }
    
    // 生成.env配置文件
    $env = generateEnvContent($TIDB_HOST, $TIDB_PORT, $TIDB_USER, $TIDB_PASSWORD, $TIDB_DATABASE, $DB_SSL_CA_PEM);
    file_put_contents($env_file, $env);
    print_r(".env配置文件已生成\n");
    
    // 创建安装锁定文件
    file_put_contents($install_lock_file, 'installed');
    print_r("安装锁定文件已创建\n");
    
    $status = true;
    print_r("========================================\n");
    print_r("安装完成!\n");
    print_r("管理员账号: $ADMIN_USER\n");
    print_r("管理员密码: $ADMIN_PASSWORD\n");
    print_r("========================================\n");
} else {
    print_r("未检测到数据库配置\n");
    print_r("请在 HuggingFace Spaces Secrets 中设置以下环境变量:\n");
    print_r("  - DB_HOST: TiDB主机地址\n");
    print_r("  - DB_PORT: TiDB端口 (默认4000)\n");
    print_r("  - DB_USER: 数据库用户名\n");
    print_r("  - DB_PASSWORD: 数据库密码\n");
    print_r("  - DB_NAME: 数据库名称\n");
    print_r("========================================\n");
}

print_r("初始化完成\n");

/**
 * 生成.env配置文件内容
 */
function generateEnvContent($host, $port, $user, $password, $database, $sslCaPem = null) {
    $sslConfig = '';
    
    if ($sslCaPem) {
        // 如果提供了SSL证书，写入临时文件
        $sslCaFile = '/app/runtime/tidb_ca.pem';
        if (!is_dir('/app/runtime')) {
            mkdir('/app/runtime', 0755, true);
        }
        file_put_contents($sslCaFile, $sslCaPem);
        $sslConfig = "\nMYSQL_SSL_CA = {$sslCaFile}";
    }
    
    return <<<EOF
APP_DEBUG = false

[APP]

[DATABASE]
TYPE = mysql
HOSTNAME = {$host}
DATABASE = {$database}
USERNAME = {$user}
PASSWORD = {$password}
HOSTPORT = {$port}
CHARSET = utf8mb4
DEBUG = false
{$sslConfig}

[CACHE]
DRIVER = file

EOF;
}

/**
 * 创建数据库连接
 */
function createDbConnection($host, $port, $user, $password, $database, $sslCaPem = null) {
    // 如果提供了SSL证书，写入临时文件
    $sslCaFile = null;
    if ($sslCaPem) {
        $sslCaFile = '/app/runtime/tidb_ca_' . md5($sslCaPem) . '.pem';
        if (!is_dir('/app/runtime')) {
            mkdir('/app/runtime', 0755, true);
        }
        // 检查证书是否已存在且内容一致
        if (!file_exists($sslCaFile) || file_get_contents($sslCaFile) !== $sslCaPem) {
            file_put_contents($sslCaFile, $sslCaPem);
        }
    }
    
    // 使用PDO进行连接（更好的SSL支持）
    try {
        $dsn = "mysql:host={$host};port={$port}";
        if ($database) {
            $dsn .= ";dbname={$database}";
        }
        $dsn .= ";charset=utf8mb4";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 30,
        ];
        
        // TiDB Cloud强制要求SSL
        if ($sslCaFile && file_exists($sslCaFile)) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCaFile;
        } else {
            // 使用系统证书池
            $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
        }
        
        // 禁用服务器证书验证（自动续期兼容）
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        
        $pdo = new PDO($dsn, $user, $password, $options);
        
        // 返回mysqli连接（兼容现有代码）
        return new mysqli($host, $user, $password, $database ?: null, $port);
        
    } catch (PDOException $e) {
        // 回退到mysqli
        $conn = @new mysqli($host, $user, $password, $database ?: null, $port);
        return $conn;
    }
}

/**
 * 测试数据库连接
 */
function testDbConnection($host, $port, $user, $password, $database, $sslCaPem = null) {
    $conn = createDbConnection($host, $port, $user, $password, $database, $sslCaPem);
    return $conn && !$conn->connect_error;
}

/**
 * 生成雪花ID（简化版，用于安装脚本）
 */
function generateSnowflakeId() {
    // 简化版雪花ID生成
    $workerId = 1;
    $sequence = mt_rand(0, 4095);
    $time = (int)(microtime(true) * 1000) - 1577836800000; // 2020-01-01
    return ($time << 22) | ($workerId << 17) | ($sequence);
}
