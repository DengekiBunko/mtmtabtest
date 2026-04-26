<?php

namespace app\model;

use app\extend\SnowFlake;
use think\Model;

class LinkStoreModel extends Model
{
    protected $name = "linkstore";
    protected $pk = "id";
    protected $autoWriteTimestamp = "datetime";
    protected $createTime = "create_time";
    
    /**
     * 获取雪花ID
     * @return int
     */
    public static function getSnowflakeId(): int
    {
        return SnowFlake::getInstance(8)->nextId();
    }
    
    /**
     * 创建链接记录
     * 使用雪花ID替代自增ID
     */
    public static function createLinkStore($data)
    {
        if (!isset($data['id'])) {
            $data['id'] = self::getSnowflakeId();
        }
        return self::create($data);
    }
    
    /**
     * 增加安装数量
     */
    public static function incrementInstall($id)
    {
        return self::where('id', $id)->inc('install_num')->update();
    }
}
