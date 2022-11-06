<?php
declare (strict_types = 1);

namespace app\api\controller\default;

use think\Request;
use think\facade\{Cache, Lang};

class Proxy extends Base
{
    protected $middleware = [];

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
        $result = [];
        $config = [];
        
        // 动态方法且方法存在
        $result = $this->curl($param, 'get');
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->json($data, $msg, $code, $config);
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
        $config = [];

        if ($IID == 'def') $IID = 'get';
        
        // 存在的方法
        $method = ['post'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->curl($param, $IID);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->json($data, $msg, $code, $config);
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
        $config = [];

        if ($IID == 'def') $IID = 'get';

        // 存在的方法
        $method = ['get','post','put','del','patch'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->curl($param, $IID);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->json($data, $msg, $code, $config);
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function IPUT(Request $request, $IID)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = Lang::get('参数不存在！');
        $result = [];
        $config = [];

        if ($IID == 'def') $IID = 'put';
        
        // 存在的方法
        $method = ['put'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->curl($param, $IID);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->json($data, $msg, $code, $config);
    }

    /**
     * 删除指定资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function IDELETE(Request $request, $IID)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = Lang::get('参数不存在！');
        $result = [];
        $config = [];

        if ($IID == 'def') $IID = 'del';
        
        // 存在的方法
        $method = ['del'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->curl($param, $IID);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->json($data, $msg, $code, $config);
    }
    
    /**
     * 补丁指定资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function IPATCH(Request $request, $IID)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = Lang::get('参数不存在！');
        $result = [];
        $config = [];

        if ($IID == 'def') $IID = 'patch';
        
        // 存在的方法
        $method = ['patch'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->curl($param, $IID);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->json($data, $msg, $code, $config);
    }
    
    public function curl($param, $method = 'get')
    {
        $data = [];
        $code = 400;
        $msg  = Lang::get('数据请求成功！');
        
        $url         = !empty($param['p_url'])     ? $param['p_url']     : null;
        $headers     = !empty($param['p_headers']) ? $param['p_headers'] : [];
        $is_comfig   = !empty($param['p_config'])  ? ($param['p_config'] == 'true' ? true : false) : false;
        
        unset($param['p_config']);
        
        // 处理ThinkPHP占用name参数问题
        if (!empty($param['p_name'])) {
            $param['name'] = $param['p_name'];
            unset($param['p_name']);
        } else unset($param['name']);
        
        if (empty($url)) $msg = Lang::get('参数p_url（需要代理请求的URL地址）不得为空！');
        else {
            
            $code = 200;
            unset($param['p_url']);
            unset($param['p_headers']);
            
            $data = $this->helper->$method($url, $param, $headers);
        }
        
        $params['url'] = $url;
        // 合并参数
        $params = !empty($param) ? array_merge($params, $param) : $params;
        // 返回提交的配置
        $config = ($is_comfig) ? ['config'=>['method'=>$method,'headers'=>$headers,'params'=>$params]] : [];
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg, 'config'=>$config];
    }
}