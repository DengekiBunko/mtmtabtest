<?php

namespace app\model;

use app\extend\SnowFlake;
use think\Model;

class SettingModel extends Model
{
    protected $name = "setting";
    protected $pk = "id";
    
    /**
     * 获取雪花ID
     * @return int
     */
    public static function getSnowflakeId(): int
    {
        return SnowFlake::getInstance(13)->nextId();
    }

    public static function systemSetting($key = false, $def = false, $emptyReplace = false)
    {
        if ($key === false) {
            $list = self::select();
            $config = [];
            foreach ($list as $item) {
                $config[$item['keys']] = $item['value'];
            }
            return $config;
        }
        $info = self::where('keys', $key)->find();
        if ($info) {
            if ($emptyReplace && empty($info['value'])) {
                return $def;
            }
            return $info['value'];
        }
        return $def;
    }

    public static function Config($key = false, $def = "")
    {
        return self::systemSetting($key, $def);
    }

    public static function siteConfig()
    {
        $keys = ['title', 'keywords', 'description', 'logo', 'favicon', 'footer', 'customHead', 'pwa', 'register', 'login', 'upload_size', 'upload_ext', 'version'];
        $config = [];
        foreach ($keys as $key) {
            $config[$key] = self::Config($key);
        }
        return $config;
    }
}
