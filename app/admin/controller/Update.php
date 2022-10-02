<?php
// +----------------------------------------------------------------------
// | 在线更新 方法
// +----------------------------------------------------------------------
// | 负责系统更新和修复
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\Request;
use think\facade\{Db};
use inis\utils\{File, helper};

class Update extends Base
{

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
            $tables= !empty($param['tables']) ? $param['tables'] : null;

            // 获取表信息
            $tabs  = $this->helper->get(config('inis.official.api') . $db . '/created', [
                'tables' => $tables,
            ]);

            if ($tabs['code'] != 200) return $this->json([], $tabs['msg'], 400);

            // 数据库
            $conn = Db::connect($db);

            // SQL 语句
            $sqls = [];
            if (!empty($tabs['data']) and !empty($tables)) {

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
            
            return $this->json($data, $msg, $code);
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
            $tables= !empty($param['tables']) ? $param['tables'] : null;

            if (empty($tables)) return $this->json($data, $msg, $code);

            // 获取默认数据
            $result= $this->helper->get(config('inis.official.api') . $db . '/data', [
                'tables' => $tables,
            ]);

            // 估计是没有权限 - 或者网络问题
            if ($result['code'] != 200) return $this->json([], $result['msg'], (int)$result['code']);

            // 先判断数据有没有
            if (!empty($result['data'])) foreach ($result['data'] as $key => $val) {
                // 批量插入数据
                Db::connect($db)->table($key)->insertAll($val);
            }

            return $this->json($data, $msg, $code);
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
            
            // 下载文件到网站根目录
            (new File)->downloadFile($url, '../', 'inis update.zip');
            
            return $this->json($data, $msg, $code);
        }
    }
    
    // 解压文件
    public function unzipFile(Request $request)
    {
        if ($request->isPost())
        {
            $data = [];
            $code = 200;
            $msg  = 'ok';
            
            $zip  = new \ZipArchive;
            
            $path = '../inis update.zip';
            
            if ($zip->open($path) === true) {
                
                // 将压缩包文件解压到根目录下
                $zip->extractTo('../');
                
                // 关闭zip文件
                $zip->close();
            }
            
            // 删除文件
            (new File)->unlinkFile($path);
            
            return $this->json($data, $msg, $code);
        }
    }

    // 本地存在的表
    public function tables(Request $request)
    {
        if ($request->isPost())
        {
            $data  = [];
            $code  = 200;
            $msg   = 'ok';

            $param = $request->param();

            $db    = !empty($param['db']) ? $param['db'] : 'mysql';
            $conn  = Db::connect($db);
            // 查看当前数据库所有表
            $data  = $conn->getTables();
            
            return $this->json($data, $msg, $code);
        }
    }

    //  ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ 【 1.9.0 可删 】 ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓

    // 暴露本地信息
    public function info(Request $request)
    {
        if ($request->isPost())
        {
            $data  = [];
            $code  = 200;
            $msg   = 'ok';
            
            $data['official']= !empty($this->config['official']) ? $this->config['official'] : ['api'=>'https://inis.cc/api/',];
            $data['version'] = !empty($this->config['version'])  ? $this->config['version']  : '1.0';
            
            return $this->json($data, $msg, $code);
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
            
            return $this->json($data, $msg, $code);
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
            
            $tables = !empty($param['tables']) ? (array)$param['tables'] : [];
            // 过滤表名前缀
            if (!empty($tables)) foreach ($tables as $key => $val) $tables[$key] = str_replace('inis_', '', $val);
            
            try {
                
                foreach ($param['data'] as $key => $val) {
                    
                    // 对新增的表导入数据
                    if (!empty(($tables))) if (in_array($key, $tables)) {
                        
                        if ($key !== 'options') Db::name($key)->insertAll($val);
                    }
                    // 对options表更新数据
                    if ($key == 'options') {
                        
                        foreach ($val as $k => $v) {
                            $item = Db::name('options')->where(['keys'=>$v['keys']])->findOrEmpty();
                            // 数据不存在
                            if (empty($item)) Db::name('options')->insert($v);
                        }
                    }
                }
                
                $code = 200;
                
            } catch (\Exception $e) {
                
                $msg  = $e->getMessage();
            }
            
            return $this->json($data, $msg, $code);
        }
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
            
            return $this->json($data, $msg, $code);
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
            
            return $this->json($data, $msg, $code);
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
            
            return $this->json($data, $msg, $code);
        }
    }
}