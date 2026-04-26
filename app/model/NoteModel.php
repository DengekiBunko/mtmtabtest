<?php

namespace app\model;

use app\extend\SnowFlake;
use think\Model;

class NoteModel extends Model
{
    protected $name = "note";
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
        return SnowFlake::getInstance(9)->nextId();
    }
    
    /**
     * 创建笔记
     * 使用雪花ID替代自增ID
     */
    public static function createNote($data)
    {
        if (!isset($data['id'])) {
            $data['id'] = self::getSnowflakeId();
        }
        return self::create($data);
    }
}
