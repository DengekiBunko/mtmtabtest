<?php

namespace app\model;

use app\extend\SnowFlake;
use think\Model;

class SettingModel extends Model
{
    protected $name = "setting";
    protected $pk = "id";
    
    /**
     * 获取雪花ID
     * @return int
     */
    public static function getSnowflakeId(): int
    {
        return SnowFlake::getInstance(13)->nextId();
    }
}
