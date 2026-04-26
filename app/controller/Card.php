<?php

namespace app\controller;

use app\BaseController;
use app\model\CardModel;

class Card extends BaseController
{
    // 免费卡片组件列表（保留的组件）
    private $freeCards = [
        'todo',       // 记事本
        'topSearch',  // 热搜
        'weather',    // 天气日历
    ];

    function index(): \think\response\Json
    {
        // 只返回免费的卡片组件
        $apps = CardModel::where('status', 1)
            ->whereIn('name_en', $this->freeCards)
            ->select();
        return $this->success('ok', $apps);
    }

    function install_num(): \think\response\Json
    {
        $id = $this->request->post('id', 0);
        if ($id) {
            $find = CardModel::where("id", $id)->find();
            if ($find) {
                $find->install_num += 1;
                $find->save();
            }
        }
        return $this->success('ok');
    }
}