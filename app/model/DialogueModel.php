<?php

namespace app\model;

use app\extend\SnowFlake;
use think\Model;

class DialogueModel extends Model
{
    protected $name = "dialogue";
    protected $pk = "id";
    protected $autoWriteTimestamp = "datetime";
    protected $createTime = "create_time";
    
    /**
     * 创建对话记录
     * 使用雪花ID替代自增ID
     */
    public static function createDialogue($userId, $title = '', $modeId = 0)
    {
        $snowflake = SnowFlake::getInstance(3);
        return self::create([
            'id' => $snowflake->nextId(),
            'user_id' => $userId,
            'title' => $title,
            'mode_id' => $modeId,
            'create_time' => date('Y-m-d H:i:s'),
        ]);
    }
}
