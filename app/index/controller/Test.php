<?php
namespace app\index\controller;

use Parsedown;
use app\Request;
use Metowolf\Meting;
use inis\music\Music;
use app\index\controller\{Tool, Index};
use think\facade\{Cache, Config, Cookie, Session, Db};
use inis\utils\{File, FileDB, helper, markdown, Db as inisDb, Env, Image};
use Firebase\JWT\{JWT, ExpiredException, BeforeValidException, SignatureInvalidException};
use app\model\mysql\{Log, Tag, Users, Links, Placard, Article, Options, Comments, LinksSort, VerifyCode, ArticleSort, Music as MusicModel};

use app\model\sqlite\{Options as iOptions, Log as iLog};

use QL\QueryList;
use Jaeger\GuzzleHttp;
use GuzzleHttp\Client;

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
        
        header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
		header('Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH');
    }
    
    public function index(Request $request)
    {
        $data = [];
        
        
        
        
        
        
        
        
        
        
        // 实例化客户端
        // $client = new Client();
     
        // $url  = 'https://api.gmit.vip/Api/WaSentence';
     
        // $data = $client->request('GET', $url)->getBody()->getContents();
        // $data = json_decode($data);
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        // $env  = new Env('../.env');
        // $data = $env->get('database.hostname');
        
        
        
        
        
        
        
        
        
        
        // 获取数据中所有表的名称
        // $data = Db::query("show tables");
        
        // 获取数据库某个表的所有字段名称
        // $data = Db::query("show COLUMNS FROM inis_article");
        
        // $sqlite = Db::connect('sqlite');
        
        // $data = $sqlite->execute('CREATE TABLE log(
        //   "id" integer PRIMARY KEY AUTOINCREMENT,
        //   "type" text,
        //   "ip" text,
        //   "content" text,
        //   "message" text,
        //   "opt" text,
        //   "expand" text,
        //   "longtext" text,
        //   "create_time" text,
        //   "update_time" text
        // );');
        
        // $data = $sqlite->table('options')->select();
        
        // $data = (new iLog)->save([
        //     'type'=> 'test1',
        //     'opt' => json_encode([
        //         'numb'=>[1,2,3,4,5],
        //         'json'=>[
        //             'a'=>'a',
        //             'b'=>'b',
        //             'c'=>[
        //                 'c1'=>1,
        //                 'c2'=>2
        //             ]
        //         ]
        //     ], JSON_UNESCAPED_UNICODE)
        // ]);
        
        // $data = iLog::select();
        
        // 获取数据中所有表的名称
        // $data = $sqlite->getTables();
        
        // --查看所有表结构信息 
        // $data = $sqlite->query('select * from sqlite_master where type="table"');
        
        // $data = $sqlite->table('sqlite_sequence')->select();
        
        // return json($data);
        
        
        
        // $icmp_socket = socket_create(AF_INET, SOCK_RAW, 1);
        // socket_set_block($icmp_socket);
        // socket_set_option($icmp_socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 1, "usec" => 0));
    
        // /* connect to socket */
        // socket_connect($icmp_socket, $host, null);
    
        // /* 创建一个发送包*/
        // $request = "\x08\x00\x19\x2f\x00\x00\x00\x00\x70\x69\x6e\x67";
    
        // socket_send($icmp_socket, $request, strlen($request), 0);
    
        // if (@socket_read($icmp_socket, 255)) {
        //     return true;
        // } else {
        //     return false;
        // }
        // socket_close($icmp_socket);
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        // $FileDB = new FileDB('t1');
        
        // 查询
        // $data = $FileDB->select();
        // $data = FileDB::table('t1') ->select();
        // // 单条插入
        // $data = FileDB::table('t1')->insert(['a' => 1232, 'dt' => date('Y-m-d H:i:s'), 'rand' => rand(1, 100),'array'=>[1,2,3,4,5]]);
        // // 批量插入
        // $data = FileDB::table('t1')->insertAll([
        //     ['a' => 123, 'dt' => date('Y-m-d H:i:s'), 'rand' => rand(1, 100)],
        //     ['a' => 124, 'dt' => date('Y-m-d H:i:s'), 'rand' => rand(1, 100)],
        // ]);
        // // 更新
        // $data = FileDB::table('t1')->where('id', '=', '1')->update(['c' => 200]);
        // // 删除
        // $data = FileDB::table('t1')->where('id', '=', '3')->delete();
        // // 单条查询
        // $data = FileDB::table('t1')->find(2);
        // // 排序
        // $data = FileDB::table('t1')->order('rand DESC')->select();
        
        // return json($data);
        
        
        
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
        
        return json($data);
    }
}