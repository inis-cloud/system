<?php
declare (strict_types = 1);

namespace app\api\controller\inis;

use QL\QueryList;
use think\Request;
use think\facade\{Cache};

class Other extends Base
{
    /**
     * 显示资源列表
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function index(Request $request)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        // $result = [];
        
        // $mode   = !empty($param['mode']) ? $param['mode'] : 'location';
        
        // // 存在的方法
        // $method = ['location'];
        
        // // 动态方法且方法存在
        // if (in_array($mode, $method)) $result = $this->$mode($request);
        // // 动态返回结果
        // if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->create($data, $msg, $code);
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $IID
     * @return \think\Response
     */
    public function read(Request $request, $IID)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];
        
        // 存在的方法
        $method = ['ua','qq','icp','ping','hot','domain'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->create($data, $msg, $code);
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $IID
     * @return \think\Response
     */
    public function update(Request $request, $IID)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $IID
     * @return \think\Response
     */
    public function delete(Request $request, $IID)
    {
        //
    }
    
    // 获取客户端信息
    public function ua($param)
    {
        $data = [];
        $code = 400;
        $msg  = '无数据！';
        
        $ua   = !empty($param['ua']) ? $param['ua'] : (!empty($this->header['user-agent']) ? $this->header['user-agent'] : null);
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        // 设置缓存名称
        $cache_name = 'other?ua='.$ua;
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
        else {
            
            if (!empty($ua)) {
                $data = [
                    'os'      => $this->helper->GetClientOS($ua),
                    'mobile'  => $this->helper->GetClientMobile($ua),
                    'browser' => $this->helper->GetClientBrowser($ua),
                ];
                $code = 200;
            } else $msg = 'header缺少user-agent信息';
            
            Cache::tag(['other',$cache_name])->set($cache_name, json_encode($data));
        }
        
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = '数据请求成功！';
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 解析QQ信息
    public function qq($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        if (empty($param['qq'])) $msg = '请提交QQ号，用参数qq表示！';
        else {
            
            $html   = file_get_contents("https://r.qzone.qq.com/fcg-bin/cgi_get_score.fcg?mask=7&uins=" . $param['qq']);
            // 获取编码
            $encode = mb_detect_encoding($html, ["ASCII",'UTF-8',"GB2312","GBK",'BIG5']);
            // 转换编码
            $html   = mb_convert_encoding($html, 'UTF-8', $encode);
            preg_match('/portraitCallBack\(([\s\S]+)\);/', $html, $mat);
            $result = json_decode($mat[1], true);
            $param['qq'] = array_keys($result)[0];
            $array = $result[$param['qq']];
            
            $code = 200;
            $data = [
                'qq'    => $param['qq'],
                'email' => $param['qq'] . '@qq.com',
                'name'  => $array[6],
                'image' => 'https://q1.qlogo.cn/g?b=qq&nk=' . $param['qq'] . '&s=100',
                'qzone' => 'https://' . str_replace(['https://','http://'],'',$array[0])
            ];
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 备案查询
    public function icp($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        if (empty($param['domain'])) $msg = '请提交域名，用参数 domain 表示！';
        else if (!$this->helper->CheckDomain($param['domain'])) $msg = '域名格式不正确！';
        else {
            
            $code   = 200;
            
            // 获取顶级域名
            $domain = $this->helper->GetTopDomain($param['domain']);
            
            // 是否开启了缓存
            $api_cache = $this->config['api_cache'];
            // 是否获取缓存
            $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
            
            // 设置缓存名称
            $cache_name = 'other/icp?domain='.$domain;
            
            // 检查是否存在请求的缓存数据
            if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
            else {
                
                $url = 'https://icp.chinaz.com/' . $domain;
                // 元数据采集规则
                $rules = [
                    'key'   => ['span','text'],
                    'value' => ['p','text'],
                ];
                // 切片选择器
                $range = '#first li';
                $list  = QueryList::get($url)->rules($rules)->range($range)->queryData();
                
                // 字段名称替换
                $array = [
                    '主办单位名称'      =>  'unit',
                    '主办单位性质'      =>  'nature',
                    '网站备案/许可证号' =>  'copy',
                    '网站名称'          =>  'name',
                    '网站首页网址'      =>  'index',
                    '审核时间'          =>  'time'
                ];
                
                // 处理数据
                foreach ($list as $val) if (in_array($val['key'], array_keys($array))) $data[$array[$val['key']]] = str_replace('查看截图','',$val['value']);
                
                // 缓存两天
                Cache::tag(['other',$cache_name])->set($cache_name, json_encode($data), 172800);
            }
            
            // 逆向思维，节省代码行数
            if (empty($data)) $code = 204;
            else $msg = '数据请求成功！';
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // PING查询
    public function ping($param)
    {
        $data = [];
        $code = 400;
        $msg  = 'ok';
        
        $domain = null;
        $port   = !empty($param['port'])    ? $param['port']    : 80;
        $timeout= !empty($param['timeout']) ? $param['timeout'] : 10;
        $ip     = !empty($param['ip']) ? $param['ip'] : $this->helper->GetClientIP();
        
        // 校验格式是否为域名
        if ($this->helper->CheckDomain($ip)) {
            
            $domain = str_replace(['https:','http:','//'], '', $ip);
            
            // 是否开启了缓存
            $api_cache = $this->config['api_cache'];
            // 是否获取缓存
            $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
            
            // 检查是否存在请求的缓存数据
            if (Cache::has($domain) and $api_cache and $cache) $ip = Cache::get($domain);
            else {
                
                // 域名解析IP
                $ip = gethostbyname($domain);
                
                // 缓存十分钟
                Cache::set($domain, $ip, 600);
            }
        }
        
        try {
            
            $timeStart = microtime(true);
            $FSO       = fSockOpen($ip, $port, $errno, $errstr, $timeout);
            if (!$FSO) $msg = "Ping 请求找不到主机 " . $ip . "。请检查该名称，然后重试。";
            else {
                $timeEnd = microtime(true);
                $code = 200;
                $data = [
                    'ping'  =>  round((($timeEnd - $timeStart) * 1000), 0)." ms",
                    'ip'    =>  $ip,
                    'domain'=>  $domain,
                    'port'  =>  $port
                ];
            }
            
        } catch (Exception $e) {
            $msg = '请求超时！';
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 热门
    public function hot($param)
    {
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];
        
        $mode = !empty($param['mode']) ? $param['mode'] : 'search';
        
        // 存在的方法
        $method = ['search'];
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 热搜
    public function search($param)
    {
        $data   = [];
        $code   = 200;
        $msg    = 'ok';
        
        // 需要获取的热搜数据
        $list   = ['baidu'];
        
        if (!empty($param['list'])) {
            if (is_string($param['list']))     $list = array_filter(array_unique(explode(',',$param['list'])));
            else if (is_array($param['list'])) $list = $param['list'];
        }
        
        // 是否开启了缓存
        $api_cache = $this->config['api_cache'];
        // 是否获取缓存
        $cache = (empty($param['cache']) or $param['cache'] == 'true') ? true : false;
        
        // 设置缓存名称
        $cache_name = 'other/hot?mode=search&list='.implode(',',$list);
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $api_cache and $cache) $data = json_decode(Cache::get($cache_name));
        else {
            
            $method = ['baidu','csdn'];
            
            foreach ($list as $val) if (in_array($val, $method)) $data[$val] = $this->$val();
            
            Cache::tag(['other',$cache_name])->set($cache_name, json_encode($data), 1800);
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 百度热搜数据
    public function baidu()
    {
        $data = [];
        
        $url = 'https://www.baidu.com/s?wd=inis%20api&oq=inis%20api';
        
        // 元数据采集规则
        $rules = [
            'index'   => ['.toplist1-hot_2RbQT', 'text'],
            'title'   => ['.toplist1-td_3zMd4 > a', 'text'],
            'url'     => ['.toplist1-td_3zMd4 > a', 'href'],
            'tag'     => ['.toplist1-td_3zMd4 .c-text', 'text'],
            'number'  => ['.toplist1-right-num_3FteC', 'text']
        ];
        
        // 切片选择器
        $range = '#content_right #con-ar .opr-toplist1-table_3K7iH .toplist1-tr_4kE4D';
        
        return QueryList::get($url)->rules($rules)->range($range)->query()->getData()->map(function($item){
            $item['url'] = 'https://www.baidu.com'.$item['url'];
            return $item;
        })->all();
    }
    
    // CSDN热搜
    public function csdn()
    {
        $result = [];
        $data   = $this->helper->get('https://blog.csdn.net/phoenix/web/blog/hot-rank');
        if (!empty($data['code'])) if ($data['code'] == 200) $result = $data['data'];
        
        return $result;
    }
}
