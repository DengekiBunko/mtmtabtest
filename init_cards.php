<?php
/**
 * 卡片配置初始化脚本
 * 确保启用正确的卡片组件：
 * - 保留：记事本、热搜、天气日历
 * - 移除：诗词、木鱼、付费模块
 */

require __DIR__ . '/vendor/autoload.php';

use think\facade\Db;

echo "开始初始化卡片配置...\n";

try {
    // 定义需要启用的卡片
    $enableCards = [
        [
            'name' => '记事本',
            'name_en' => 'note',
            'status' => 1,
            'version' => 1,
            'tips' => '快捷记录灵感和待办事项',
            'src' => '/static/note.png',
            'url' => '/plugins/note/card',
            'window' => '/noteApp'
        ],
        [
            'name' => '热搜',
            'name_en' => 'topSearch',
            'status' => 1,
            'version' => 1,
            'tips' => '聚合百度、B站、微博、知乎等热搜',
            'src' => '/static/app/topSearch/ico.png',
            'url' => '/plugins/topSearch/card',
            'window' => null
        ],
        [
            'name' => '天气日历',
            'name_en' => 'weather',
            'status' => 1,
            'version' => 1,
            'tips' => '实时天气信息和日历功能',
            'src' => '/static/app/weather/ico.png',
            'url' => '/plugins/weather/card',
            'window' => null
        ]
    ];
    
    // 定义需要禁用的卡片（付费模块、诗词、木鱼等）
    $disableCards = [
        'poetry',      // 诗词
        'muyu',        // 木鱼
        'food',        // 美食（付费）
        'ai',          // AI（付费）
        'commemorate', // 纪念日（付费）
        'countdown',   // 倒计时（付费）
        'calendar',    // 日历（付费）
        'imageConversion',    // 图片转换（付费）
        'amountConversion'    // 金额转换（付费）
    ];
    
    $enabledCount = 0;
    $disabledCount = 0;
    
    // 启用必要的卡片
    foreach ($enableCards as $card) {
        $exists = Db::name('card')->where('name_en', $card['name_en'])->find();
        
        if ($exists) {
            // 更新现有卡片
            Db::name('card')
                ->where('name_en', $card['name_en'])
                ->update([
                    'status' => 1,
                    'update_time' => date('Y-m-d H:i:s')
                ]);
            echo "更新卡片: {$card['name']} (已启用)\n";
        } else {
            // 插入新卡片
            $card['id'] = generateSnowflakeId();
            $card['create_time'] = date('Y-m-d H:i:s');
            $card['update_time'] = date('Y-m-d H:i:s');
            $card['install_num'] = 0;
            
            Db::name('card')->insert($card);
            echo "创建卡片: {$card['name']} (已启用)\n";
        }
        $enabledCount++;
    }
    
    // 禁用不需要的卡片
    foreach ($disableCards as $nameEn) {
        $result = Db::name('card')
            ->where('name_en', $nameEn)
            ->update([
                'status' => 0,
                'update_time' => date('Y-m-d H:i:s')
            ]);
        
        if ($result > 0) {
            echo "禁用卡片: {$nameEn}\n";
            $disabledCount++;
        }
    }
    
    echo "\n卡片配置初始化完成！\n";
    echo "启用卡片: {$enabledCount} 个\n";
    echo "禁用卡片: {$disabledCount} 个\n";
    
} catch (\Exception $e) {
    echo "初始化失败: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * 生成雪花ID（简化版）
 */
function generateSnowflakeId() {
    $workerId = 1;
    $sequence = mt_rand(0, 4095);
    $time = (int)(microtime(true) * 1000) - 1577836800000; // 2020-01-01
    return ($time << 22) | ($workerId << 17) | ($sequence);
}
