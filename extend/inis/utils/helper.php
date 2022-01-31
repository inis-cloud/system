<?php
// +----------------------------------------------------------------------
// | INIS [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020~2021 http://inis.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: racns <email: racns@qq.com> <url: https://inis.cn>
// +----------------------------------------------------------------------
// | Remarks: helper class - 助手函数
// +----------------------------------------------------------------------

namespace inis\utils;

use inis\utils\File;

/**
 * Class helper
 * @package inis\utils
 */
class helper
{
    /**
     * 秒转人性化时间
     * @param int $second
     * @return string - 整型
     */
    function NaturalSecond(int $second = 0)
    {
        $result = '';
        
        if ($second < 60) {
            $result = floor($second).'秒';
        } elseif ($second >= 60 and $second < 60 * 60) {
            $result = floor($second / 60).'分钟';
        } elseif ($second >= 60 * 60 and $second < 60 * 60 * 24) {
            $result = floor($second / (60 * 60)).'小时';
        } elseif ($second >= 24 * 60 * 60) {
            $result = floor($second / (24 * 60 * 60)).'天';
        } 
        
        return $result;
    }

    /**
     * URL参数转数组
     * @param string $data
     * @return array|string
     */
    function UrlPartsToArr(string $data = 'url or parameter')
    {
        // 验证 $data 是否带域名
        if($this->CheckDomain($data)) $data = ltrim(strstr($data,"?"),'?');
        else $data;
        
        $result = [];
        $parts  = array_filter(explode('&', $data));
        
        if (!empty($parts)) foreach ($parts as $key => $val) {
            
            $item = explode('=', $val);
            
            $result[$item[0]] = $item[1];
        }
        
        if ($data === 'url or parameter') $result = 'Please enter a url or parameter!';
        
        return $result;
    }

    /**
     * 检测域名格式
     * @param string|null $url
     * @return bool
     */
    function CheckDomain(string $url = null)
    {
        $url = str_replace(['https:','http:','//'], '', $url);
        
        $str = "/^(?:[A-za-z0-9-]+\.)+[A-za-z]{2,5}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/";
        
        if (!preg_match($str,$url)) $check = false;
        else $check = true;
        
        return $check;
    }

    /**
     * 获取重定向后的URL
     * @param string|null $url
     * @return RedirectUrl
     */
    function GetRedirectUrl(string $url = null)
    {
        $result = $url;
        $header = get_headers($url,1);
        
        if (strpos($header[0],'301') || strpos($header[0],'302')) {
            
            if(is_array($header['Location'])) $result = $header['Location'][count($header['Location'])-1];
            else $result = $header['Location'];
        }
        
        return $result;
    }

    /**
     * 获取顶级域名
     * @param string|null $url
     * @return TopDomain
     */
    function GetTopDomain(string $url = null)
    {
        $url = $this->ExtractDomain($url);
        
        // 查看是几级域名
        $data = explode('.', $url);
        
        $n = count($data);
        
        // 判断是否是双后缀
        $preg = '/[\w].+\.(com|net|org|gov|edu)\.cn$/';
        
        // 双后缀取后3位
        if(($n > 2) && preg_match($preg,$url)) $url = $data[$n-3].'.'.$data[$n-2].'.'.$data[$n-1];
        // 非双后缀取后两位
        else $url = $data[$n-2].'.'.$data[$n-1];
        
        return $url;
    }
    
    /**
    * 提取域名
    * @param string|null $url
    * @return domain
    */
    function ExtractDomain($url = null)
    {
        $result = $url;
        
        $url = parse_url($url);
        if (!isset($url['host'])) $result = null;
        $main_url = $url['host'];
        
        if(!strcmp(long2ip(sprintf('%u', ip2long($main_url))), $main_url)) $result = $main_url;
        else {
            $array  = explode('.', $main_url);
            $count  = count($array);
            // com.cn net.cn 等情况
            $endArr = ['com', 'net', 'org'];
            if (in_array($array[$count - 2], $endArr)) {
            	$result = $array[$count - 3] . '.' . $array[$count - 2] . '.' . $array[$count - 1];
            } else {
            	$result = $array[$count - 2] . '.' . $array[$count - 1];
            }
        }
        return $result;
    }

    /**
     * 生成随机字符串
     * @param int $length
     * @param String|null $chars
     * @return string
     */
    function VerifyCode(int $length = 6, string $chars = null)
    {
        if (is_null($chars)) $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        
        mt_srand(10000000 * (double)microtime());
        
        for ($i = 0, $str = '', $lc = strlen($chars) - 1; $i < $length; $i++) $str .= $chars[mt_rand(0, $lc)];
        
        return $str;
    }

    /**
     * 获取客户端浏览器信息
     * @param string $agent
     * @return array
     */
    function GetClientBrowser($agent = '')
    {
        $browser     = '';
        $browser_ver = '';
        
        if (preg_match('/OmniWeb\/(v*)([^\s|;]+)/i', $agent, $regs)) {
            $browser = 'OmniWeb';
            $browser_ver = $regs[2];
        }
        if (preg_match('/Netscape([\d]*)\/([^\s]+)/i', $agent, $regs)) {
            $browser = 'Netscape';
            $browser_ver = $regs[2];
        }
        if (preg_match('/safari\/([^\s]+)/i', $agent, $regs)) {
            $browser = 'Safari';
            $browser_ver = $regs[1];
        }
        if (preg_match('/MSIE\s([^\s|;]+)/i', $agent, $regs)) {
            $browser = 'Internet Explorer';
            $browser_ver = $regs[1];
        }
        if (preg_match('/Opera[\s|\/]([^\s]+)/i', $agent, $regs)) {
            $browser = 'Opera';
            $browser_ver = $regs[1];
        }
        if (preg_match('/NetCaptor\s([^\s|;]+)/i', $agent, $regs)) {
            $browser = '(Internet Explorer '.$browser_ver.') NetCaptor';
            $browser_ver = $regs[1];
        }
        if (preg_match('/Maxthon/i', $agent, $regs)) {
            $browser = '(Internet Explorer '.$browser_ver.') Maxthon';
            $browser_ver = '';
        }
        if (preg_match('/360SE/i', $agent, $regs)) {
            $browser = '(Internet Explorer '.$browser_ver.') 360SE';
            $browser_ver = '';
        }
        if (preg_match('/SE 2.x/i', $agent, $regs)) {
            $browser = '(Internet Explorer '.$browser_ver.') 搜狗';
            $browser_ver = '';
        }
        if (preg_match('/FireFox\/([^\s]+)/i', $agent, $regs)) {
            $browser = 'FireFox';
            $browser_ver = $regs[1];
        }
        if (preg_match('/Lynx\/([^\s]+)/i', $agent, $regs)) {
            $browser = 'Lynx';
            $browser_ver = $regs[1];
        }
        if (preg_match('/Chrome\/([^\s]+)/i', $agent, $regs)) {
            $browser = 'Chrome';
            $browser_ver = $regs[1];
        }
        if (preg_match('/MicroMessenger\/([^\s]+)/i', $agent, $regs)) {
            $browser = '微信浏览器';
            $browser_ver = $regs[1];
        }

        if ($browser != '') $res = ['kernel'=>$browser, 'version'=>$browser_ver];
        else $res = ['kernel'=>'未知','version'=> ''];

        return $res;
    }

    /**
     * 获取客户端操作系统信息
     * @param string $agent
     * @return array
     */
    function GetClientOS($agent = '')
    {
        $os         = '';
        $os_ver     = '';
        $equipment  = '';
        
        // window系统
        if (stripos($agent, 'window'))
        {
            $os         = 'Windows';
            $equipment  = 'PC';
            
            if (preg_match('/nt 6.0/i', $agent)) $os_ver = 'Vista';
            elseif(preg_match('/nt 10.0/i', $agent)) $os_ver = '10';
            elseif(preg_match('/nt 6.3/i', $agent)) $os_ver = '8.1';
            elseif(preg_match('/nt 6.2/i', $agent)) $os_ver = '8.0';
            elseif(preg_match('/nt 6.1/i', $agent)) $os_ver = '7';
            elseif(preg_match('/nt 5.1/i', $agent)) $os_ver = 'XP';
            elseif(preg_match('/nt 5/i', $agent)) $os_ver = '2000';
            elseif(preg_match('/nt 98/i', $agent)) $os_ver = '98';
            elseif(preg_match('/nt/i', $agent)) $os_ver = 'nt';
            else $os_ver = '';
            
            if (preg_match('/x64/i', $agent)) $os .= '(x64)';
            elseif(preg_match('/x32/i', $agent)) $os .= '(x32)';
            
        }
        elseif(stripos($agent, 'linux'))
        {
            if (stripos($agent, 'android')) {
                
                preg_match('/android\s([\d\.]+)/i', $agent, $match);
                $os = 'Android';
                $equipment = 'Mobile phone';
                $os_ver = (!empty($match[1]) ? $match[1] : null);
                
            }else $os = 'Linux';
        }
        elseif(stripos($agent, 'unix')) {
            $os = 'Unix';
        }
        elseif(preg_match('/iPhone|iPad|iPod/i',$agent))
        {
            preg_match('/OS\s([0-9_\.]+)/i', $agent, $match);
            $os = 'IOS';
            $os_ver = str_replace('_','.',$match[1]);
            if(preg_match('/iPhone/i',$agent)) $equipment = 'iPhone';
            elseif(preg_match('/iPad/i',$agent)) $equipment = 'iPad';
            elseif(preg_match('/iPod/i',$agent)) $equipment = 'iPod';

        }
        elseif(stripos($agent, 'mac os'))
        {
            preg_match('/Mac OS X\s([0-9_\.]+)/i', $agent, $match);
            $os = 'Mac OS X';
            $equipment = 'PC';
            $os_ver = str_replace('_','.',$match[1]);
        }
        elseif(preg_match('/PostmanRuntime\/([^\s]+)/i', $agent, $regs))
        {
            $os = 'Postman';
            $equipment = 'PC';
            $os_ver = $regs[1];
        }else $os = 'Other';

        return ['system'=>$os, 'version'=>$os_ver, 'equipment'=>$equipment];
    }

    /**
     * 获取客户端手机信息
     * @param string $agent
     * @return array
     */
    function GetClientMobile($agent = '')
    {
        $mobile_brand = '';
        $mobile_ver   = '';
        
        if(preg_match('/iPhone\s([^\s|;]+)/i', $agent, $regs)) {
            $mobile_brand = '苹果';
            $mobile_ver = $regs[0];
        }elseif(preg_match('/SAMSUNG|Galaxy|GT-|SCH-|SM-\s([^\s|;]+)/i', $agent, $regs)) {
            $mobile_brand = '三星';
            $mobile_ver = $regs[0];
        }elseif(preg_match('/Huawei|Honor|H60-|H30-\s([^\s|;]+)/i', $agent, $regs)) {
            $mobile_brand = '华为';
            $mobile_ver = $regs[0];
        }elseif(preg_match('/Mi note|mi one\s([^\s|;]+)/i', $agent, $regs)) {
            $mobile_brand = '小米';
            $mobile_ver = $regs[0];
        }elseif(preg_match('/HM NOTE|HM201\s([^\s|;]+)/i', $agent, $regs)) {
            $mobile_brand = '红米';
            $mobile_ver = $regs[0];
        }elseif(preg_match('/Coolpad|8190Q|5910\s([^\s|;]+)/i', $agent, $regs)) {
            $mobile_brand = '酷派';
            $mobile_ver = $regs[0];
        }elseif(preg_match('/ZTE|X9180|N9180|U9180\s([^\s|;]+)/i', $agent, $regs)) {
            $mobile_brand = '中兴';
            $mobile_ver = $regs[0];
        }elseif(preg_match('/OPPO|X9007|X907|X909|R831S|R827T|R821T|R811|R2017\s([^\s|;]+)/i', $agent, $regs)) {
            $mobile_brand = 'OPPO';
            $mobile_ver = $regs[0];
        }elseif(preg_match('/HTC|Desire\s([^\s|;]+)/i', $agent, $regs)) {
            $mobile_brand = 'HTC';
            $mobile_ver = $regs[0];
        }elseif(preg_match('/Nubia|NX50|NX40\s([^\s|;]+)/i', $agent, $regs)) {
            $mobile_brand = '努比亚';
            $mobile_ver = $regs[0];
        }elseif(preg_match('/M045|M032|M355\s([^\s|;]+)/i', $agent, $regs)) {
            $mobile_brand = '魅族';
            $mobile_ver = $regs[0];
        }elseif(preg_match('/Gionee|GN\s([^\s|;]+)/i', $agent, $regs)) {
            $mobile_brand = '金立';
            $mobile_ver = $regs[0];
        }elseif(preg_match('/HS-U|HS-E\s([^\s|;]+)/i', $agent, $regs)) {
            $mobile_brand = '海信';
            $mobile_ver = $regs[0];
        }elseif(preg_match('/Lenove\s([^\s|;]+)/i', $agent, $regs)) {
            $mobile_brand = '联想';
            $mobile_ver = $regs[0];
        }elseif(preg_match('/ONEPLUS\s([^\s|;]+)/i', $agent, $regs)) {
            $mobile_brand = '一加';
            $mobile_ver = $regs[0];
        }elseif(preg_match('/vivo\s([^\s|;]+)/i', $agent, $regs)) {
            $mobile_brand = 'vivo';
            $mobile_ver = $regs[0];
        }elseif(preg_match('/K-Touch\s([^\s|;]+)/i', $agent, $regs)) {
            $mobile_brand = '天语';
            $mobile_ver = $regs[0];
        }elseif(preg_match('/DOOV\s([^\s|;]+)/i', $agent, $regs)) {
            $mobile_brand = '朵唯';
            $mobile_ver = $regs[0];
        }elseif(preg_match('/GFIVE\s([^\s|;]+)/i', $agent, $regs)) {
            $mobile_brand = '基伍';
            $mobile_ver = $regs[0];
        }elseif(preg_match('/Nokia\s([^\s|;]+)/i', $agent, $regs)) {
            $mobile_brand = '诺基亚';
            $mobile_ver = $regs[0];
        }else $mobile_brand = '其他';
        
        return ['brand'=>$mobile_brand, 'version'=>$mobile_ver];
    }

    /**
     * 获取客户端IP
     * @return bool|mixed
     */
    function GetClientIP()
    {
        $ip = FALSE;
        // 客户端IP 或 NONE
        if(!empty($_SERVER["HTTP_CLIENT_IP"])) $ip = $_SERVER["HTTP_CLIENT_IP"];
        
        // 多重代理服务器下的客户端真实IP地址（可能伪造）,如果没有使用代理，此字段为空
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            
            $ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
            
            if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }
            
            for ($i = 0; $i < count($ips); $i++) {
                try {
                    if (!preg_match("^(10│172.16│192.168).", $ips[$i])) {
                        $ip = $ips[$i];
                        break;
                    }
                } catch (ValidateException $e) {
                    $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
                } catch (\Exception $e) {
                    $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
                }
            }
        }
        
        // 客户端IP 或 (最后一个)代理服务器 IP
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }
    
    /**
     * 二维数组冒泡排序
     * @return bool|mixed
     */
    function BubbSort($array, string $key, $sort = 'acs')
    {
        if ($sort == 'acs') {   // 升序
            for ($i=0; $i<count($array); $i++) for ($j=$i; $j<count($array); $j++) if ($array[$i][$key]>$array[$j][$key]) {
                $temp       = $array[$i];
                $array[$i]  = $array[$j];
                $array[$j]  = $temp;
            }
        } elseif ($sort == 'desc') {    // 降序
            for ($i=0; $i<count($array); $i++) for ($j=$i; $j<count($array); $j++) if ($array[$i][$key]<$array[$j][$key]) {
                $temp       = $array[$i];
                $array[$i]  = $array[$j];
                $array[$j]  = $temp;
            }
        }
        
        return $array;
    }
    
    // 获取本地域名
    function domain()
    {
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        $domain = $_SERVER['HTTP_HOST'];
        
        return $http_type.$domain;
    }
    
    // 数组随机
    function RandArray($array = ['light','danger','dark','primary','success','info','warning'])
    {
        $rand   = array_rand($array);
        $result = $array[$rand];
        
        return $result;
    }
    
    // 随机图
    function RandomImg($type = "link", $file_path = "storage/random-img/img.txt")
    {
        $result = null;
        
        if ($type == "link") {
            
            if (!file_exists($file_path)) $result = "文件不存在";
            
            // 从文本获取链接
            $pics = [];
            
            $fs = fopen($file_path, "r");
            
            while (!feof($fs)) {
            
            	$line=trim(fgets($fs));
            	
            	if(!empty($line)) array_push($pics, $line);
            }
            
            // 从数组随机获取链接
            $result = $this->RandArray($pics);
            
        } else if ($type == "local") {
            
            // 得到所有的文件
            $files = scandir($file_path);
            
            // 符合要求的后缀
            $allow = ['jpg','jpeg','png','gif','webp'];
            
            foreach ($files as $key => $val) {
                $item = explode(".", $val);
                if (in_array(array_pop($item), $allow)) {
                    $result[] = $this->domain()."/".$file_path.$val;
                }
            }
            
            $result = $this->RandArray($result);
        }
        
        return $result;
    }
    
    // 版本号比对
    function VersionCompare($versionA, $versionB, $parting = '.')
    {
        
        if ((int)$versionA > 2147483646 || (int)$versionB > 2147483646) {
            throw new Exception('版本号,位数太大暂不支持!','101');
            // return '版本号,位数太大暂不支持!';
        }
        
        $verListA = explode($parting, (string)$versionA);
        $verListB = explode($parting, (string)$versionB);
        
        $len = max(count($verListA),count($verListB));
        $i   = -1;
        
        while ($i++ < $len) {
            
            $verListA[$i] = intval(@$verListA[$i]);
            if ($verListA[$i] < 0 ) $verListA[$i] = 0;
            
            $verListB[$i] = intval(@$verListB[$i]);
            if ($verListB[$i] < 0 ) $verListB[$i] = 0;
            
            if ($verListA[$i] > $verListB[$i]) return 1;
            else if ($verListA[$i] < $verListB[$i]) return -1;
            else if ($i==($len-1)) return 0;
        }
    }
    
    // 提取富文本字符串的纯文本并进行截取
    function StringToText($string, $num = 100)
    {
        $result = $string;
        
        if ($string) {
            
            $html_string = htmlspecialchars_decode($string);
            $content     = str_replace([" "], [""], $html_string);
            $contents    = strip_tags($content);
            $result      = mb_strlen($contents,'utf-8') > $num ? mb_substr($contents, 0, $num, "utf-8").'....' : mb_substr($contents, 0, $num, "utf-8");
        }
        
        return $result;
    }
    
    // 是否日期格式
    function IsDate($string)
    {
        $result    = false;
        $strtotime = strtotime($string);
        if (is_numeric($strtotime) and strtotime(date('Y-m-d H:i:s', $strtotime)) == $strtotime) $result = true;
        return $result;
    }
    
    // 时间戳格式求相差天数
    function DiffTime($strtotime1,$strtotime2)
    {
    	if($strtotime1 < $strtotime2) {
    		$starttime = $strtotime1;
    		$endtime   = $strtotime2;
    	} else {
    		$starttime = $strtotime2;
    		$endtime   = $strtotime1;
    	}
    	
    	// 计算天数
    	$timediff = $endtime - $starttime;
    	$days     = intval($timediff / 86400);
    	// 计算小时数
    	$remain   = $timediff % 86400;
    	$hours    = intval($remain / 3600);
    	// 计算分钟数
    	$remain   = $remain % 3600;
    	$mins     = intval($remain / 60);
    	// 计算秒数
    	$secs     = $remain % 60;
    	$result   = ["day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs];
    	return $result;
    }
    
    // 日期格式求相差天数
    function DiffDate($date1, $date2)
    {
        $datetime1 = new \DateTime($date1);
        $datetime2 = new \DateTime($date2);
        $interval  = $datetime1->diff($datetime2);
        
        $result['year']  = (int)$interval->format('%Y');
        $result['month'] = (int)$interval->format('%m');
        $result['day']   = (int)$interval->format('%d');
        $result['hour']  = (int)$interval->format('%H');
        $result['min']   = (int)$interval->format('%i');
        $result['sec']   = (int)$interval->format('%s');
        $result['days']  = (int)$interval->format('%a');
        
        return $result;
    }
    
    // 是否在二维数组内
    public function InArray($search, array $array)
    {
        $result = false;
        
        if (is_array($search)) foreach ($array as $val) {
            foreach ($val as $k => $v) if ($k == $search[0] and $v == $search[1]) $result = true;
        }
        
        return $result;
    }
    
    // GET请求
    public function get(string $url, array $params = [], array $headers = [])
    {
        $header  = ['Content-type'=>'application/json;','Accept'=>'application/json','origin'=>str_replace(['https','http',':','//'], '', $this->domain())];
        $params  = !empty($params)  ? http_build_query($params) : json_encode($params);
        $headers = !empty($headers) ? array_merge($header, $headers) : $header;
        
        foreach ($headers as $key => $val) $_headers[] = $key.':'.$val;
        
        $curl   = curl_init();
        
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $_headers);
        curl_setopt($curl, CURLOPT_URL, $url . '?' . $params);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        
        $result = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($result,true);
        
        return $result;
    }
    
    // POST请求
    public function post(string $url, array $params = [], array $headers = [])
    {
        $header  = ['Content-type'=>'application/json;charset="utf-8"','Accept'=>'application/json','origin'=>str_replace(['https','http',':','//'], '', $this->domain())];
        $params  = json_encode($params);
        $headers = !empty($headers) ? array_merge($header, $headers) : $header;
        
        foreach ($headers as $key => $val) $_headers[] = $key.':'.$val;
        
        $curl   = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $_headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        
        $result = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($result,true);
        
        return $result;
    }
    
    // PUT请求
    public function put(string $url, array $params = [], array $headers = [])
    {
        $header  = ['Content-type'=>'application/json;','Accept'=>'application/json','origin'=>str_replace(['https','http',':','//'], '', $this->domain())];
        $params  = json_encode($params);
        $headers = !empty($headers) ? array_merge($header, $headers) : $header;
        
        foreach ($headers as $key => $val) $_headers[] = $key.':'.$val;
        
        $curl   = curl_init();
        
        // 设置请求的URL
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $_headers);
        // 设为TRUE把curl_exec()结果转化为字串，而不是直接输出 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // 设置请求方式
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        // 设置提交的字符串
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        
        $result = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($result,true);
        
        return $result;
    }
    
    // DELETE请求
    public function del(string $url, array $params = [], array $headers = [])
    {
        $header  = ['Content-type'=>'application/json;','Accept'=>'application/json','origin'=>str_replace(['https','http',':','//'], '', $this->domain())];
        $params  = json_encode($params);
        $headers = !empty($headers) ? array_merge($header, $headers) : $header;
        
        foreach ($headers as $key => $val) $_headers[] = $key.':'.$val;
        
        $curl   = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $_headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");   
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        
        $result = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($result,true);
        
        return $result;
    }
    
    // PATCH请求
    public function patch(string $url, array $params = [], array $headers = [])
    {
        $header  = ['Content-type'=>'application/json;','Accept'=>'application/json','origin'=>str_replace(['https','http',':','//'], '', $this->domain())];
        $params  = json_encode($params);
        $headers = !empty($headers) ? array_merge($header, $headers) : $header;
        
        foreach ($headers as $key => $val) $_headers[] = $key.':'.$val;
        
        $curl   = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $_headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");  
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        
        $result = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($result,true);
        
        return $result;
    }
    
    // 判断IP版本
    public function IPType($ip = '')
    {
        $version  = null;
        $is_ip    = false;
        
        if (filter_var($ip, \FILTER_VALIDATE_IP)) $is_ip = true;
        if (filter_var($ip, \FILTER_VALIDATE_IP,\FILTER_FLAG_IPV4)) $version = 4;
        if (filter_var($ip, \FILTER_VALIDATE_IP,\FILTER_FLAG_IPV6)) $version = 6;
        
        return ['version'=>$version,'is_ip'=>$is_ip];
    }
    
    /*
     * @name 自定义处理API
     * @param {string} url [API地址]
     * @param {string} api [API应用名]
     */
    public function CustomProcessApi($url = '', $api = 'api')
    {
        $result = $url;
        
        if (!empty($url)) {
            
            $prefix = '//';
            
            if (strstr($url, 'http://')) $prefix = 'http://';
            else if (strstr($url, 'https://')) $prefix = 'https://';
            
            // 过滤http(s):// - 转数组 - 去空 - 重排序
            $result = array_values(array_filter(explode('/',str_replace(['https','http',':'],'',$url))));
            
            if (count($result) == 1) $result = $prefix . $result[0] . "/" . $api . "/";
            else if (count($result) == 2) {
                $result = $prefix + $result[0] + "/" + $result[1] + "/";
            }
        }
        return $result;
    }
    
    // 数组键名转大写
    public function ArrayKeysToUpper(&$array, $case = CASE_LOWER, $flag = false)
    {
        $array = array_change_key_case($array, $case);
        if ($flag) foreach ($array as $key => $value) if (is_array($value)) $this->ArrayKeysToUpper($array[$key], $case, true);
    }
    
    // END
}