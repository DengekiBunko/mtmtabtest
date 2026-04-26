<?php

namespace app\controller\apps\weather;

use app\model\CardModel;
use app\PluginsBase;

class Index extends PluginsBase
{
    public $gateway = '';

    function _initialize()
    {
        parent::_initialize();
        $this->gateway = CardModel::config("weather", "gateway", "https://devapi.qweather.com");
        //如果不是https://开头就重新修改为https开头
        if (!preg_match('/^https?:\/\//', $this->gateway)) {
            $this->gateway = 'https://' . $this->gateway;
        }
    }

    function calendar(): \think\response\Json
    {
        // 获取当前日期信息
        $today = date('Y-m-d');
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        $weekday = date('w');
        $lunar = $this->getLunarDate($year, $month, $day);
        
        $calendarInfo = [
            'date' => $today,
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'weekday' => $weekday,
            'weekday_name' => ['周日', '周一', '周二', '周三', '周四', '周五', '周六'][$weekday],
            'lunar' => $lunar,
            'solar_terms' => $this->getSolarTerms($month, $day),
            'holidays' => $this->getHolidays($today),
            'is_weekend' => in_array($weekday, [0, 6])
        ];
        
        return $this->success('ok', $calendarInfo);
    }

    private function getLunarDate($year, $month, $day)
    {
        // 简化的农历计算，实际项目中应使用专门的农历库
        $lunarMonths = ['正月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '冬月', '腊月'];
        $lunarDays = ['初一', '初二', '初三', '初四', '初五', '初六', '初七', '初八', '初九', '初十', 
                     '十一', '十二', '十三', '十四', '十五', '十六', '十七', '十八', '十九', '二十',
                     '廿一', '廿二', '廿三', '廿四', '廿五', '廿六', '廿七', '廿八', '廿九', '三十'];
        
        // 这里使用简化算法，实际应该使用准确的农历转换
        $lunarMonth = $lunarMonths[($month - 1) % 12];
        $lunarDay = $lunarDays[($day - 1) % 30];
        
        return [
            'month' => $lunarMonth,
            'day' => $lunarDay,
            'full_date' => $lunarMonth . $lunarDay
        ];
    }

    private function getSolarTerms($month, $day)
    {
        $solarTerms = [
            1 => ['小寒', '大寒'],
            2 => ['立春', '雨水'],
            3 => ['惊蛰', '春分'],
            4 => ['清明', '谷雨'],
            5 => ['立夏', '小满'],
            6 => ['芒种', '夏至'],
            7 => ['小暑', '大暑'],
            8 => ['立秋', '处暑'],
            9 => ['白露', '秋分'],
            10 => ['寒露', '霜降'],
            11 => ['立冬', '小雪'],
            12 => ['大雪', '冬至']
        ];
        
        if (isset($solarTerms[$month])) {
            $terms = $solarTerms[$month];
            return $terms[$day <= 15 ? 0 : 1] ?? '';
        }
        
        return '';
    }

    private function getHolidays($date)
    {
        // 简化的节假日判断
        $holidays = [
            '01-01' => '元旦',
            '02-14' => '情人节',
            '03-08' => '妇女节',
            '04-01' => '愚人节',
            '05-01' => '劳动节',
            '06-01' => '儿童节',
            '10-01' => '国庆节',
            '12-25' => '圣诞节'
        ];
        
        $monthDay = date('m-d', strtotime($date));
        return $holidays[$monthDay] ?? '';
    }

    function ip(): \think\response\Json
    {
        $ip = getRealIp();
        $ipInfo = [
            'ipAddress' => $ip,
            'latitude' => 39.91,
            'longitude' => 116.41,
            'cityName' => "北京",
            'regionName' => "北京",
            'countryName' => "中国"
        ];
        return $this->success('ok', $ipInfo);
    }

    function setting()
    {
        $this->getAdmin();
        if ($this->request->isPost()) {
            $form = $this->request->post();
            CardModel::saveConfigs("weather", $form);
            return $this->success("保存成功");
        }
        if ($this->request->isPut()) {
            $form = CardModel::configs('weather');
            return $this->success('ok', $form);
        }

        return $this->fetch("setting.html");
    }

    function everyDay(): \think\response\Json
    {

        $apiKey = CardModel::config('weather', 'key');
        $location = $this->request->get("location", "101010100");
        try {
            $result = \Axios::http()->get($this->gateway . '/v7/weather/7d', [
                'query' => [
                    'location' => $location,
                ],
                "headers" => [
                    "X-QW-Api-Key" => $apiKey
                ]
            ]);
            if ($result->getStatusCode() === 200) {
                $json = \Axios::toJson($result->getBody()->getContents());
                if ($json && $json['code'] == "200") {
                    return $this->success($json['daily']);
                }
            }
        } catch (\Exception $e) {
        }
        return $this->error("数据获取错误");
    }

    function now(): \think\response\Json
    {

        $apiKey = CardModel::config('weather', 'key');
        $location = $this->request->get('location', '101010100');
        try {
            $result = \Axios::http()->get($this->gateway . '/v7/weather/now', [
                'query' => [
                    'location' => $location,
                ],
                "headers" => [
                    "X-QW-Api-Key" => $apiKey
                ]
            ]);
            if ($result->getStatusCode() === 200) {
                $json = \Axios::toJson($result->getBody()->getContents());
                if ($json && $json['code'] == '200') {
                    return $this->success($json['now']);
                }
            }
        } catch (\Exception $e) {

        }
        return $this->error('数据获取错误');
    }

    function locationToCity(): \think\response\Json
    {

        $location = $this->request->all('location', '101010100');
        $apiKey = CardModel::config('weather', 'key');
        try {
            $result = \Axios::http()->get("{$this->gateway}/geo/v2/city/lookup", [
                'query' => [
                    'location' => $location,
                ],
                "headers" => [
                    "X-QW-Api-Key" => $apiKey
                ]
            ]);
            if ($result->getStatusCode() === 200) {
                $json = \Axios::toJson($result->getBody()->getContents());
                if ($json && $json['code'] == '200') {
                    if (count($json['location']) > 0) {
                        return $this->success($json['location'][0]);
                    }
                }
            }
            if ($result->getStatusCode() === 401 || $result->getStatusCode() === 403) {
                return $this->error("获取失败，请检查API或者API KEY是否正确");
            }
        } catch (\Exception $e) {
        }
        return $this->error('数据获取错误');
    }

    function citySearch(): \think\response\Json
    {
        $city = $this->request->post("city", "");
        $apiKey = CardModel::config('weather', 'key');
        if (trim($city)) {
            try {
                $result = \Axios::http()->get("{$this->gateway}/geo/v2/city/lookup", [
                    'query' => [
                        'location' => $city,
                        'key' => $apiKey,
                    ],
                    "headers" => [
                        "X-QW-Api-Key" => $apiKey
                    ]
                ]);
                if ($result->getStatusCode() === 200) {
                    $json = \Axios::toJson($result->getBody()->getContents());
                    if ($json && $json['code'] == '200') {
                        if (count($json['location']) > 0) {
                            return $this->success($json['location']);
                        }
                    }
                }
                if ($result->getStatusCode() === 401 || $result->getStatusCode() === 403) {
                    return $this->error("获取失败，请检查API或者API KEY是否正确");
                }
            } catch (\Exception $e) {
            }
        }
        return $this->error('数据获取错误');
    }

    function ipV2(): \think\response\Json
    {
        $ip = getRealIp();
        $result = \Axios::http()->get("https://auth.mtab.cc/weather/ipLocation?ip={$ip}");
        if ($result->getStatusCode() === 200) {
            $json = \Axios::toJson($result->getBody()->getContents());
            if ($json && $json['code'] == 1) {
                return $this->success($json['data']);
            }
        }
        return $this->error('数据获取错误');
    }

    function citySearchV2(): \think\response\Json
    {
        $city = $this->request->post("city", "");
        $result = \Axios::http()->get("https://auth.mtab.cc/weather/citySearch?q={$city}");
        if ($result->getStatusCode() === 200) {
            $json = \Axios::toJson($result->getBody()->getContents());
            if ($json && $json['code'] == 1) {
                return $this->success($json['data']);
            }
        }
        return $this->error('数据获取错误');
    }

    function nowV2(): \think\response\Json
    {
        $cityId = $this->request->get("cityId", "");
        $result = \Axios::http()->get("https://auth.mtab.cc/weather/cityWeather?cityCode={$cityId}");
        if ($result->getStatusCode() === 200) {
            $json = \Axios::toJson($result->getBody()->getContents());
            if ($json) {
                return $this->success($json);
            }
        }
        return $this->error('数据获取错误');
    }
}