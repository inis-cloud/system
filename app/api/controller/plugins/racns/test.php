<?php
declare (strict_types = 1);

// 命名空间 - 末尾的racns等于文件夹名 - 其他为固定格式
namespace app\api\controller\plugins\racns;

// 必选
use think\Request;
// 按需导入 - 文档：https://www.kancloud.cn/manual/thinkphp6_0/1037491
use think\facade\{Cache, Config};
// 按需导入 - 数据库模型 - 具体见inis后端目录 app/model/mysql/
use app\model\mysql\{Links, Article};

// Base类，继承必选
use app\api\controller\Base;

// 类名test可以改为其他名字，但必须与文件名相同
class test extends Base
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
        $msg    = '方法不存在！';
        $result = [];
        $other  = [];
        
        // 存在的方法
        $method = ['demo','links','article','hasCache','setCache','getCache'];
        
        // 判断 mode参数 是否为空 - 为空调用 demo方法 - 否则调用参数mode传递的指定方法
        $mode   = (empty($param['mode'])) ? 'demo' : $param['mode'];
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->create($data, $msg, $code, $other);
    }
    
    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data   = [];
        $code   = 400;
        $msg    = '方法不存在！';
        $result = [];
        
        // 获取请求参数
        $param  = $request->param();
        
        // 存在的方法
        $method = ['sendEmail','addLinks'];
        
        if (!empty($param['mode'])) {
            
            $name = (string)$param['mode'];
            
            // 动态方法且方法存在
            if (in_array($name, $method)) $result = $this->$name($param);
            // 动态返回结果
            if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        }
        
        return $this->create($data, $msg, $code);
    }

    /**
     * 显示指定的资源
     *
     * @param  \think\Request  $request
     * @param  int  $IID
     * @return \think\Response
     */
    public function read(Request $request, $IID)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '方法不存在！';
        $result = [];
        $other  = [];
        
        // 存在的方法
        $method = ['demo','links','article'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->create($data, $msg, $code, $other);
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function update(Request $request)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '方法不存在！';
        $result = [];
        $other  = [];
        
        // 存在的方法
        $method = ['put'];
        
        // 判断 mode参数 是否为空 - 为空调用 demo方法 - 否则调用参数mode传递的指定方法
        $mode   = (empty($param['mode'])) ? 'put' : $param['mode'];
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->create($data, $msg, $code, $other);
    }

    /**
     * 删除指定资源
     *
     * @param  \think\Request  $request
     * @param  int  $IID
     * @return \think\Response
     */
    public function delete(Request $request)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '方法不存在！';
        $result = [];
        $other  = [];
        
        // 存在的方法
        $method = ['del'];
        
        // 判断 mode参数 是否为空 - 为空调用 demo方法 - 否则调用参数mode传递的指定方法
        $mode   = (empty($param['mode'])) ? 'del' : $param['mode'];
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->create($data, $msg, $code, $other);
    }
    
    /**
     * patch请求
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function patch(Request $request)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '方法不存在！';
        $result = [];
        $other  = [];
        
        // 存在的方法
        $method = ['patches'];
        
        // 判断 mode参数 是否为空 - 为空调用 demo方法 - 否则调用参数mode传递的指定方法
        $mode   = (empty($param['mode'])) ? 'patches' : $param['mode'];
        
        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->create($data, $msg, $code, $other);
    }
    
    /**
     * $name  默认的演示方法
     * $param 发起请求时提交的参数
     */
    public function demo($param)
    {
        // 数据
        $data  = [];
        // 状态码
        $code  = 200;
        // 接口信息
        $msg   = 'ok';
        // 其他参数
        $other = [
            'author'=>[
                'name' => '兔子',
                'qq'   => 97783391,
                'email'=> '97783391@qq.com',
            ],
            'api-info'=>[
                'name'          => '演示API',
                'description'   => '这是一个演示用的API，里面包含了许多API案例',
                'version'       => '1.0.0'
            ]
        ];
        
        $data  = [
            // 调用助手函数，获取服务器域名
            'domian'     =>  $this->Helper->domain(),
            // 调用助手函数，获取客户端IP
            'ip'         =>  $this->Helper->GetClientIP(),
            // 从请求头中获取客户端UA信息
            'ua'         =>  [
                'value'  =>  $this->Header['user-agent'],
                // 从客户端UA中解析浏览器信息
                'browser'=>  $this->Helper->GetClientBrowser($this->Header['user-agent']),
                // 从客户端UA中解析系统信息
                'os'     =>  $this->Helper->GetClientOS($this->Header['user-agent']),
                // 从客户端UA中解析移动信息
                'mobile' =>  $this->Helper->GetClientMobile($this->Header['user-agent']),
            ],
            // 从inis配置文件中获取信息 - 是否开启API缓存
            'cache'      =>  Config::get('inis.api_cache'),
            // 请求参数
            'params'     =>  $param
        ];
        // 关于助手函数$this->Helper的更多方法，可以前往 inis后端/extend/inis/utils/helper.php 内查看更多方法
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg,'other'=>$other];
    }
    
    /**
     * $name  获取友链方法
     * $param 发起请求时提交的参数
     * $docs  https://www.kancloud.cn/manual/thinkphp6_0/1037585
     */
    public function links($param)
    {
        // 数据
        $data  = [];
        // 状态码
        $code  = 200;
        // 接口信息
        $msg   = 'ok';
        
        $data  = [
            // ThinkPHP原生查询 - 文档：https://www.kancloud.cn/manual/thinkphp6_0/1037585
            'native'=>Links::limit(10)->select(),
            // inis处理过的查询 - ExpandAll(int $id, array $config)
            'expand'=>Links::ExpandAll(null, [
                'page'   =>  !empty($param['page'])  ? (int)$param['page']  : 1,
                'limit'  =>  !empty($param['limit']) ? (int)$param['limit'] : 2,
                'order'  =>  !empty($param['order']) ? (string)$param['order'] : 'create_time asc',
            ])
        ];
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    /**
     * $name  获取文章方法
     * $param 发起请求时提交的参数
     * $docs  https://www.kancloud.cn/manual/thinkphp6_0/1037585
     */
    public function article($param)
    {
        // 数据
        $data  = [];
        // 状态码
        $code  = 200;
        // 接口信息
        $msg   = 'ok';
        
        $data  = [
            // ThinkPHP原生查询 - 文档：https://www.kancloud.cn/manual/thinkphp6_0/1037585
            'native'=>Article::limit(2)->select(),
            // inis处理过的查询 - ExpandAll(int $id, array $config)
            'expand'=>Article::ExpandAll(null, [
                'page'   =>  !empty($param['page'])  ? (int)$param['page']  : 1,
                'limit'  =>  !empty($param['limit']) ? (int)$param['limit'] : 2,
                'order'  =>  !empty($param['order']) ? (string)$param['order'] : 'create_time asc',
            ]),
        ];
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    /**
     * $name  判断缓存是否存在
     * $param 发起请求时提交的参数
     * $docs  https://www.kancloud.cn/manual/thinkphp6_0/1037634
     */
    public function hasCache($param)
    {
        // 数据
        $data  = [];
        // 状态码
        $code  = 400;
        // 接口信息
        $msg   = 'ok';
        
        if (empty($param['name'])) $msg = '请提交需要查询的缓存名称，用参数 name 表示';
        else {
            
            $code  = 200;
            $data  = [
                'name'        => $param['name'],
                'cache'       => Cache::has($param['name']),
                'description' => Cache::has($param['name']) ? '缓存存在' : '缓存不存在'
            ];
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    /**
     * $name  获取缓存
     * $param 发起请求时提交的参数
     * $docs  https://www.kancloud.cn/manual/thinkphp6_0/1037634
     */
    public function getCache($param)
    {
        // 数据
        $data  = [];
        // 状态码
        $code  = 200;
        // 接口信息
        $msg   = 'ok';
        
        if (empty($param['name'])) $msg = '请提交需要查询的缓存名称，用参数 name 表示';
        else {
            
            $code  = 200;
            $data  = [
                'name'        => $param['name'],
                'cache'       => Cache::get($param['name']),
                'description' => empty(Cache::get($param['name'])) ? '缓存数据为空' : '缓存数据不为空'
            ];
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    /**
     * $name  设置缓存
     * $param 发起请求时提交的参数
     * $docs  https://www.kancloud.cn/manual/thinkphp6_0/1037634
     */
    public function setCache($param)
    {
        // 数据
        $data  = [];
        // 状态码
        $code  = 400;
        // 接口信息
        $msg   = 'ok';
        
        if (empty($param['name']))      $msg = '请提交需要设置的缓存名称，用参数 name 表示';
        else if (empty($param['data'])) $msg = '请提交需要设置的缓存数据，用参数 data 表示';
        else {
            
            $code  = 200;
            $data  = [
                'name'        => $param['name'],
                'cache'       => Cache::set($param['name'], $param['data']),
                'cache_data'  => Cache::get($param['name']),
                'description' => '缓存成功'
            ];
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    /**
     * $name  邮箱推送
     * $param 发起请求时提交的参数
     * $desc  需要在后台先配置邮件服务，才能成功发送邮箱推送
     */
    public function sendEmail($param)
    {
        // 数据
        $data  = [];
        // 状态码
        $code  = 400;
        // 接口信息
        $msg   = 'ok';
        
        $template = "<div>
            <h2>这是一个演示用的API</h2>
            <p>当你收到这份邮件时，表示您的邮箱推送<span style='color:red'>已生效</span>！</p>
        </div>";
        
        if (empty($param['email'])) $msg = '请提交需要接收的邮箱，用参数 email 表示';
        else {
            
            $code = 200;
            
            // 这个方法可以往指定邮箱发送一封邮件
            // $this->Tool->sendMail(<string | array> $email, string $title, string $template)
            $this->Tool->sendMail($param['email'], 'inis演示API', $template);
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    /**
     * $name  添加友链
     * $param 发起请求时提交的参数
     */
    public function addLinks($param)
    {
        // 数据
        $data   = [];
        // 状态码
        $code   = 200;
        // 接口信息
        $msg    = 'ok';
        
        // 允许提交并存储的字段
        $obtain = ['name','url','head_img','description','sort_id','is_show'];
        
        $links  = new Links;
        
        // 判断字段是否允许存储，防提权
        foreach ($param as $key => $val) if (in_array($key, $obtain)) $links->$key = $val;
        
        // 权限判断
        $links->save();
        
        $data   = $links;
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    /**
     * $name  PUT请求
     * $param 发起请求时提交的参数
     */
    public function put($param)
    {
        // 数据
        $data   = [];
        // 状态码
        $code   = 200;
        // 接口信息
        $msg    = 'ok';
        
        $data   = [
            'method'  =>  'PUT',
            'params'  =>  $param
        ];
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    /**
     * $name  DELETE请求
     * $param 发起请求时提交的参数
     */
    public function del($param)
    {
        // 数据
        $data   = [];
        // 状态码
        $code   = 200;
        // 接口信息
        $msg    = 'ok';
        
        $data   = [
            'method'  =>  'DELETE',
            'params'  =>  $param
        ];
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    /**
     * $name  PATCH请求
     * $param 发起请求时提交的参数
     */
    public function patches($param)
    {
        // 数据
        $data   = [];
        // 状态码
        $code   = 200;
        // 接口信息
        $msg    = 'ok';
        
        $data   = [
            'method'  =>  'PATCH',
            'params'  =>  $param
        ];
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
}
