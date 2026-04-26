<?php

namespace app\model;

use app\extend\SnowFlake;
use think\Model;

class AiModel extends Model
{
    protected $name = "ai";
    protected $pk = "id";
    protected $autoWriteTimestamp = "datetime";
    protected $createTime = "create_time";
    
    /**
     * 创建AI对话消息
     * 使用雪花ID替代自增ID
     */
    public static function addMessage($userId, $dialogueId, $role, $message, $aiId = '', $reasoningContent = '')
    {
        $snowflake = SnowFlake::getInstance(1);
        $data = [
            'id' => $snowflake->nextId(),
            'user_id' => $userId,
            'dialogue_id' => $dialogueId,
            'role' => $role,
            'message' => $message,
            'ai_id' => $aiId,
            'reasoning_content' => $reasoningContent,
            'create_time' => date('Y-m-d H:i:s'),
        ];
        return self::create($data);
    }
}
