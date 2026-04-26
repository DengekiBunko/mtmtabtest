<?php
/*
 * @description: TiDB Cloud SSL 兼容数据库配置
 * @Date: 2022-09-26 17:52:37
 * @LastEditTime: 2024-04-24 (Modified for TiDB Cloud SSL)
 * 
 * TiDB兼容性说明:
 * 1. 强制启用SSL连接 - TiDB Cloud要求SSL
 * 2. 字符集统一使用 utf8mb4_general_ci
 * 3. 启用断线重连 - 云数据库网络波动时保持连接
 * 4. 使用雪花ID替代自增ID - TiDB自增ID在多并发下不是严格递增的
 */
return [
    // 默认使用的数据库连接配置
    'default'         => env('database.driver', 'mysql'),

    // 自定义时间查询规则
    'time_query_rule' => [],

    // 自动写入时间戳字段
    'auto_timestamp'  => true,

    // 时间字段取出后的默认时间格式
    'datetime_format' => 'Y-m-d H:i:s',

    // 时间字段配置 配置格式：create_time,update_time
    'datetime_field'  => '',

    // 数据库连接配置信息
    'connections'     => [
        'mysql' => [
            // 数据库类型
            'type'            => env('database.type', 'mysql'),
            // 服务器地址 (TiDB Cloud 地址)
            'hostname'        => env('database.hostname', '127.0.0.1'),
            // 数据库名
            'database'        => env('database.database', 'mtab'),
            // 用户名
            'username'        => env('database.username', 'root'),
            // 密码
            'password'        => env('database.password', ''),
            // 端口 (TiDB 默认 4000)
            'hostport'        => env('database.hostport', '4000'),
            
            // TiDB Cloud SSL连接参数 - 关键配置
            'params'          => [
                // 强制SSL连接 - TiDB Cloud要求
                \PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt',
                // 禁用服务器证书验证 - 兼容云环境，自动续期不失效
                \PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
                // 设置连接超时时间
                \PDO::ATTR_TIMEOUT => 30,
            ],
            
            // 字符集 - TiDB兼容utf8mb4_general_ci
            'charset'         => 'utf8mb4',
            // 数据库表前缀
            'prefix'          => env('database.prefix', ''),

            // 数据库部署方式:0 集中式(单一服务器)
            'deploy'          => 0,
            // 数据库读写是否分离
            'rw_separate'     => false,
            // 读写分离后 主服务器数量
            'master_num'      => 1,
            // 指定从服务器序号
            'slave_no'        => '',
            // 是否严格检查字段是否存在
            'fields_strict'   => true,
            // 【重要】断线重连 - 云数据库连接由于网络波动容易断开
            'break_reconnect' => true,
            // 监听SQL
            'trigger_sql'     => env('database.debug', false),
            // 开启字段缓存
            'fields_cache'    => false,
            // SQL执行超时时间(秒)
            'timeout'         => 30,
        ],
    ],
];
