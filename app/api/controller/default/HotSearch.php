<?php
declare (strict_types = 1);

namespace app\api\controller\default;

use QL\QueryList;
use think\Request;
use think\facade\{Cache, Lang};

class HotSearch extends Base
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
        $msg    = Lang::get('方法不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['complex','list','detail'];
        
        $mode   = !empty($param['mode']) ? $param['mode'] : 'complex';
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
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
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = Lang::get('参数不存在！');
        $result = [];
        
        // 存在的方法
        $method = [];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->json($data, $msg, $code);
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
        $msg    = Lang::get('方法不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['complex','list','detail'];
        
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
    
    // 综合热搜
    public function complex($param)
    {
        $data = [];
        $code = 400;
        $msg  = Lang::get('数据请求成功！');

        $type = !empty($param['type']) ? $param['type'] : 'complex';

        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'hot-search/complex'], $param));
        $cache_time = !empty($param['cache_time']) ? (int)$param['cache_time'] : 10 * 60;

        // 从缓存中获取数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = Cache::get($cache_name);
        // 缓存中没有数据，爬虫去爬一下数据
        else {

            $range  = '.hot_main.hot_index .hot_list';
            $rules  = [
                'title' => ['.right_box .com_title span', 'text'],
                'list'  => ['.hot_new_list', 'html'],
            ];
            $html   = QueryList::get($this->config['openapi']['reptile']['hot_search'] . "/hot/$type", null, [
                'headers' => [
                    // 3000+ 随机 User-Agent，伪装蜘蛛，防止被封
                    // 'User-Agent'      => $this->randUA(),
                    'Accept-Encoding' => 'gzip, deflate, br',
                ]
            ]);

            $item = $html->rules($rules)->range($range)->query()->getData(function($item) {
                $rules = [
                    'title' => ['span:eq(1)'  , 'text'],
                    'url'   => ['span:eq(1) a', 'href'],
                    'count' => ['span:eq(2)'  , 'text'],
                ];
                $item['list']= QueryList::html($item['list'])->rules($rules)->range('li')->queryData();
                return $item;
            })->all();

            $data = json_encode($item, JSON_UNESCAPED_UNICODE);

            // 设置缓存数据
            if ($this->ApiCache) Cache::set($cache_name, $data, $cache_time);
        }

        $data = json_decode($data, true);
        $code = !empty($data) ? 200 : 204;
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 热搜榜
    public function list($param)
    {
        $data = [];
        $code = 400;
        $msg  = Lang::get('数据请求成功！');

        $type = !empty($param['type']) ? $param['type'] : 'complex';
        $name = !empty($param['name']) ? $param['name'] : 'baidu';

        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'hot-search/list'], $param));
        $cache_time = !empty($param['cache_time']) ? (int)$param['cache_time'] : 10 * 60;

        // 从缓存中获取数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = Cache::get($cache_name);
        // 缓存中没有数据，爬虫去爬一下数据
        else {

            $range  = '.right_box.mb15 ul.hot_new_list li';
            $rule   = [
                'title' => ['span:eq(1)', 'text'],
                'url'   => ['span:eq(1) a', 'href'],
                'count' => ['span:eq(3)', 'text'],
            ];

            $html   = QueryList::get($this->config['openapi']['reptile']['hot_search'] . "/hot/$type/$name", null, [
                'headers' => [
                    // 'User-Agent'      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36',
                    'Accept-Encoding' => 'gzip, deflate, br',
                ]
            ]);
            $data['title'] = $html->find('.right_box.mb15 .com_title span')->text();
            $data['list']  = $html->rules($rule)->range($range)->query()->getData()->all();

            $data = json_encode($data, JSON_UNESCAPED_UNICODE);

            // 设置缓存数据
            if ($this->ApiCache) Cache::set($cache_name, $data, $cache_time);
        }

        $data = json_decode($data, true);
        $code = !empty($data) ? 200 : 204;
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 热搜详情
    public function detail($param)
    {
        $data = [];
        $code = 400;
        $msg  = Lang::get('数据请求成功！');

        $url  = !empty($param['url']) ? $param['url'] : null;

        if (empty($url)) return ['data'=>$data,'code'=>$code,'msg'=>Lang::get('url参数不能为空！')];

        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'hot-search/detail'], $param));
        $cache_time = !empty($param['cache_time']) ? (int)$param['cache_time'] : 10 * 60;

        // 从缓存中获取数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = Cache::get($cache_name);
        // 缓存中没有数据，爬虫去爬一下数据
        else {

            $html   = QueryList::get($this->config['openapi']['reptile']['hot_search'] . $url, null, [
                'headers' => [
                    // 3000+ 随机 User-Agent，伪装蜘蛛，防止被封
                    // 'User-Agent'      => $this->randUA(),
                    'Accept-Encoding' => 'gzip, deflate, br',
                ]
            ]);

            $range  = '.main .hot_details_top .hot_details_index li';
            $rules  = [
                'title' => ['.text2', 'text'],
                'text'  => ['.text1', 'text'],
            ];

            $data['title']       = $html->find('title')->text();
            $data['description'] = str_replace('0XUCN', 'inis api', $html->find('meta[name="description"]')->attr('content'));
            $data['keywords']    = $html->find('meta[name="keywords"]')->attr('content');
            $data['list']        = $html->rules($rules)->range($range)->query()->getData()->all();
            $data['hot']         = [
                'key'         => $html->find('.main .hot_details_top .hot_key')->text(),
                'source'      => $html->find('.main .hot_details_top .hot_source')->text(),
                'link'        => $html->find('.main .hot_details_top .hot_link')->attr('href'),
                'echarts'     => $html->find('.main .hot_details_echarts .title')->text(),
                'description' => $html->find('.main .hot_details_top .hot_desc')->text(),
            ];
            // 去掉首尾的 # 号
            $key = rtrim(ltrim($data['hot']['key'], '#'), '#');
            $data['hot']['key'] = $key;
            $data['search'] = [
                'baidu'   => "https://www.baidu.com/s?wd=$key",
                'so'      => "https://www.so.com/s?q=$key",
                'sogou'   => "https://www.sogou.com/web?query=$key",
                'weixin'  => "https://weixin.sogou.com/weixin?type=2&query=$key",
                'toutiao' => "https://so.toutiao.com/search/?dvpf=pc&keyword=$key",
                'weibo'   => "https://s.weibo.com/article?q=$key",
                'zhihu'   => "https://www.zhihu.com/search?type=content&q=$key",
                'bilibili'=> "https://search.bilibili.com/all?keyword=$key",
            ];

            $data = json_encode($data, JSON_UNESCAPED_UNICODE);

            // 设置缓存数据
            if ($this->ApiCache) Cache::set($cache_name, $data, $cache_time);
        }

        $data = json_decode($data, true);
        $code = !empty($data) ? 200 : 204;
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
}
