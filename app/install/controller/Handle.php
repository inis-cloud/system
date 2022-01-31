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
                        $data['version'] = explode('-', $conn->server_info)[0];
                        $data['check']   = ($this->helper->VersionCompare(explode('-', $conn->server_info)[0], '5.5') >= 0) ? true : false;
                        
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
        
        $text = "APP_DEBUG = false\n\n[APP]\nDEFAULT_TIMEZONE = Asia/Shanghai\n\n[DATABASE]\nTYPE     = mysql\nHOSTNAME = $HOSTNAME\nDATABASE = $DATABASE\nUSERNAME = $USERNAME\nPASSWORD = $PASSWORD\nHOSTPORT = $HOSTPORT\nCHARSET  = utf8mb4\nDEBUG    = true\nPREFIX   = inis_\n\n[LANG]\ndefault_lang = zh-cn\n\n[WEB]\nAPPCODE = inisblog\nKEY = aisfucasoiasdadwa_inis\nISS = INIS\nAUD = YUEQING\nEXPIRE = 14400\n";
        
        $env = $this->File->writeFile($env_file_path, $text);
    }
    
    // 执行安装
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
    
    // 创建数据库表
    public function createTable(Request $request)
    {
        if ($request->isPost())
        {
            $data  = [];
            $code  = 400;
            $msg   = 'ok';
            
            $param = $request->param();
            
            try {
                
                // 如果表已存在，先删除该表
                if (in_array($param['table'], Db::getTables())) Db::execute('DROP TABLE '. $param['table']);
                
                // 创建表
                Db::execute($param['query']);
                // 重新设置自增起始值
                Db::execute("ALTER TABLE " . $param['table'] . " AUTO_INCREMENT=1;");
                
                $code = 200;
                
            } catch (\Exception $e) {
                
                $msg  = $e->getMessage();
            }
            
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
            $account['level']       = 'admin';
            $account['create_time'] = time();
            $account['update_time'] = time();
            $account['password']    = password_hash(md5($account['password']), PASSWORD_BCRYPT);
            $account['head_img']    = $this->helper->RandomImg("local", "storage/users/anime/");
            $user    = Db::name('users')->where(['id'=>1])->findOrEmpty();
            
            // 帐号已存在
            if (!empty($user)) $user->save($account);
            else Db::name('users')->save($account);
            
            return $this->create($data, $msg, $code);
        }
    }
    
    // 导入数据库表默认数据
    public function importData(Request $request)
    {
        if ($request->isPost())
        {
            $data  = [];
            $code  = 400;
            $msg   = 'ok';
            
            $param = $request->param();
            
            unset($param['name']);
            
            try {
                
                foreach ($param as $key => $val) {
                    // 清空表数据
                    if ($key != 'users') Db::execute('DELETE FROM inis_' . $key);
                    if ($key == 'options') {
                        // 空值处理
                        foreach ($val as $k => $v) if (empty($v['opt']))   $val[$k]['opt']   = null;
                        foreach ($val as $k => $v) if (empty($v['value'])) $val[$k]['value'] = null;
                        Db::name($key)->insertAll($val);
                    } else Db::name($key)->insertAll($val);
                }
                
                $code = 200;
                $this->fulfill();
                
            } catch (\Exception $e) {
                
                $msg  = $e->getMessage();
            }
            
            return $this->create($data, $msg, $code);
        }
    }
    
    // 安装完成
    public function fulfill()
    {
        Cache::clear();
        $route_file_path = "../app/install/route/app.php";
        $text = "<?php\n\nuse think\\facade\\Route;";
        $this->File->unlinkFile('install.env');
        $this->File->writeFile($route_file_path, $text);
    }
    
    // sqlite数据库
    public function existSqlite(Request $request)
    {
        if ($request->isPost())
        {
            $data  = [];
            $code  = 400;
            $msg   = 'ok';
            
            $param = $request->param();
            
            $File  = new File;
            
            try {
                
                $data = $File->dirInfo('../extend/sqlite');
                
            } catch (\Exception $e) {
                
                $msg  = $e->getMessage();
            }
            
            // sqlite数据库不存在
            if (empty($data) or !in_array('inis.db', $data)) $code = 204;
            // sqlite数据库已存在
            else if (in_array('inis.db', $data)) $code = 200;
            
            return $this->create($data, $msg, $code);
        }
    }
    
    // 下载文件到本地服务器
    public function downloadFile(Request $request)
    {
        if ($request->isPost())
        {
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            $param= $request->param();
            
            $url  = (!empty($param['path'])) ? $param['path'] : null;
            
            // 下载文件
            (new File)->downloadFile($url, '../extend/sqlite', 'inis.db');
            
            return $this->create($data, $msg, $code);
        }
    }
    
    // 本地sqlite表信息
    public function sqliteTables(Request $request)
    {
        if ($request->isPost())
        {
            $data   = [];
            $code   = 200;
            $msg    = 'ok';
            
            $param  = $request->param();
            
            $sqlite = Db::connect('sqlite');
            
            // 查看当前数据库所有表
            $data   = $sqlite->getTables();
            // 删除 sqlite_sequence 表
            if (in_array('sqlite_sequence', $data)) foreach ($data as $key => $val) if ($val == 'sqlite_sequence') unset($data[$key]);
            
            return $this->create($data, $msg, $code);
        }
    }
    
    // 创建sqlite表
    public function createSqliteTable(Request $request)
    {
        if ($request->isPost())
        {
            $data   = [];
            $code   = 400;
            $msg    = 'ok';
            
            $param  = $request->param();
            $sqlite = Db::connect('sqlite');
            
            try {
                
                // 如果表已存在，先删除该表 - DROP TABLE database_name.table_name;
                if (in_array($param['table'], $sqlite->getTables())) Db::execute('DROP TABLE '. $param['table']);
                
                // 创建表
                $sqlite->execute($param['query']);
                // 重新设置自增起始值
                // $sqlite->execute("ALTER TABLE " . $param['table'] . " AUTO_INCREMENT=1;");
                
                $code = 200;
                
            } catch (\Exception $e) {
                
                $msg  = $e->getMessage();
            }
            
            return $this->create($data, $msg, $code);
        }
    }
}