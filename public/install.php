<?php
//设置永不超时
set_time_limit(0);
//设置内存可以使用512MB
ini_set('memory_limit', '512M');
function isAjaxRequest(): bool
{
    if (isset($_SERVER['HTTP_REFERER'])) {
        return true;
    } else {
        return false;
    }
}

if (file_exists("./installed.lock")) {
    if (isAjaxRequest()) {
        echo json_encode(['code' => 1000, 'msg' => '系统已安装，请勿重复安装！'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    header("Location: /");
    exit;
}

//允许后台运行
ini_set('max_execution_time', 0);
//取消超时限制
set_time_limit(0);
//内存允许更大的
ini_set('memory_limit', '512M');

class Install
{
    protected $defError = [
        1045 => [
            'reason' => '用户名或密码错误，或用户没有访问权限。'
        ],
        2002 => [
            'reason' => '数据库服务器未运行或无法连接到指定的主机和端口。'
        ],
        1049 => [
            'reason' => '指定的数据库不存在。'
        ],
        1044 => [
            'reason' => '用户没有访问指定数据库的权限。'
        ],
        2003 => [
            'reason' => '无法连接到 MySQL 服务器，可能是防火墙阻止了连接。'
        ]
    ];

    function __construct()
    {
    }

    //内容，是否换行
    function output($content, $is_enter = false)
    {
        $content = trim($content);
        echo $content . "\n";
        flush();
    }

    function json($arr)
    {
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    function index(): string
    {
        $var = [
            'title' => 'mTab新标签页安装程序',
            'customHead|raw' => '',
            'keywords' => '',
            'description' => '',
            'favicon' => '/static/mtab.png',
            'version' => time()
        ];
        //正则替换模板变量{$title},{$customHead|raw}
        $template = file_get_contents(__DIR__ . '/dist/index.html');
        return preg_replace_callback('/{\$([\w|]+)}/', function ($matches) use ($var) {
            $key = $matches[1];
            return $var[$key] ?? '';
        }, $template);
    }

    function connect($form, $tableName = null): mysqli
    {
        //将上面的值提取到数据库配置中
        $db_host = $form['db_host'];
        $db_username = $form['db_username'];
        $db_password = $form['db_password'];
        $db_port = $form['db_port'];
        $conn = @new mysqli($db_host, $db_username, $db_password, $tableName, $db_port);
        if ($conn->connect_error) {
            $errorCode = $conn->connect_errno; // 获取错误代码
            // 检查 defError 中是否存在该错误代码
            $errorMessage = isset($this->defError[$errorCode])
                ? $this->defError[$errorCode]['reason']
                : $conn->connect_error; // 如果不存在，返回原始错误信息
            throw new Exception($errorMessage, 500);
        }

        return $conn;
    }

    function ext()
    {
        $phpVersion = phpversion();
        $php_version = false;
        if (version_compare($phpVersion, '7.4', '>')) {
            $php_version = true;
        }
        $fileinfo_ext = false;
        if (extension_loaded('fileinfo')) {
            $fileinfo_ext = true;
        }
        $zip_ext = false;
        if (extension_loaded('zip')) {
            $zip_ext = true;
        }
        $mysqli_ext = false;
        if (extension_loaded('mysqli')) {
            $mysqli_ext = true;
        }
        $curl_ext = false;
        if (extension_loaded('curl')) {
            $curl_ext = true;
        }
        return $this->json(['code' => 200, 'data' => [
            'php_version' => $php_version,
            'fileinfo_ext' => $fileinfo_ext,
            'zip_ext' => $zip_ext,
            'mysqli_ext' => $mysqli_ext,
            'curl_ext' => $curl_ext
        ]]);
    }

    function testDb()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $conn = $this->connect($data);
            $conn->close();
            return $this->json(['code' => 200, 'msg' => '连接成功']);
        } catch (Exception $e) {
            return $this->json(['code' => 500, 'msg' => $e->getMessage()]);
        }
    }

    function install()
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        $form = json_decode(file_get_contents('php://input'), true);
        $database_type = $form['database_type'];
        $table_name = $form['table_name'];
        $db_host = $form['db_host'];
        $db_username = $form['db_username'];
        $db_password = $form['db_password'];
        $db_port = $form['db_port'];
        $admin_password = $form['admin_password'];
        $admin_email = $form['admin_email'];
        try {
            $conn = $this->connect($form);
        } catch (\Exception $e) {
            return $this->json(['code' => 500, 'msg' => $e->getMessage()]);
        }
        if ($database_type == 1) { //全新安装
            $sql = "DROP DATABASE $table_name"; //删除原来的
            $conn->query($sql);
            $sql = "CREATE DATABASE `$table_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;";
            if ($conn->query($sql) !== TRUE) {
                return $this->json(['code' => 500, 'msg' => '数据表创建失败']);
            }
            $conn->close();
            $conn = $this->connect($form, $table_name);
            $this->output("连接数据库成功...", true);
            //数据库的格式内容数据
            $sql_file_content = file_get_contents('../install.sql');
            // 解析SQL文件内容并执行
            $sql_statements = explode(';', trim($sql_file_content));
            $this->output("执行数据库建表SQL语句", true);
            foreach ($sql_statements as $sql_statement) {
                if (!empty($sql_statement)) {
                    try {
                        $conn->query($sql_statement);
                        $this->output("$sql_statement", true);
                    } catch (Exception $exception) {
                    }
                }
            }
            //默认的一些基础数据
            $sql_file_content = file_get_contents('../defaultData.sql');
            $this->output("导入默认的数据...", true);
            // 解析SQL文件内容并执行
            $sql_statements = explode(';', trim($sql_file_content));
            $this->output("执行SQL语句", true);
            foreach ($sql_statements as $sql_statement) {
                if (!empty($sql_statement)) {
                    try {
                        $conn->query($sql_statement);
                        $this->output("$sql_statement", true);
                    } catch (Exception $exception) {
                    }
                }
            }
            $admin_password = md5($admin_password);
            //添加默认管理员
            $AdminSql = ("
                    INSERT INTO user (mail, password, create_time, login_ip, register_ip, manager, login_fail_count, login_time)
                    VALUES ('$admin_email', '$admin_password', null, null, null, 1, DEFAULT, null);
                 ");
            $conn->query($AdminSql);
            $this->output("添加管理员成功...", true);
            $conn->close();
        }
        $env = <<<EOF
                APP_DEBUG = false
                
                [APP]
                
                [DATABASE]
                TYPE = mysql
                HOSTNAME = {$db_host}
                DATABASE = {$table_name}
                USERNAME = {$db_username}
                PASSWORD = {$db_password}
                HOSTPORT =  {$db_port}
                CHARSET = utf8mb4
                DEBUG = false
                
                [CACHE]
                DRIVER = file
                
                EOF;
        file_put_contents('../.env', $env);
        $this->output("配置文件写入成功=><b style='color:red'>.env</b>", true);
        file_put_contents('./installed.lock', 'installed');
        $this->output("安装完成...", true);
        return "\nmTabDONE";
    }
}

$handle = new Install();
$path = "/";
if (isset($_GET['s'])) {
    $path = $_GET['s'];
}
switch ($path):
    case '/testDb':
        echo $handle->testDb();
        break;
    case '/install':
        echo $handle->install();
        break;
    case '/ext':
        echo $handle->ext();
        break;
    default:
        echo $handle->index();
        break;
endswitch;
exit;

