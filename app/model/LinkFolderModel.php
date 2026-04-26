<?php

namespace app\model;

use app\extend\SnowFlake;
use think\Model;

class LinkFolderModel extends Model
{
    protected $name = "link_folder";
    protected $pk = "id";
    protected $autoWriteTimestamp = "datetime";
    protected $createTime = "create_time";
    
    /**
     * 获取雪花ID
     * @return int
     */
    public static function getSnowflakeId(): int
    {
        return SnowFlake::getInstance(6)->nextId();
    }
    
    /**
     * 创建文件夹
     * 使用雪花ID替代自增ID
     */
    public static function createFolder($data)
    {
        if (!isset($data['id'])) {
            $data['id'] = self::getSnowflakeId();
        }
        return self::create($data);
    }
}
