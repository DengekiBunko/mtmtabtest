<?php

namespace app\model;

use app\extend\SnowFlake;
use think\Model;

class CardModel extends Model
{
    protected $name = "card";
    protected $pk = "id";
    protected $autoWriteTimestamp = "datetime";
    protected $createTime = "create_time";
    protected $updateTime = "update_time";
    
    /**
     * 获取雪花ID
     * @return int
     */
    public static function getSnowflakeId(): int
    {
        return SnowFlake::getInstance(1)->nextId();
    }
    
    /**
     * 创建卡片记录
     * 使用雪花ID替代自增ID
     */
    public static function createCard($data)
    {
        if (!isset($data['id'])) {
            $data['id'] = self::getSnowflakeId();
        }
        return self::create($data);
    }
}
