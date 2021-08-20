<?php
namespace app\index\controller;

use Parsedown;
use app\Request;
use app\index\controller\Tool;
use app\index\controller\Index;
use app\model\Log;
use app\model\Tag;
use think\facade\Db;
use app\model\Users;
use app\model\Links;
use Metowolf\Meting;
use inis\music\Music;
use Firebase\JWT\JWT;
use inis\utils\File;
use inis\utils\helper;
use inis\utils\markdown;
use app\model\Article;
use app\model\Options;
use app\model\Comments;
use think\facade\Cache;
use think\facade\Config;
use app\model\LinksSort;
use think\facade\Cookie;
use app\model\VerifyCode;
use think\facade\Session;
use app\model\ArticleSort;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\SignatureInvalidException;

use inis\utils\Db as inisDb;

class Test
{
    // 助手函数
    protected $File;
    protected $helper;
    // inis 配置
    protected $config;
    
    public function __construct()
    {
        header('Access-Control-Allow-Origin:*');
        $this->File   = new File;
        $this->helper = new helper;
        $this->config = Config::get('inis');
    }
    
    public function index(Request $request)
    {
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        // $data = [
        //     'domain' =>$_SERVER['HTTP_HOST'],
        //     'php_ver'=>PHP_VERSION,
        //     'test'   =>php_sapi_name()
        // ];
        
        
        
        
        
        
        // $File = new File();
        
        // $data = [];
        
        // $File->rename('/www/wwwroot/api.inis.cn/public/2.txt','/www/wwwroot/api.inis.cn/public/1.txt');
        
        // 可以正确读取文件大小
        // $data = $File->openInfo('./1.txt');
        
        // 可以正确读取目录大小
        // $data = $File->getDirInfo('./index');
        
        // 可以得到文件和文件夹的其他信息
        // $data = $File->listInfo('./index');
        
        // $File->createFile('./b.txt');
        
        // return json($data);
        
        // $File = new File();
        
        // // 创建文件夹
        // $File->createDir('aaa/aa/aa');
        
        // // 创建文件
        // $File->createFile('b.text');
        
        // // 操作文件
        // $File->handleFile('a.text','index/b.text','copy');
        
        // // 操作文件夹
        // $File->handleDir('test', 'index/test', 'move');
        
        // // 重命名文件
        // $File->rename('a.text','b.text');
        
        // 获取目录信息
        // $data = $File->getDirInfo('./');
        
        // 列出指定目录下符合条件的文件和文件夹
        // $data = $File->listDirInfo('./',true,'env');
        
        // // 返回指定路径的文件夹信息，其中包含指定路径中的文件和目录
        // $data = $File->dirInfo('./');
        
        // // 判断文件夹是否为空
        // $data = $File->isEmpty('test/a');
        
        // // 返回指定文件和目录的信息
        // $data = $File->listInfo('test/a');
        
        // // 返回关于打开文件的信息
        // $data = $File->openInfo('a.txt');
        
        // // 改变文件和目录的相关属性
        // $data = $File->changeFile('test','mode', 0777);
        
        // // 获取文件或目录的属性信息
        // $data = $File->getFileType('a.txt');
        
        // // 获取服务器的最大上传限制
        // $data = $File->allowUploadSize();
        
        // // 删除文件
        // $data = $File->unlinkFile('./test');
        
        // // 删除非空文件夹
        // $data = $File->removeDir('test',true);
        
        // $data = $File->empty_dir('test');
        
        // return json($data);
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        // function markdown($text, $code, $tag, $open) {
            
        // 	if (strpos($text, $code)) {
        	    
        // 		$insertTag = ($open) ? '<'.$tag.'>' : '<{}'.$tag.'>';
        		
        // 		$reg       = '['.preg_quote($code).']';
        		
        // 		$text      = preg_replace($reg, $insertTag, $text, 1);
        		
        // // 		$text = markdown($text, $code, $tag, !$open);
        		
        // 		return $text;
        		
        // 	} else return;
        	
        //     // if(strpos($text, $code)){
        //     //     $insertTag=($open) ? '<'.$tag.'>' : '<{}'.$tag.'>';
        //     // }
        // }
        
        // function getCharpos2($str, $char) {
        // 	$j   = 0;
        // 	$arr = [];
        // 	$count = substr_count($str, $char);
        // 	for ($i = 0; $i < $count; $i++) {
        // 		$j = strpos($str, $char, $j);
        // 		$arr[] = $j;
        // 		$j = $j+1;
        // 	}
        // 	return $arr;
        // }
        
        
        // $input = " *Bold text*  *Bold text* ";
        
        // $input = getCharpos2($input,'*');
        
        // // $input = markdown($input, '*', 'strong', true);
        // // $input = markdown($input, '*', '/strong', true);
        
        // // $input = str_replace('{}', '/', $input);
        
        // $arr = " *Bold text* -好的- *Bold text* ";
        // $str1 = str_replace("*","<strong>",$arr,$i);
        // echo $str1;
        
        // return json($str1);
        
        
        
        /**
         * 代码自己写的，但注释是给别人看的
         * 好的代码和注释是程序员的职业素养
         * */
        // QQ音乐歌单地址
        // $url = 'https://c.y.qq.com/base/fcgi-bin/u?__=ZhpZFd43';
        // $url = 'https://music.163.com/playlist?id=2821988381&userid=1868061933';
        // // 助手函数
        // $helper = new helper();
        // // 重定向URL
        // $url = $helper->GetRedirectUrl($url);
        // // 解析歌单ID
        // $id  = $helper->UrlPartsToArr($url)['id'];
        // // 获取歌单 netease - tencent
        // $music = (new Music)->GetInfo($id,'netease','collect');
        // // 返回歌单
        // return json($music);
    }
}