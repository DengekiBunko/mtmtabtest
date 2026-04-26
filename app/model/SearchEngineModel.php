<?php

namespace app\model;

use app\extend\SnowFlake;
use think\Model;

class SearchEngineModel extends Model
{
    protected $name = "search_engine";
    protected $pk = "id";
    protected $autoWriteTimestamp = "datetime";
    protected $createTime = "create_time";
    
    /**
     * 获取雪花ID
     * @return int
     */
    public static function getSnowflakeId(): int
    {
        return SnowFlake::getInstance(12)->nextId();
    }
}
