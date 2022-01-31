<?php
// +----------------------------------------------------------------------
// | 在线更新 方法
// +----------------------------------------------------------------------
// | 负责系统更新和修复
// +----------------------------------------------------------------------

namespace app\index\controller;

use app\Request;
use think\facade\{Db};
use inis\utils\{File};

class Update extends Base
{
    
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
            
            return $this->create($data, $msg, $code);
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
            
            $data  = Db::getTables();
            
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
            
            // 下载文件到网站根目录
            (new File)->downloadFile($url, '../', 'inis update.zip');
            
            return $this->create($data, $msg, $code);
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
            
            return $this->create($data, $msg, $code);
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
            
            return $this->create($data, $msg, $code);
        }
    }
    
    // 下载文件到本地服务器
    public function downloadSqlite(Request $request)
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