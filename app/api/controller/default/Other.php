<?php
declare (strict_types = 1);

namespace app\api\controller\default;

use QL\QueryList;
use think\Request;
use think\facade\{Cache, Lang};

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
        $msg    = Lang::get('参数不存在！');
        
        return $this->json($data, $msg, $code);
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function IPOST(Request $request, $IID)
    {
        
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $IID
     * @return \think\Response
     */
    public function IGET(Request $request, $IID)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = Lang::get('参数不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['ua','qq','icp','ping','hot','domain','base64','baiduRecord'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->json($data, $msg, $code);
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $IID
     * @return \think\Response
     */
    public function IPUT(Request $request, $IID)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $IID
     * @return \think\Response
     */
    public function IDELETE(Request $request, $IID)
    {
        //
    }
    
    // 获取客户端信息
    public function ua($param)
    {
        $data = [];
        $code = 400;
        $msg  = Lang::get('无数据！');
        
        $ua   = !empty($param['ua']) ? $param['ua'] : (!empty($this->header['user-agent']) ? $this->header['user-agent'] : null);
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'other/ua'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            
            if (!empty($ua)) {
                $data = [
                    'os'      => $this->helper->GetClientOS($ua),
                    'mobile'  => $this->helper->GetClientMobile($ua),
                    'browser' => $this->helper->GetClientBrowser($ua),
                ];
                $code = 200;
            } else $msg = Lang::get('header缺少user-agent信息！');
            
            if ($this->ApiCache) Cache::tag(['other',$cache_name])->set($cache_name, json_encode($data));
        }
        
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = Lang::get('数据请求成功！');
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 解析QQ信息
    public function qq($param)
    {
        $data = [];
        $code = 400;
        $msg  = Lang::get('数据请求成功！');
        
        if (empty($param['qq'])) $msg = Lang::get('请提交QQ号，用参数qq表示！');
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
        $msg  = Lang::get('数据请求成功！');
        
        // 允许的类型
        $allow= ['chinaz','yudinet'];
        // 配置默认类型
        $type = empty($param['type']) ? 'chinaz' : $param['type'];
        // 防止无类型
        $type = in_array($type, $allow) ? $type : 'chinaz';
        
        if (empty($param['domain'])) $msg = '请提交域名，用参数 domain 表示！';
        else if (!$this->helper->CheckDomain($param['domain'])) $msg = '域名格式不正确！';
        else {
            
            $code   = 200;
            
            // 获取顶级域名
            $domain = $this->helper->GetTopDomain($param['domain']);
            
            // 设置缓存名称
            $cache_name = json_encode(array_merge(['IAPI'=>'other/icp','domain'=>$domain], $param));
            
            // 检查是否存在请求的缓存数据
            if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
            else {
                
                $data = $type == 'chinaz' ? $this->chinaz($domain) : $this->yudinet($domain);
                // 缓存两天
                if ($this->ApiCache) Cache::tag(['other',$cache_name])->set($cache_name, json_encode($data), 172800);
            }
            
            // 逆向思维，节省代码行数
            if (empty($data)) $code = 204;
            else $msg = Lang::get('数据请求成功！');
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 爬取站长之家的备案信息
    public function chinaz($domain)
    {
        $data= [];
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
        
        return $data;
    }
    
    public function yudinet($domain)
    {
        $data  = [];
     
        $url  = 'http://beian.yudinet.com/Account/QueryRecordStatus';
        
        // 采集正文内容
        $html = QueryList::post($url, [
            'queryType'     => 0,
            'queryContent'  => $domain
        ])->getHtml();
        
        // 匹配 table 数据
        $table = QueryList::html($html)->find('table');
        
        // 采集表头
        $th = $table->find('tr:eq(0)')->find('th')->texts();
        // 采集表内容
        $td = $table->find('tr:eq(1)')->map(function($row){
            return $row->find('td')->texts()->all();
        });
        
        // 字段名称替换
        $array = [
            '网站名称'    =>  'name',
            '主体备案号'  =>  'main',
            '网站备案号'  =>  'copy',
            '备案状态'    =>  'status',
        ];
        
        foreach ($th->all() as $key => $val) if (in_array($val, array_keys($array))) {
            $data[$array[$val]] = $td->all()[0][$key];
        }
        
        return $data;
    }
    
    // PING查询
    public function ping($param)
    {
        $data = [];
        $code = 400;
        $msg  = Lang::get('数据请求成功！');
        
        $domain = null;
        $port   = !empty($param['port'])    ? $param['port']    : 80;
        $timeout= !empty($param['timeout']) ? $param['timeout'] : 10;
        $ip     = !empty($param['ip']) ? $param['ip'] : $this->helper->GetClientIP();
        
        // 校验格式是否为域名
        if ($this->helper->CheckDomain($ip)) {
            
            $domain = str_replace(['https:','http:','//'], '', $ip);
            
            // 是否开启了缓存
            $api_cache = config('inis.api.cache');
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
            $FSO       = fSockOpen($ip, (int)$port, $errno, $errstr, (float)$timeout);
            if (!$FSO) $msg = Lang::get('Ping 请求找不到主机', [$ip]);
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
            
        } catch (\Exception $e) {
            $msg = Lang::get('请求超时！');
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 热门
    public function hot($param)
    {
        $data   = [];
        $code   = 400;
        $msg    = Lang::get('参数不存在！');
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
        $msg    = Lang::get('数据请求成功！');
        
        // 需要获取的热搜数据
        $list   = ['baidu'];
        
        if (!empty($param['list'])) {
            if (is_string($param['list']))     $list = array_filter(array_unique(explode(',',$param['list'])));
            else if (is_array($param['list'])) $list = $param['list'];
        }
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'other/search'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            
            $method = ['baidu','csdn'];
            
            foreach ($list as $val) if (in_array($val, $method)) $data[$val] = $this->$val();
            
            if ($this->ApiCache) Cache::tag(['other',$cache_name])->set($cache_name, json_encode($data), 1800);
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

    public function base64($param)
    {
        $data   = [];
        $code   = 400;
        $msg    = Lang::get('数据请求成功！');

        if (empty($param['url'])) $msg = Lang::get('请提交图片地址，用参数 url 表示！');
        else {
            
            $param['url'] = $this->helper->GetRedirectUrl($param['url']);
            
            // 设置缓存名称
            $cache_name = json_encode(array_merge(['IAPI'=>'other/base64'], $param));
            
            // 检查是否存在请求的缓存数据
            if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
            else {
                // 重定向后的地址
                $image  = getimagesize($param['url']);
                $base64 = "" . chunk_split(base64_encode(file_get_contents($param['url'])));
                $data   = [
                    'url'    => $param['url'],
                    'base64' => 'data:' . $image['mime'] . ';base64,' . chunk_split(base64_encode(file_get_contents($param['url'])))
                ];
                if ($this->ApiCache) Cache::tag(['other',$cache_name])->set($cache_name, json_encode($data));
            }
            
            $code = 200;
            $msg  = Lang::get('无数据！');
            // 逆向思维，节省代码行数
            if (empty($data)) $code = 204;
            else $msg = Lang::get('数据请求成功！');
        }

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    public function baiduRecord($param)
    {

        $data   = [];
        $code   = 400;
        $msg    = Lang::get('数据请求成功！');

        if (empty($param['domain'])) $msg = Lang::get('请提交域名，用参数 domain 表示！');
        else {

            // 过滤掉域名中的 http:// 和 https://
            $domain = str_replace(['https','http','://'], '', $param['domain']);
            $url    = 'https://www.baidu.com/s?ie=UTF-8&wd=site%3A' . $domain;

            // // 切片选择器
            // $range = '#content_left';
            
            // $result  = QueryList::get($url, null, [
            //     'headers' => [
            //         'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36',
            //         'Accept-Encoding' => 'gzip, deflate, br',
            //     ]
            // ]);
            // // ->range($range)->queryData();
            // // 获取搜索结果标题列表
            // $text = $result->find('#content_left')->texts();
            // $data = $text->all();
        }

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
}
