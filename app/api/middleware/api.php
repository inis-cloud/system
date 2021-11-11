<?php
declare (strict_types = 1);
namespace app\api\middleware;

use Closure;
use think\Config;
use think\Request;
use think\Response;
use app\model\Options;

class api
{
    protected $cookieDomain;
    
    // 设置 header
    protected $header = [
        'Access-Control-Allow-Credentials' => true,
        'Access-Control-Max-Age'           => 1800,
        'Content-Type'                     => 'application/json;charset=utf-8',
        'Access-Control-Allow-Methods'     => 'GET, POST, PATCH, PUT, DELETE, OPTIONS, PATCH',
        'Access-Control-Allow-Headers'     => 'Authorization, token, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With',
    ];
    
    // 构造器
    public function __construct(Config $config)
    {
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
        
        $result  = ['code'=>403,'msg'=>'禁止非法操作！','data'=>[]];
        
        $header  = !empty($header) ? array_merge($this->header, $header) : $this->header;
        
        $site_conf = Options::where(['keys'=>'site_conf'])->findOrEmpty();
        
        $domain  = Options::where(['keys'=>'domain'])->findOrEmpty();
        $domain  = (!$domain->isEmpty()) ? array_filter(explode(",", $domain->value)) : ['*'];
        
        // 允许免校验接口
        $allow   = ['file/random'];
        
        // 获取请求API域名
        $headers = $request->header();
        // 请求接口地址
        $pathinfo= $request->pathinfo();
        
        // 域名白名单配置为空
        $map1 = empty($site_conf->opt->domain);
        // 域名白名单配置为空
        $map2 = empty($domain);
        // 域名白名单关闭
        $map3 = ($site_conf->opt->domain->status == 0);
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
                
                // 处理HTTP请求，中间件代码
                if (in_array($origin, $domain)) $header['Access-Control-Allow-Origin'] = $http_prefix.$origin;
                
            } else return json($result);
        }
        
        // ↑↑↑ 前置中间件执行区域 ↑↑↑
        $reponse = $next($request)->header($header);
        // ↓↓↓ 后置中间件执行区域 ↓↓↓
        
        // 执行 Token 验证
        if ($site_conf->opt->token->status == 1) {
            
            // 获取 header token
            $token = $request->header('token', $request->request('token'));
            
            // 三元赋值 token
            (!empty($token)) ? $token : $token = (!empty($params['token'])) ? $params['token'] : null;
            
            if ($token != $site_conf->opt->token->value) $reponse = json($result);
        }
        
        if (empty($site_conf->opt->domain) or empty($domain) or ($site_conf->opt->domain->status == 0) or in_array('*',$domain)) {
            
        } else {
            // 防止代理
            $origin = !empty($headers['origin']) ? str_replace(['https','http',':','//'], '', $headers['origin']) : null;
            if (!in_array($origin, $domain)) return json($result);
        }
        
        // 回调本身并返回response对象
        return $reponse;
    }
}
