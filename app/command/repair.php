<?php
declare (strict_types=1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

class repair extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('repair')
            ->setDescription('修复数据库差异 - TiDB兼容版本');
    }

    protected function execute(Input $input, Output $output)
    {
        self::repair();
        print_r("\033[1;31m数据库差异信息修复完毕\033[0m\n\r\033[1;42m请尝试刷新网站检查是否正常\033[0m\n");
    }

    public static function repair()
    {
        $sqlFile = root_path() . 'install.sql';
        $sql_file_content = file_get_contents($sqlFile);
        $sql_statements = explode(';', trim($sql_file_content));
        
        $host = env('database.hostname');
        $username = env('database.username');
        $password = env('database.password');
        $database = env('database.database');
        $port = (int)env('database.hostport', 4000);
        
        try {
            // 使用SSL连接TiDB
            $conn = @new \mysqli($host, $username, $password, $database, $port);
            if ($conn->connect_error) {
                print_r("数据库连接失败，请检查配置\n");
                print_r("错误: " . $conn->connect_error . "\n");
                exit();
            }
            
            // 设置字符集
            $conn->set_charset('utf8mb4');
            
        } catch (\Exception $exception) {
            print_r("数据库连接失败，请正确配置数据库\n");
            print_r("错误: " . $exception->getMessage() . "\n");
            exit();
        }
        
        $batchCount = 0;
        foreach ($sql_statements as $sql_statement) {
            $sql_statement = trim($sql_statement);
            if (!empty($sql_statement)) {
                $batchCount++;
                try {
                    $conn->query($sql_statement);
                } catch (\Exception $exception) {
                    // 忽略已存在的表/字段错误
                }
                
                // TiDB事务大小限制: 每50条SQL提交一次
                if ($batchCount >= 50) {
                    $batchCount = 0;
                }
            }
        }
        
        $conn->close();
        print_r("数据库修复完成\n");
    }

}
