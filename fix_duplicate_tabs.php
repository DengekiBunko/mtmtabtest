<?php
/**
 * 修复脚本：清理重复的标签数据
 * 用于修复首页添加标签重复显示的问题
 */

require __DIR__ . '/vendor/autoload.php';

use think\facade\Db;
use app\model\TabbarModel;

echo "开始修复标签重复问题...\n";

try {
    // 获取所有用户的 tabbar 数据
    $tabbars = Db::name('tabbar')->select();
    
    $fixedCount = 0;
    
    foreach ($tabbars as $tabbar) {
        if (empty($tabbar['tabs'])) {
            continue;
        }
        
        $tabs = json_decode($tabbar['tabs'], true);
        if (!is_array($tabs)) {
            continue;
        }
        
        // 去重处理
        $seen = [];
        $uniqueTabs = [];
        
        foreach ($tabs as $tab) {
            $tabId = is_array($tab) ? ($tab['id'] ?? uniqid()) : $tab;
            
            if (!isset($seen[$tabId])) {
                $seen[$tabId] = true;
                $uniqueTabs[] = $tab;
            }
        }
        
        // 如果有重复数据，更新数据库
        if (count($uniqueTabs) < count($tabs)) {
            $duplicateCount = count($tabs) - count($uniqueTabs);
            echo "用户 {$tabbar['user_id']}: 发现 {$duplicateCount} 个重复标签，正在修复...\n";
            
            Db::name('tabbar')
                ->where('id', $tabbar['id'])
                ->update([
                    'tabs' => json_encode($uniqueTabs, JSON_UNESCAPED_UNICODE),
                    'update_time' => date('Y-m-d H:i:s')
                ]);
            
            $fixedCount++;
        }
    }
    
    echo "\n修复完成！共修复 {$fixedCount} 个用户的标签数据。\n";
    
} catch (\Exception $e) {
    echo "修复失败: " . $e->getMessage() . "\n";
    exit(1);
}
