<?php

namespace app\model;

use app\extend\SnowFlake;
use think\Model;

class UserSearchEngineModel extends Model
{
    protected $name = "user_search_engine";
    protected $pk = "id";
    
    /**
     * 获取雪花ID
     * @return int
     */
    public static function getSnowflakeId(): int
    {
        return SnowFlake::getInstance(18)->nextId();
    }
}
