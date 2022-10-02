<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: Parse class - 解析类
// +----------------------------------------------------------------------

namespace inis\utils\tool;

/**
 * Class Parse
 * @package inis\utils\tool
 */
class Parse
{
    /**
     * 解析User-Agent
     * @param string|null $ua User-Agent
     * @return array 返回解析后的数组
     */
    public function ua($ua = '')
    {
        if (empty($ua)) return false;

        $result = [];

        // ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ 解析浏览器信息 - 开始 ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓
        $browser_rule = [
            'OmniWeb'  => '/OmniWeb\/(v*)([^\s|;]+)/i',
            'Netscape' => '/Netscape([\d]*)\/([^\s]+)/i',
            'Safari'   => '/safari\/([^\s]+)/i',
            'Internet Explorer' => '/MSIE\s([^\s|;]+)/i',
            'Opera'    => '/Opera[\s|\/]([^\s]+)/i',
            'NetCaptor'=> '/NetCaptor\s([^\s|;]+)/i',
            'Maxthon'  => '/Maxthon/i',
            '360SE'    => '/360SE/i',
            '搜狗'     => '/SE 2.x/i',
            'FireFox'  => '/FireFox\/([^\s]+)/i',
            'Lynx'     => '/Lynx\/([^\s]+)/i',
            'Chrome'   => '/Chrome\/([^\s]+)/i',
            '微信浏览器'=> '/MicroMessenger\/([^\s]+)/i',
            'CriOS'    => '/CriOS\/([^\s]+)/i',
        ];

        $browser_name    = 'Unknown';
        $browser_version = '';
        foreach ($browser_rule as $k => $v) {
            if (preg_match($v, $ua, $regs)) {
                $browser_name    = $k;
                $browser_version = $regs[1];
                break;
            }
        }

        if ($browser_name != 'Unknown') $result['browser'] = ['kernel'=>$browser_name, 'version'=>$browser_version];
        else $result['browser'] = ['kernel'=>'未知','version'=>''];
        // ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ 解析浏览器信息 - 结束 ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑

        // ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ 解析操作系统信息 - 开始 ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ 
        $os_name    = '';
        $os_version = '';
        $equipment  = '';
        if (stripos($ua, 'window')) {

            if (stripos($ua, 'android')) {
                
                preg_match('/android\s([\d\.]+)/i', $ua, $match);
                $os_name   = 'Android';
                $equipment = 'Mobile phone';
                $os_version= (!empty($match[1]) ? $match[1] : null);
                
            } else $os_name = 'Linux';

        }
        else if(preg_match('/iPhone|iPad|iPod/i',$ua)) {

            preg_match('/OS\s([0-9_\.]+)/i', $ua, $match);
            $os_name = 'IOS';
            try {

                $os_version = str_replace('_', '.', $match[1]);
                if (preg_match('/iPhone/i', $ua))    $equipment = 'iPhone';
                else if (preg_match('/iPad/i', $ua)) $equipment = 'iPad';
                else if (preg_match('/iPod/i', $ua)) $equipment = 'iPod';

            } catch (\Throwable $th) {

                $os_version= null;
                $equipment = null;
            }
        }
        else if (stripos($ua, 'mac os')) {

            preg_match('/Mac OS X\s([0-9_\.]+)/i', $ua, $match);
            $os_name    = 'Mac OS X';
            $equipment  = 'PC';
            $os_version = str_replace('_', '.', $match[1]);
        }
        else if (stripos($ua, 'linux')) $os_name = 'Linux';
        else if (stripos($ua, 'unix'))  $os_name = 'Unix';
        else if (stripos($ua, 'bsd'))   $os_name = 'BSD';
        else $os_name = 'Unknown';

        $result['os'] = ['system'=>$os_name, 'version'=>$os_version, 'equipment'=>$equipment];
        // ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ 解析操作系统信息 - 结束 ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑

        // ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ 客户端手机信息 - 开始 ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ 
        $mobile_rule = [
            '苹果' => '/iPhone\s([^\s|;]+)/i',
            '三星' => '/SAMSUNG|Galaxy|GT-|SCH-|SM-\s([^\s|;]+)/i',
            '华为' => '/Huawei|Honor|H60-|H30-\s([^\s|;]+)/i',
            '小米' => '/Mi note|mi one\s([^\s|;]+)/i',
            '红米' => '/HM NOTE|HM201\s([^\s|;]+)/i',
            '酷派' => '/Coolpad|8190Q|5910\s([^\s|;]+)/i',
            '中兴' => '/ZTE|X9180|N9180|U9180\s([^\s|;]+)/i',
            'OPPO' => '/OPPO|X9007|X907|X909|R831S|R827T|R821T|R811|R2017\s([^\s|;]+)/i',
            'HTC' => '/HTC|Desire\s([^\s|;]+)/i',
            '努比亚' => '/Nubia|NX50|NX40\s([^\s|;]+)/i',
            '魅族' => '/M045|M032|M355\s([^\s|;]+)/i',
            '金立' => '/Gionee|GN\s([^\s|;]+)/i',
            '一加' => '/ONEPLUS\s([^\s|;]+)/i',
            'vivo' => '/vivo\s([^\s|;]+)/i',
            '天语' => '/K-Touch\s([^\s|;]+)/i',
            '朵唯' => '/DOOV\s([^\s|;]+)/i',
            '基伍' => '/GFIVE\s([^\s|;]+)/i',
            '诺基亚' => '/Nokia\s([^\s|;]+)/i',
            '其他' => '/Android|Linux|Windows\sNT|Windows\sPhone|Windows\sCE|SymbianOS|BlackBerry|iPhone|iPad|Windows\sPhone|MQQBrowser|UCBrowser|MicroMessenger|UCWEB|Opera|OPR|Firefox|Chrome|Safari|Trident|Edge|compatible|Gecko|KHTML|Wget|curl|Lynx|Java/i',
        ];
        foreach ($mobile_rule as $key => $val) {
            if (preg_match($val, $ua, $regs)) {
                $mobile_name    = $key;
                $mobile_version = $regs[0];
                break;
            } else {
                $mobile_name    = '未知';
                $mobile_version = '未知';
            }
        }
        $result['mobile'] = [
            'brand'    => $mobile_name,
            'version'  => $mobile_version,
        ];
        // ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ 客户端手机信息 - 结束 ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑

        return $result;
    }

    /**
     * 解析URL参数
     * @param string $url
     * @return array 参数数组
     */
    public function url(string $url = null)
    {
        if (is_null($url) or empty($url)) return [];

        if (((new Domain))($url)['is']) $url = ltrim(strstr($url,"?"),'?');
        else if (substr($url, 0, 1) === '?') $url = substr($url,1);
        else $url;
        
        $result = [];
        $params = array_filter(explode('&', $url));
        
        if (!empty($params)) foreach ($params as $key => $val) {
            
            $item = explode('=', $val);
            $result[$item[0]] = $item[1];
        }
        
        return $result;
    }

    /**
     * 将 HTML 解析为省略的文本
     * @param string $html HTML代码
     * @param int $length 截取长度
     * @return string 省略的文本
     */
    public function html($html = '', $length = 100)
    {
        if (empty($html) or $length == 0) return $html;

        $html     = htmlspecialchars_decode($html);
        $content  = str_replace([' '], [''], $html);
        $content  = strip_tags($content);
        $result   = mb_strlen($content, 'utf-8') > $length ? mb_substr($content, 0, $length, 'utf-8') . ' ...' : mb_substr($content, 0, $length, 'utf-8');
        
        return $result;
    }

    // 调用不存在的方法时触发
    public function __call($name, $args)
    {
        // 获取当前 class 存在的方法
        $methods = get_class_methods($this);
        // 过滤掉魔术方法
        $methods = array_filter($methods, function ($item) {
            return !preg_match('/^__/', $item);
        });
        // 获取当前的 class 名称
        $class = get_class($this);
        // 返回异常
        throw new \Exception("当前 {$class} 类没有 {$name} 方法, 存在的方法有: " . implode('、', $methods));
    }
}