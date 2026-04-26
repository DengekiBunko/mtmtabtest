<?php
namespace app\model;

use app\extend\SnowFlake;
use think\Model;

class UserModel extends Model
{
    protected $name = "user";
    protected $pk = "id";
    
    /**
     * 获取雪花ID
     * @return int
     */
    public static function getSnowflakeId(): int
    {
        return SnowFlake::getInstance(17)->nextId();
    }
    
    // 设置字段信息
    protected static $user_temp = null;

    protected function getManagerAttr($value): int
    {
        return (int) $value;
    }
    protected function getIdAttr($value): int
    {
        return (int) $value;
    }
    protected function getStatusAttr($value): int
    {
        return (int) $value;
    }

    public static function getUser(bool $must = false)
    {
        $id = request()->header('Userid', '');
        $token = request()->header('Token', '');
        if (!$id) {
            $id = request()->cookie('user_id', '');
        }
        if (!$token) {
            $token = request()->cookie('token', '');
        }
        if ($id && $token) {
            if (self::$user_temp) return self::$user_temp;
            $user = TokenModel::where('user_id', $id)->where('token', $token)->field('user_id,token,create_time')->find();
            if ($user) {
                $status = self::where('id', $user['user_id'])->find();
                if ($status && $status['status'] === 0) {
                    $user['group_id'] = $status['group_id'];
                    if (time() > ($user['create_time'] + 60 * 60 * 24 * 15)) {
                        $user->delete();
                    } else {
                        if ((time() - $user['create_time']) > (864000)) {
                            $user->create_time = time();
                            $user->save();
                        }
                        self::$user_temp = $user;
                        return $user;
                    }
                }
            }
        }
        if ($must) {
            json(['code' => 0, 'msg' => '请登录后操作'])->send();
            exit();
        }
        return false;
    }
}
