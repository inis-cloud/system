<?php

namespace app\install\controller;

use app\Request;
use inis\utils\File;
use think\facade\Db;
use app\BaseController;
use think\facade\Config;
use think\facade\Session;

class Handle extends BaseController
{
    protected $File;
    protected $DBUPDATE;
    
    public function __construct()
    {
        $this->File     = new File;
        $this->DBUPDATE = Config::get('dbupdate');
    }
    
    // 创建 .env 文件
    public function createEnv(Request $request)
    {
        if ($request->isPost())
        {
            $data = [];
            $code = 400;
            $msg  = 'ok';
            
            $param = $request->param();
            
            $HOSTNAME = (!empty($param['HOSTNAME'])) ? $param['HOSTNAME'] : 'localhost';
            $HOSTPORT = (!empty($param['HOSTPORT'])) ? $param['HOSTPORT'] : '3306';
            $DATABASE = (!empty($param['DATABASE'])) ? $param['DATABASE'] : null;
            $USERNAME = (!empty($param['USERNAME'])) ? $param['USERNAME'] : null;
            $PASSWORD = (!empty($param['PASSWORD'])) ? $param['PASSWORD'] : null;
            
            if (empty($DATABASE) or empty($USERNAME) or empty($PASSWORD)) $code = 400;
            else {
                
                $env_file_path = "../.env";
                
                $text = "APP_DEBUG = false\n\n[APP]\nDEFAULT_TIMEZONE = Asia/Shanghai\n\n[DATABASE]\nTYPE     = mysql\nHOSTNAME = $HOSTNAME\nDATABASE = $DATABASE\nUSERNAME = $USERNAME\nPASSWORD = $PASSWORD\nHOSTPORT = $HOSTPORT\nCHARSET  = utf8mb4\nDEBUG    = true\nPREFIX   = inis_\n\n[LANG]\ndefault_lang = zh-cn\n\n[WEB]\nAPPCODE = inisblog\nKEY = aisfucasoiasdadwa_inis\nISS = INIS\nAUD = YUEQING\nEXPIRE = 14400\n";
                
                $env = $this->File->writeFile($env_file_path, $text);
                
                if ($env) $code = 200;
            }
            
            return $this->create($data,$code,$msg);
        }
    }
    
    // 下载数据库文件
    public function downloadDb(Request $request)
    {
        if ($request->isPost())
        {
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            $param = $request->param();
            
            $url = (!empty($param['file_path'])) ? $param['file_path'] : null;
            // 先删除原来的数据库文件
            $this->File->unlinkFile('storage/runtime/db.sql');
            $this->File->download($url,'storage/runtime','db.sql');
            
            return $this->create($data,$code,$msg);
        }
    }
    
    // 导入数据库
    public function importDb(Request $request)
    {
        if ($request->isPost())
        {
            $data = [];
            $code = 400;
            $msg  = '导入数据库成功';
            
            $db_file= 'storage/runtime/db.sql';
            $route_file_path = "../app/install/route/app.php";
            $text = "<?php\n\nuse think\\facade\\Route;";
            
            // 格式化数据结构
            $sql_query   = $this->processSqlFile($db_file);
            
            try {
                
                $DBUPDATE = $this->DBUPDATE;
                
                // 导入数据库
                foreach ($sql_query as $val) Db::execute($val.';');
                // 导入额外表
                // if (!empty($DBUPDATE)) if (!empty($DBUPDATE['query'])) foreach ($DBUPDATE['query'] as $val) Db::execute($val);
                // 导入数据
                if (!empty($DBUPDATE)) if (!empty($DBUPDATE['data'])) foreach ($DBUPDATE['data'] as $key => $val) Db::name($key)->limit(1)->insertAll($val);
                
                $this->File->unlinkFile($db_file);
                $this->File->unlinkFile('install.env');
                $this->File->unlinkFile('../config/dbupdate.php');
                $this->File->writeFile($route_file_path, $text);
                
                $code = 200;
                
            } catch (\Exception $e) {
                $msg = $e->getMessage();
            }
            
            return $this->create($data,$code,$msg);
        }
    }
    
    // 处理sql文件 - 格式化数据
    public function processSqlFile($sql_file_path)
    {
        $num    = 0;
        $result = ['sql文件地址不能为空'];
        
        if (!empty($sql_file_path)) {
            
            // 读取文件
            $sql_data  = $this->File->readFile($sql_file_path);
            $sql_data  = str_replace(["\r"], ["\n"], $sql_data);
            $sql_array = explode(";\n", trim($sql_data));
            
            foreach($sql_array as $query) {
                
                $result[$num] = '';
                $queries = explode("\n", trim($query));
                $queries = array_filter($queries);
                
                foreach($queries as $item) {
                    $str = substr($item, 0, 1);
                    if ($str != '#' && $str != '-') $result[$num] .= $item;
                }
                
                $num++;
            }
        }
        
        return $result;
    }
    
}