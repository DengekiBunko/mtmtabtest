<?php

namespace app\model;

use app\extend\SnowFlake;
use think\Model;

class TabbarModel extends Model
{
    protected $name = "tabbar";
    protected $pk = "id";
    protected $autoWriteTimestamp = "datetime";
    protected $updateTime = "update_time";
    
    /**
     * 获取雪花ID
     * @return int
     */
    public static function getSnowflakeId(): int
    {
        return SnowFlake::getInstance(14)->nextId();
    }
    
    /**
     * 获取或创建用户的页脚配置
     */
    public static function getOrCreate($userId)
    {
        $tabbar = self::where('user_id', $userId)->find();
        if (!$tabbar) {
            $tabbar = self::create([
                'id' => self::getSnowflakeId(),
                'user_id' => $userId,
            ]);
        }
        return $tabbar;
    }
}
