<?php

namespace app\model;

use app\extend\SnowFlake;
use think\Model;

class ConfigModel extends Model
{
    protected $name = "config";
    protected $pk = "id";
    protected $autoWriteTimestamp = "datetime";
    
    /**
     * 获取雪花ID
     * @return int
     */
    public static function getSnowflakeId(): int
    {
        return SnowFlake::getInstance(2)->nextId();
    }
    
    /**
     * 根据user_id获取或创建配置
     */
    public static function getOrCreate($userId)
    {
        $config = self::where('user_id', $userId)->find();
        if (!$config) {
            $config = self::create([
                'id' => self::getSnowflakeId(),
                'user_id' => $userId,
            ]);
        }
        return $config;
    }
}
