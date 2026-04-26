<?php

namespace app\model;

use app\extend\SnowFlake;
use think\Model;

class AiModelModel extends Model
{
    protected $name = "ai_model";
    protected $pk = "id";
    protected $autoWriteTimestamp = "datetime";
    protected $createTime = "create_time";
    
    /**
     * 获取雪花ID
     * @return int
     */
    public static function getSnowflakeId(): int
    {
        return SnowFlake::getInstance(20)->nextId();
    }
}
