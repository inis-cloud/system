<?php
declare (strict_types = 1);

namespace app\api\middleware;

use Closure;
use think\facade\{Lang};
use inis\utils\{FileLog};
use app\model\mysql\{Options};
use think\{Config, Request, Response};

class api
{
    protected $cookieDomain;
    
    // 设置 header
    protected $header = [
        'Access-Control-Allow-Credentials' => true,
        'Access-Control-Max-Age'           => 1800,
        'Content-Type'                     => 'application/json; charset=utf-8',
        'Access-Control-Allow-Methods'     => 'GET, POST, PATCH, PUT, DELETE, OPTIONS, PATCH',
        'Access-Control-Allow-Headers'     => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With',
    ];
    
    // 构造器
    public function __construct(Config $config)
    {
        $this->FileLog      = new FileLog([
            'filePath' => app()->getRootPath() . 'runtime/storage/' . date('Y-m-d', time()) . '.log',
        ]);
        $this->cookieDomain = $config->get('cookie.domain', '');
    }
    
    /**
     * 处理请求
     *
     * @access public
     * @param Request $request
     * @param Closure $next
     * @param array   $header
     * @return Response
     */
    public function handle($request, Closure $next, ?array $header = [])
    {
        $params  = $request->param();

        $this->header['I-API-DESCRIBE'] = lang('I-API-DESCRIBE');

        $result  = ['code'=>403,'msg'=>'禁止非法操作！','data'=>[]];
        
        $header  = !empty($header) ? array_merge($this->header, $header) : $this->header;
        
        $security= Options::where(['keys'=>'config:security'])->findOrEmpty();
        
        $domain  = (!$security->isEmpty()) ? array_filter(explode(",", $security->value)) : ['*'];
        
        // 允许免校验接口
        $allow   = ['file/random'];
        
        // 获取请求API域名
        $headers = $request->header();
        // 请求接口地址
        $pathinfo= $request->pathinfo();
        
        // 域名白名单配置为空
        $map1 = empty($security->opt->domain);
        // 域名白名单配置为空
        $map2 = empty($domain);
        // 域名白名单关闭
        $map3 = ($security->opt->domain->status == 0);
        // 域名白名单放行全部
        $map4 = in_array('*',$domain);
        // 允许免校验接口
        $map5 = in_array($pathinfo,$allow);
        
        // 判断域名白名单是否为空
        if ($map1 or $map2 or $map3 or $map4 or $map5) {
            
            $header['Access-Control-Allow-Origin'] = '*';
            
        } else {
            
            $http_prefix = 'http://';
            
            if (!empty($headers['origin'])) {
                
                $origin = $headers['origin'];

                if (strstr($origin, 'http://')) $origin = str_replace('http://','',$origin);
                else if (strstr($origin, 'https://')) {
                    
                    $http_prefix = 'https://';
                    $origin = str_replace('https://','',$origin);
                }
                
                // 用于授权系统同意获取API后生效
                $domain[] = 'inis.cc';
                // 中文域名转码
                $origin = idn_to_utf8($origin, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
                
                // 处理HTTP请求，中间件代码
                if (in_array($origin, $domain)) $header['Access-Control-Allow-Origin'] = $http_prefix.$origin;
                
            } else return json($result);
        }
        
        // ↑↑↑ 前置中间件执行区域 ↑↑↑
        $reponse = $next($request)->header($header);
        // ↓↓↓ 后置中间件执行区域 ↓↓↓

        // 执行 Token 验证
        if ($security->opt->token->status == 1) {
            
            // 获取 header token
            $token  = $request->header('token', $request->request('token'));
            // 获取请求的域名 - 用于授权系统同意获取API后生效
            $origin = !empty($headers['origin']) ? str_replace(['https','http',':','//'], '', $headers['origin']) : null;
            
            // 三元赋值 token
            (!empty($token)) ? $token : $token = (!empty($params['token'])) ? $params['token'] : null;
            
            if ($token != $security->opt->token->value and $origin != 'inis.cc') $reponse = json($result);
        }
        
        if (empty($security->opt->domain) or empty($domain) or ($security->opt->domain->status == 0) or in_array('*',$domain)) {
            
        } else {
            // 防止代理
            $origin = !empty($headers['origin']) ? str_replace(['https','http',':','//'], '', $headers['origin']) : null;
            // 中文域名转码
            $origin = idn_to_utf8($origin, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            if (!in_array($origin, $domain)) return json($result);
        }

        // 写入日志
        if (config('inis.api.log', false)) $this->FileLog->save([
            'ip'    =>  $request->ip(),
            'url'   =>  $request->url(),
            'method'=>  $request->method(),
            'ua'    =>  $request->header('user-agent'),
            'params'=>  $request->param(),
        ]);
        
        // 回调本身并返回response对象
        return $reponse;
    }
}
