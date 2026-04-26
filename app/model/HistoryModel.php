<?php

namespace app\model;

use app\extend\SnowFlake;
use think\Model;

class HistoryModel extends Model
{
    protected $name = "link_history";
    protected $pk = "id";
    protected $autoWriteTimestamp = "datetime";
    protected $createTime = "create_time";
    
    /**
     * 创建历史记录
     * 使用雪花ID替代自增ID
     */
    public static function createHistory($userId, $link)
    {
        $snowflake = SnowFlake::getInstance(5);
        return self::create([
            'id' => $snowflake->nextId(),
            'user_id' => $userId,
            'link' => $link,
            'create_time' => date('Y-m-d H:i:s'),
        ]);
    }
}
