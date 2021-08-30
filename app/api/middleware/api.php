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
        $params = $request->param();
        
        $result = ['code'=>403,'msg'=>'禁止非法操作！','data'=>[]];
        
        $header = !empty($header) ? array_merge($this->header, $header) : $this->header;
        
        $site_conf = Options::where(['keys'=>'site_conf'])->findOrEmpty();
        
        $domain = Options::where(['keys'=>'domain'])->findOrEmpty();
        $domain = (!$domain->isEmpty()) ? array_filter(explode(",", $domain->value)) : ['*'];
        
        // 获取请求API域名
        $headers = $request->header();
        
        // 判断域名白名单是否为空
        if (empty($site_conf->opt->domain) or empty($domain) or ($site_conf->opt->domain->status == 0) or in_array('*',$domain)) {
            
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
        
        // // 获取请求方式
        // if ($request->method(true) === 'OPTIONS') $reponse->code(204);
        
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
