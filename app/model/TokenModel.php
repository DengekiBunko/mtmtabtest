<?php

namespace app\model;

use app\extend\SnowFlake;
use think\Model;

class TokenModel extends Model
{
    protected $name = "token";
    protected $pk = "id";
    protected $autoWriteTimestamp = false;
    
    /**
     * 获取雪花ID
     * @return int
     */
    public static function getSnowflakeId(): int
    {
        return SnowFlake::getInstance(15)->nextId();
    }
}
