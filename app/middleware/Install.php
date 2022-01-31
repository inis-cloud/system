<?php
declare (strict_types = 1);

namespace app\middleware;

use Closure;
use inis\utils\{File};
use think\{Request, Response};
use think\facade\{Cookie, Session};

class Install
{
    
    /**
     * 处理请求
     *
     * @access public
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        // halt($request);
        
        $File    = new File;
        
        // public目录是否存在 install.env - 存在则安装
        if (in_array('install.env', $File->dirInfo('./'))) {
            
            $route_file_path = "../app/install/route/app.php";
            
            // 路由信息
            $text = "<?php\n\nuse think\\facade\\Route;\n\nRoute::group('handle', function (){\n\tRoute::rule(':name', 'Handle/:name');\n});\n\nRoute::group(function (){\n\tRoute::any('/', 'Index/index');\n\tRoute::rule(':name', 'Index/:name');\n});";
            
            // 创建路由
            $route = $File->writeFile($route_file_path, $text);
            
            // 如果创建失败
            if (!$route) {
                // 创建文件夹
                $File->createDir("../app/install/route");
                // 重新创建路由
                $File->writeFile($route_file_path, $text);
            }
            
            // 给路由文件赋予755权限 - 虽然默认的644也可以
            $File->changeFile($route_file_path, 'mode', 0755);
            
            // 安装初始化完成，开始执行安装引导
            return redirect((string) url('/install'));
        }
        
        // ↑↑↑ 前置中间件执行区域 ↑↑↑
        $reponse = $next($request);
        // ↓↓↓ 后置中间件执行区域 ↓↓↓
        
        // 回调本身并返回response对象
        return $reponse;
    }
}
