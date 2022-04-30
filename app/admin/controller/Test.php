<?php
namespace app\admin\controller;

use Parsedown;
use app\Request;
use Metowolf\Meting;
use inis\music\Music;
use app\admin\controller\{Tool, Index};
use think\facade\{Cache, Config, Cookie, Session, Db, Log};
use inis\utils\{File, FileDB, helper, markdown, Db as inisDb, Env, Image, FileLog};
use Firebase\JWT\{JWT, ExpiredException, BeforeValidException, SignatureInvalidException};
use app\model\mysql\{mLog, Tag, Users, Links, Placard, Article, Options, Comments, LinksSort, VerifyCode, ArticleSort, Music as MusicModel, Banner};

use app\model\sqlite\{Options as iOptions, Log as iLog, Search};

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
        $this->File    = new File;
        $this->helper  = new helper;
        $this->FileLog = new FileLog;
        $this->config  = Config::get('inis');
        
        header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
		header('Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH');
    }
    
    public function index(Request $request)
    {
        $data = [];
        










        // // 初始化
        // $FileLog = new FileLog([
        //     // 日志文件路径
        //     'filePath' => app()->getRootPath() . 'runtime/storage/' . date('Y-m-d', time()) . '.log',
        // ]);

        // // 根据行查询
        // $data = $FileLog->line(1);
        // // 根据ID查询
        // $data = $FileLog->find(20);
        // // 查询单条并根据ID倒序排序
        // $data = $FileLog->order('id desc')->find();
        // // 查询多条并根据创建时间排序，限制10条且获取第二页数据
        // $data = $FileLog->order(['create_time'=>'desc'])->limit(10)->page(2)->select();
        // // where查询多条，获取ID不为空的数据
        // $data = $FileLog->where([['id','<>',null]])->select();
        // // 查询多条并排序
        // $data = $FileLog->where([['b','>=',2],['a','=',1]])->where('c','>=','10')->select();
        // // 更加复杂的高级查询
        // $data = $FileLog->where([['b','>=',2]])->whereOr([['a','=',1]])->order(['id'=>'desc'])->find();
        // // 难度逐渐升级
        // $data = $FileLog->where('id','>',200)->whereOr(['ip'=>'101.206.110.245'])->withAttr('expand', function($value, $data){
        //     return $data;
        // })->page(2)->limit(10)->order('id desc')->select();
        // // 插入一条数据，默认在文件末尾插入
        // $data = $FileLog->insert(['a'=>1, 'b'=>2, 'c'=>3]);
        // // 在文件开头插入数据
        // $data = $FileLog->insert(['name'=>'test'], 'start');
        // // 保存数据，单条 - 一维数组
        // $data = $FileLog->save(['name'=>'test']);
        // // 保存数据，多条 - 二维数组
        // $data = $FileLog->save([['name'=>'test'], ['name'=>'test']]);

        






        
        // $filePath = '../runtime/storage/202204/15.log';

        // $log = $this->FileSystem->path($filePath)->limit(3)->page(1)->select();
        
        // foreach ($log as $key => $val) {
        //     $item  = json_decode($val, true);
        //     $item['msg'] = json_decode($item['msg'], true);
        //     $data[] = $item;
        // }

        

        // 读取根目录的emoji.json文件
        // $data = $this->helper->get('https://api.inis.cn/emoji.json');
        
        // foreach ($data as $key => $val) {
        //     $this->File->downloadFile($val, './storage/random/emoji/qq');
        // }
        
        // Log::channel('api')->record(['test'=>'1','b'=>1], 'info');


        
        // 实例化客户端
        // $client = new Client();
     
        // $url  = 'https://api.gmit.vip/Api/WaSentence';
     
        // $data = $client->request('GET', $url)->getBody()->getContents();
        // $data = json_decode($data);
        
        
        
        
        // 获取数据中所有表的名称
        // $data = Db::query("show tables");
        
        // 获取数据库某个表的所有字段名称
        // $data = Db::query("show COLUMNS FROM inis_article");
        
        // 定义数据库连接为sqlite
        // $sqlite = Db::connect('sqlite');
        
        // // 创建log表
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
        
        // // 查询log表
        // $data = $sqlite->table('log')->select();
        
        
        
        
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
        
        
        
        
        
        
        
        
        
        
        
        // $env  = new Env('../.env');
        // $data = $env->get('database.hostname');
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
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