<?php

namespace app\install\controller;

use app\Request;
use app\BaseController;
use inis\utils\{File, helper};
use think\facade\{Db, Config, Session, Cache};

class Handle extends BaseController
{
    protected $File;
    protected $helper;
    protected $DBUPDATE;
    
    public function __construct()
    {
        $this->File     = new File;
        $this->helper   = new helper;
        $this->DBUPDATE = Config::get('dbupdate');

        $path = app()->getRootPath() . '/extend';
        $dir  = $this->File->getDir($path)['dir'];

        if (!in_array('sqlite', $dir)) $this->File->createDir($path . '/sqlite');
    }
    
    // 数据库连接测试
    public function testConn(Request $request)
    {
        if ($request->isPost())
        {
            $data  = [];
            $code  = 400;
            $msg   = '连接测试成功';
            
            $param = $request->param();
            
            $HOSTNAME = (!empty($param['HOSTNAME'])) ? $param['HOSTNAME'] : 'localhost';
            $HOSTPORT = (!empty($param['HOSTPORT'])) ? $param['HOSTPORT'] : '3306';
            $DATABASE = (!empty($param['DATABASE'])) ? $param['DATABASE'] : null;
            $USERNAME = (!empty($param['USERNAME'])) ? $param['USERNAME'] : null;
            $PASSWORD = (!empty($param['PASSWORD'])) ? $param['PASSWORD'] : null;
            
            if (empty($DATABASE) or empty($USERNAME) or empty($PASSWORD)) $msg = "数据库名称、数据库用户名、数据库密码 不得为空";
            else {
                
                try {
                    
                    $conn = mysqli_connect($HOSTNAME, $USERNAME, $PASSWORD, $DATABASE, $HOSTPORT);
                    
                    $data = [
                        'conn'   => false,
                        'version'=> false,
                        'check'  => false,
                    ];
                    
                    if ($conn) {
                        
                        $code = 200;
                        $data['conn']    = true;
                        $data['version'] = explode('-', $conn->server_info ?? '')[0];
                        $data['check']   = ($this->helper->VersionCompare(explode('-', $conn->server_info ?? '')[0], '5.5') >= 0) ? true : false;
                        
                        // 创建.$env
                        $this->createEnv($param);
                        
                    } else {
                        
                        $code = 403;
                        $msg  = '信息有误，连接测试未通过';
                    }
                    
                } catch (\Exception $e) {
                    
                    $code = 403;
                    $data = [
                        'conn'   => false,
                        'version'=> false,
                        'check'  => false,
                    ];
                    $msg  = '信息有误，连接测试未通过';
                }
            }
            
            return $this->create($data, $msg, $code);
        }
    }
    
    // 创建 .env 文件
    public function createEnv($param)
    {
        $HOSTNAME = (!empty($param['HOSTNAME'])) ? $param['HOSTNAME'] : 'localhost';
        $HOSTPORT = (!empty($param['HOSTPORT'])) ? $param['HOSTPORT'] : '3306';
        $DATABASE = (!empty($param['DATABASE'])) ? $param['DATABASE'] : null;
        $USERNAME = (!empty($param['USERNAME'])) ? $param['USERNAME'] : null;
        $PASSWORD = (!empty($param['PASSWORD'])) ? $param['PASSWORD'] : null;
        
        $env_file_path = "../.env";
        
        $text = "# 开发者模式\nAPP_DEBUG = false\n\n[APP]\n# 默认的网站应用\nDEFAULT_APP      = admin\n# 时区\nDEFAULT_TIMEZONE = Asia/Shanghai\n\n# 数据库配置\n[DATABASE]\n# 数据库类型\nTYPE     = mysql\n# 数据库地址\nHOSTNAME = $HOSTNAME\n# 数据库用户\nDATABASE = $DATABASE\n# 数据库名称\nUSERNAME = $USERNAME\n# 数据库密码\nPASSWORD = $PASSWORD\n# 数据库端口\nHOSTPORT = $HOSTPORT\n# 编码\nCHARSET  = utf8mb4\n# 调试模式\nDEBUG    = true\n# 数据库前缀\nPREFIX   = inis_\n\n[LANG]\n# 默认语言\ndefault_lang = zh-cn\n\n[WEB]\nAPPCODE = inisblog\nKEY = aisfucasoiasdadwa_inis\nISS = INIS\nAUD = YUEQING\nEXPIRE = 14400\n";
        
        $env = $this->File->writeFile($env_file_path, $text);
    }
    
    // 缓存帐号信息
    public function setCache(Request $request)
    {
        if ($request->isPost())
        {
            $data  = [];
            $code  = 200;
            $msg   = 'ok';
            
            $param = $request->param();
            
            unset($param['name']);
            
            Cache::set('account', json_encode($param, JSON_UNESCAPED_UNICODE), 7200);
            
            return $this->create($data, $msg, $code);
        }
    }
    
    // 创建管理员帐号
    public function createAdmin(Request $request)
    {
        if ($request->isPost())
        {
            $data  = [];
            $code  = 200;
            $msg   = 'ok';
            
            $param = $request->param();
            
            // 获取缓存中的帐号信息
            $account = json_decode(Cache::get('account'), true);
            // 设置默认信息
            $account = array_merge($account, [
                'level'       => 'admin',
                'create_time' => time(),
                'update_time' => time(),
                'password'    => password_hash(md5($account['password']), PASSWORD_BCRYPT),
                'head_img'    => $this->helper->RandomImg('local', 'admin/images/anime/'),
            ]);
            $item    = Db::name('users')->where(['account'=>$account['account']])->findOrEmpty();

            // 帐号已存在
            if (!empty($item)) Db::name('users')->where(['account'=>$account['account']])->update($account);
            else Db::name('users')->save($account);

            $this->fulfill();
            
            return $this->create($data, $msg, $code);
        }
    }
    
    // 安装完成
    public function fulfill()
    {
        Cache::clear();
        $route= "../app/install/route/app.php";
        $text = "<?php\n\nuse think\\facade\\Route;";
        $this->File->unlinkFile('install.env');
        $this->File->writeFile($route, $text);
    }
    
    // 批量建表
    public function createTables(Request $request)
    {
        if ($request->isPost())
        {
            $data  = [];
            $code  = 200;
            $msg   = 'ok';
            
            $param = $request->param();
            $db    = !empty($param['db']) ? $param['db'] : 'mysql';

            // 获取表信息
            $tabs  = $this->helper->get(config('inis.official.api') . $db . '/created');

            if ($tabs['code'] != 200) return $this->create([], $tabs['msg'], 400);

            // 数据库
            $conn = Db::connect($db);

            // SQL 语句
            $sqls = [];
            if (!empty($tabs['data'])) {

                // mysql 驱动
                if ($db == 'mysql') foreach ($tabs['data'] as $key => $val) {

                    // 如果表已存在，先删除该表
                    if (in_array($key, $conn->getTables())) $conn->execute('DROP TABLE '. $key);

                    $sqls[] = $val;
                    // 重新设置自增起始值
                    $sqls[] = "ALTER TABLE " . $key . " AUTO_INCREMENT=1;";
                }
                // sqlite 驱动
                else if ($db == 'sqlite') foreach ($tabs['data'] as $key => $val) {
                    
                    // 如果表已存在，先删除该表
                    if (in_array($key, $conn->getTables())) $conn->execute('DROP TABLE '. $key);

                    $sqls[] = $val;
                }
            }
            
            try {

                // 执行 SQL 语句
                if (!empty($sqls)) foreach ($sqls as $val) $conn->execute($val);
                
            } catch (\Exception $e) {
                
                $code = 400;
                $msg  = $e->getMessage();
            }

            return $this->create($data, $msg, $code);
        }
    }

    // 批量导入数据
    public function insertAll(Request $request)
    {
        if ($request->isPost())
        {
            $data  = [];
            $code  = 200;
            $msg   = 'ok';
            $param = $request->param();

            $db    = !empty($param['db']) ? $param['db'] : 'mysql';

            // 获取默认数据
            $result= $this->helper->get(config('inis.official.api') . $db . '/data');

            // 估计是没有权限 - 或者网络问题
            if ($result['code'] != 200) return $this->create([], $result['msg'], (int)$result['code']);

            // 先判断数据有没有
            if (!empty($result['data'])) foreach ($result['data'] as $key => $val) {
                // 批量插入数据
                Db::connect($db)->table($key)->insertAll($val);
            }

            return $this->create($result, $msg, $code);
        }
    }
}