<?php
namespace app\admin\controller;

use Parsedown;
use app\Request;
use Metowolf\Meting;
use inis\music\Music;
use app\admin\controller\{Tool, Index};
use think\facade\{Cache, Config, Cookie, Session, Db, Log};
use inis\utils\{File, FileDB, helper, markdown, Db as inisDb, Env, Image, FileLog, utils};
use Firebase\JWT\{JWT, ExpiredException, BeforeValidException, SignatureInvalidException};
use app\model\mysql\{mLog, Tag, Users, Links, Placard, Article, Options, Comments, LinksSort, VerifyCode, ArticleSort, Music as MusicModel, Banner};

use app\model\sqlite\{Options as iOptions, Log as iLog, Search};

use QL\QueryList;
use Jaeger\GuzzleHttp;
use GuzzleHttp\Client;

class Test
{
    // 构造器
    // public function __construct()
    // {
    //     $this->utils = new utils;
    // }
    
    public function index(Request $request)
    {
        $data = [];
        // 获取请求参数
        $param= $request->param();




        // $utils= $this->utils;
        // $url  = 'https://api.inis.cn/api/';

        // 新链式调用法：$utils->Type->Method(...$params);
        // $data[] = $utils->curl->get($url . 'links');
        // 原始调用法：$utils->TypeMethod(...$params);
        // $data[] = $utils->curlGet($url . 'links');

        // $data[] = $utils->curl->post($url . 'links', ['id' => 1], ['Content-Type'=>'application/json'], [CURLOPT_USERAGENT=>'UA']);
        // $data[] = $utils->curl->put($url . 'links');
        // $data[] = $utils->curl->delete($url . 'links');
        // $data[] = $utils->curl->patch($url . 'links');
        // $data[] = $utils->get->domain();
        // $data[] = $utils->rand->string(6, '0123456789');
        // $data[] = $utils->rand->array();
        // $data[] = $utils->rand->image('link', 'storage/random/images/img.txt');
        
        return json($data);
    }
}