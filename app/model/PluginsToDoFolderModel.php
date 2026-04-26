<?php

namespace app\model;

use app\extend\SnowFlake;
use think\Model;

class PluginsToDoFolderModel extends Model
{
    protected $name = "plugins_todo_folder";
    protected $pk = "id";
    protected $autoWriteTimestamp = "datetime";
    protected $createTime = "create_time";
    
    /**
     * 获取雪花ID
     * @return int
     */
    public static function getSnowflakeId(): int
    {
        return SnowFlake::getInstance(10)->nextId();
    }
}
