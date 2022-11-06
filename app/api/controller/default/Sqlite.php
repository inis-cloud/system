<?php
declare (strict_types = 1);

namespace app\api\controller\default;

use think\facade\{Cache, Db};
use think\{Request, Response};

/**
 * Class Sqlite
 * @package app\api\controller
 */
class Sqlite extends Base 
{
    // 数据库驱动
    protected $db = 'sqlite';

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];
        // 存在的方法
        $method = [];

        $mode   = empty($param['id']) ? 'all' : 'one';

        // 动态方法且方法存在
        if (in_array($mode, $method)) $result = $this->$mode($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->json($data, $msg, $code);
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function IPOST(Request $request, $IID)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 500;
        $msg    = '参数不存在！';
        $result = [];

        if (request()->user['level'] != 'admin') return $this->json([], '无权限！', 403);

        // 存在的方法
        $method = ['create','save','insert','insertAll','execute'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->json($data, $msg, $code);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $IID
     * @return \think\Response
     */
    public function IGET(Request $request, $IID)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];
        
        // 存在的方法
        $method = ['has','tables','find','select','count','max','min','avg','sum','column','query'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->json($data, $msg, $code);
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $IID
     * @return \think\Response
     */
    public function IPUT(Request $request, $IID)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];

        if (request()->user['level'] != 'admin') return $this->json([], '无权限！', 403);
        
        // 存在的方法
        $method = ['update','delete'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->json($data, $msg, $code);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $IID
     * @return \think\Response
     */
    public function IDELETE(Request $request, $IID)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = '参数不存在！';
        $result = [];

        if (request()->user['level'] != 'admin') return $this->json([], '无权限！', 403);
        
        // 存在的方法
        $method = ['drop','delete'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        return $this->json($data, $msg, $code);
    }

    // 获取单条数据
    public function one($param)
    {
        $data   = [];
        $code   = 400;
        $msg    = '无数据！';

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 查询全部数据
    public function all($param)
    {
        $data = [];
        $code = 200;
        $msg  = '无数据！';

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 查询一条
    public function find($param)
    {
        $data = [];
        $code = 200;
        $msg  = '成功！';

        if (empty($param['table'])) return ['data'=>[],'code'=>400,'msg'=>'表名不能为空！'];

        $conn = Db::connect($this->db);
        // 查看当前数据库所有表
        $tabs = $conn->getTables();
        // 删除 sqlite_sequence 表
        if (in_array('sqlite_sequence', $tabs)) foreach ($tabs as $key => $val) if ($val == 'sqlite_sequence') unset($tabs[$key]);
        $tabs = array_merge($tabs);
        if (!in_array($param['table'], $tabs)) return ['data'=>false,'code'=>400,'msg'=>'表不存在！'];

        $item = $conn->table($param['table']);
        $item->field($param['field'] ?? '*');
        $item->order($param['order'] ?? 'id desc');
        $item->where($param['where'] ?? []);
        $item->whereOr($param['whereOr'] ?? []);
        $data = $item->find($param['id'] ?? null);

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 列查询
    public function column($param)
    {
        $data = [];
        $code = 200;
        $msg  = '成功！';

        if (empty($param['table'])) return ['data'=>[],'code'=>400,'msg'=>'表名不能为空！'];

        $conn = Db::connect($this->db);
        // 查看当前数据库所有表
        $tabs = $conn->getTables();
        // 删除 sqlite_sequence 表
        if (in_array('sqlite_sequence', $tabs)) foreach ($tabs as $key => $val) if ($val == 'sqlite_sequence') unset($tabs[$key]);
        $tabs = array_merge($tabs);
        if (!in_array($param['table'], $tabs)) return ['data'=>false,'code'=>400,'msg'=>'表不存在！'];

        $item = $conn->table($param['table']);
        $item->where($param['where'] ?? []);
        $item->whereOr($param['whereOr'] ?? []);
        $item->order($param['order'] ?? 'id desc');
        $data = $item->column(!empty($param['field']) ? $param['field'] : '*');

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 查询数量
    public function count($param)
    {
        $data = [];
        $code = 200;
        $msg  = '成功！';

        if (empty($param['table'])) return ['data'=>[],'code'=>400,'msg'=>'表名不能为空！'];

        $conn = Db::connect($this->db);
        // 查看当前数据库所有表
        $tabs = $conn->getTables();
        // 删除 sqlite_sequence 表
        if (in_array('sqlite_sequence', $tabs)) foreach ($tabs as $key => $val) if ($val == 'sqlite_sequence') unset($tabs[$key]);
        $tabs = array_merge($tabs);
        if (!in_array($param['table'], $tabs)) return ['data'=>false,'code'=>400,'msg'=>'表不存在！'];

        $item = $conn->table($param['table']);
        $item->where($param['where'] ?? []);
        $item->whereOr($param['whereOr'] ?? []);
        $data = $item->count($param['field'] ?? '*');

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 最大值查询
    public function max($param)
    {
        $data = [];
        $code = 200;
        $msg  = '成功！';

        if (empty($param['table'])) return ['data'=>[],'code'=>400,'msg'=>'表名不能为空！'];

        $conn = Db::connect($this->db);
        // 查看当前数据库所有表
        $tabs = $conn->getTables();
        // 删除 sqlite_sequence 表
        if (in_array('sqlite_sequence', $tabs)) foreach ($tabs as $key => $val) if ($val == 'sqlite_sequence') unset($tabs[$key]);
        $tabs = array_merge($tabs);
        if (!in_array($param['table'], $tabs)) return ['data'=>false,'code'=>400,'msg'=>'表不存在！'];

        $item = $conn->table($param['table']);
        $item->where($param['where'] ?? []);
        $item->whereOr($param['whereOr'] ?? []);
        $data = $item->max($param['field']);

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 最小值查询
    public function min($param)
    {
        $data = [];
        $code = 200;
        $msg  = '成功！';

        if (empty($param['table'])) return ['data'=>[],'code'=>400,'msg'=>'表名不能为空！'];

        $conn = Db::connect($this->db);
        // 查看当前数据库所有表
        $tabs = $conn->getTables();
        // 删除 sqlite_sequence 表
        if (in_array('sqlite_sequence', $tabs)) foreach ($tabs as $key => $val) if ($val == 'sqlite_sequence') unset($tabs[$key]);
        $tabs = array_merge($tabs);
        if (!in_array($param['table'], $tabs)) return ['data'=>false,'code'=>400,'msg'=>'表不存在！'];

        $item = $conn->table($param['table']);
        $item->where($param['where'] ?? []);
        $item->whereOr($param['whereOr'] ?? []);
        $data = $item->min($param['field']);

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 求平均值
    public function avg($param)
    {
        $data = [];
        $code = 200;
        $msg  = '成功！';

        if (empty($param['table'])) return ['data'=>[],'code'=>400,'msg'=>'表名不能为空！'];

        $conn = Db::connect($this->db);
        // 查看当前数据库所有表
        $tabs = $conn->getTables();
        // 删除 sqlite_sequence 表
        if (in_array('sqlite_sequence', $tabs)) foreach ($tabs as $key => $val) if ($val == 'sqlite_sequence') unset($tabs[$key]);
        $tabs = array_merge($tabs);
        if (!in_array($param['table'], $tabs)) return ['data'=>false,'code'=>400,'msg'=>'表不存在！'];

        $item = $conn->table($param['table']);
        $item->where($param['where'] ?? []);
        $item->whereOr($param['whereOr'] ?? []);
        $data = $item->avg($param['field']);

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 求和
    public function sum($param)
    {
        $data = [];
        $code = 200;
        $msg  = '成功！';

        if (empty($param['table'])) return ['data'=>[],'code'=>400,'msg'=>'表名不能为空！'];

        $conn = Db::connect($this->db);
        // 查看当前数据库所有表
        $tabs = $conn->getTables();
        // 删除 sqlite_sequence 表
        if (in_array('sqlite_sequence', $tabs)) foreach ($tabs as $key => $val) if ($val == 'sqlite_sequence') unset($tabs[$key]);
        $tabs = array_merge($tabs);
        if (!in_array($param['table'], $tabs)) return ['data'=>false,'code'=>400,'msg'=>'表不存在！'];

        $item = $conn->table($param['table']);
        $item->where($param['where'] ?? []);
        $item->whereOr($param['whereOr'] ?? []);
        $data = $item->sum($param['field']);

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 查询多条
    public function select($param)
    {
        $data = [];
        $code = 200;
        $msg  = '成功！';

        if (empty($param['table'])) return ['data'=>[],'code'=>400,'msg'=>'表名不能为空！'];

        $conn = Db::connect($this->db);
        // 查看当前数据库所有表
        $tabs = $conn->getTables();
        // 删除 sqlite_sequence 表
        if (in_array('sqlite_sequence', $tabs)) foreach ($tabs as $key => $val) if ($val == 'sqlite_sequence') unset($tabs[$key]);
        $tabs = array_merge($tabs);
        if (!in_array($param['table'], $tabs)) return ['data'=>false,'code'=>400,'msg'=>'表不存在！'];

        $item = $conn->table($param['table']);
        $item->where($param['where'] ?? []);
        $item->whereOr($param['whereOr'] ?? []);
        $item->field($param['field'] ?? '*');
        $item->order($param['order'] ?? 'id desc');
        $item->page(!empty($param['page']) ? (int)$param['page'] : 1);
        $item->limit(!empty($param['limit']) ? (int)$param['limit'] : $item->count());

        $data = $item->select($param['id'] ?? null);

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 新增或修改数据
    public function save($param)
    {
        $data = [];
        $code = 200;
        $msg  = '成功！';

        if (empty($param['table'])) return ['data'=>[],'code'=>400,'msg'=>'表名不能为空！'];

        $conn = Db::connect($this->db);
        // 查看当前数据库所有表
        $tabs = $conn->getTables();
        // 删除 sqlite_sequence 表
        if (in_array('sqlite_sequence', $tabs)) foreach ($tabs as $key => $val) if ($val == 'sqlite_sequence') unset($tabs[$key]);
        $tabs = array_merge($tabs);
        if (!in_array($param['table'], $tabs)) return ['data'=>[],'code'=>400,'msg'=>'表不存在！'];
        if (empty($param['data'])) return ['data'=>[],'code'=>400,'msg'=>'数据不能为空！'];

        $data = $conn->table($param['table'])->strict(false)->save($param['data']);

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 更新数据
    public function update($param)
    {
        $data = [];
        $code = 200;
        $msg  = '成功！';

        if (empty($param['table'])) return ['data'=>[],'code'=>400,'msg'=>'表名不能为空！'];

        $conn = Db::connect($this->db);
        // 查看当前数据库所有表
        $tabs = $conn->getTables();
        // 删除 sqlite_sequence 表
        if (in_array('sqlite_sequence', $tabs)) foreach ($tabs as $key => $val) if ($val == 'sqlite_sequence') unset($tabs[$key]);
        $tabs = array_merge($tabs);
        if (!in_array($param['table'], $tabs)) return ['data'=>false,'code'=>400,'msg'=>'表不存在！'];

        $item = $conn->table($param['table']);
        $item->where($param['where'] ?? []);
        $item->whereOr($param['whereOr'] ?? []);
        $data = $item->update($param['data']);

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 插入数据
    public function insert($param)
    {
        $data = [];
        $code = 200;
        $msg  = '成功！';

        if (empty($param['table'])) return ['data'=>[],'code'=>400,'msg'=>'表名不能为空！'];

        $conn = Db::connect($this->db);
        // 查看当前数据库所有表
        $tabs = $conn->getTables();
        // 删除 sqlite_sequence 表
        if (in_array('sqlite_sequence', $tabs)) foreach ($tabs as $key => $val) if ($val == 'sqlite_sequence') unset($tabs[$key]);
        $tabs = array_merge($tabs);
        if (!in_array($param['table'], $tabs)) return ['data'=>[],'code'=>400,'msg'=>'表不存在！'];
        if (empty($param['data'])) return ['data'=>[],'code'=>400,'msg'=>'数据不能为空！'];

        $data = $conn->table($param['table'])->strict(false)->insertGetId($param['data']);

        return ['data'=>(int)$data,'code'=>$code,'msg'=>$msg];
    }

    // 批量插入数据
    public function insertAll($param)
    {
        $data = [];
        $code = 200;
        $msg  = '成功！';

        if (empty($param['table'])) return ['data'=>[],'code'=>400,'msg'=>'表名不能为空！'];
        $limit = !empty($param['limit']) ? (int)$param['limit'] : 100;

        $conn = Db::connect($this->db);
        // 查看当前数据库所有表
        $tabs = $conn->getTables();
        // 删除 sqlite_sequence 表
        if (in_array('sqlite_sequence', $tabs)) foreach ($tabs as $key => $val) if ($val == 'sqlite_sequence') unset($tabs[$key]);
        $tabs = array_merge($tabs);
        if (!in_array($param['table'], $tabs)) return ['data'=>[],'code'=>400,'msg'=>'表不存在！'];
        if (empty($param['data'])) return ['data'=>[],'code'=>400,'msg'=>'数据不能为空！'];

        $data = $conn->table($param['table'])->strict(false)->replace()->limit($limit)->insertAll($param['data']);

        return ['data'=>(int)$data,'code'=>$code,'msg'=>$msg];
    }

    // 删除数据
    public function delete($param)
    {
        $data = [];
        $code = 200;
        $msg  = '成功！';

        if (empty($param['table'])) return ['data'=>[],'code'=>400,'msg'=>'表名不能为空！'];

        $conn = Db::connect($this->db);
        // 查看当前数据库所有表
        $tabs = $conn->getTables();
        // 删除 sqlite_sequence 表
        if (in_array('sqlite_sequence', $tabs)) foreach ($tabs as $key => $val) if ($val == 'sqlite_sequence') unset($tabs[$key]);
        $tabs = array_merge($tabs);
        if (!in_array($param['table'], $tabs)) return ['data'=>false,'code'=>400,'msg'=>'表不存在！'];

        $item = $conn->table($param['table']);
        $item->where($param['where'] ?? []);
        $item->whereOr($param['whereOr'] ?? []);

        $data = $item->delete($param['id'] ?? null);
        
        $data = $data ? true : false;
        $code = $data ? 200 : 400;
        $msg  = $data ? '删除成功！' : '删除失败！';

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 获取表列表
    public function tables($param)
    {
        $data = [];
        $code = 200;
        $msg  = '请求数据成功！';

        $conn = Db::connect($this->db);
        // 查看当前数据库所有表
        $tabs = $conn->getTables();
        // 删除 sqlite_sequence 表
        if (in_array('sqlite_sequence', $tabs)) foreach ($tabs as $key => $val) if ($val == 'sqlite_sequence') unset($tabs[$key]);
        $data = array_merge($tabs);

        $code = !empty($data) ? 200 : 204;

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 判断表是否存在
    public function has($param)
    {
        $data = [];
        $code = 200;
        $msg  = '无数据！';

        if (empty($param['table'])) return ['data'=>[],'code'=>400,'msg'=>'表名不能为空！'];

        $conn = Db::connect($this->db);
        // 查看当前数据库所有表
        $tabs = $conn->getTables();
        // 删除 sqlite_sequence 表
        if (in_array('sqlite_sequence', $tabs)) foreach ($tabs as $key => $val) if ($val == 'sqlite_sequence') unset($tabs[$key]);
        $tabs = array_merge($tabs);
        if (!in_array($param['table'], $tabs)) return ['data'=>false,'code'=>400,'msg'=>'表不存在！'];

        $data = true;
        $msg  = '表存在！';

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 删除表
    public function drop($param)
    {
        $data = [];
        $code = 200;
        $msg  = '无数据！';

        if (empty($param['table'])) return ['data'=>[],'code'=>400,'msg'=>'表名不能为空！'];

        $conn = Db::connect($this->db);
        // 查看当前数据库所有表
        $tabs = $conn->getTables();
        // 删除 sqlite_sequence 表
        if (in_array('sqlite_sequence', $tabs)) foreach ($tabs as $key => $val) if ($val == 'sqlite_sequence') unset($tabs[$key]);
        $tabs = array_merge($tabs);
        if (!in_array($param['table'], $tabs)) return ['data'=>false,'code'=>400,'msg'=>'表不存在！'];

        $result = $conn->execute('DROP TABLE ' . $param['table']);

        if ($result == 0) {
            $data = true;
            $msg  = '表删除成功！';
        } else {
            $data = false;
            $code = 400;
            $msg  = '表删除失败！';
        }

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 原生查询
    public function query($param)
    {
        $data = [];
        $code = 200;
        $msg  = '成功！';

        if (empty($param['sql'])) return ['data'=>[],'code'=>400,'msg'=>'请提交需要执行的 SQL 语句！'];
        $params = !empty($param['params']) ? $param['params'] : [];

        $conn = Db::connect($this->db);

        try {

            $data = $conn->query($param['sql'], $params);
            $code = !empty($data) ? 200 : 204;
            $msg  = !empty($data) ? '执行成功！' : '无数据！';

        } catch (\Throwable $th) {

            $data = [];
            $code = 400;
            $msg  = $th->getMessage();
        }

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 原生执行
    public function execute($param)
    {
        $data = [];
        $code = 200;
        $msg  = '成功！';

        if (empty($param['sql'])) return ['data'=>[],'code'=>400,'msg'=>'请提交需要执行的 SQL 语句！'];
        $params = !empty($param['params']) ? $param['params'] : [];

        $conn = Db::connect($this->db);

        try {

            $data = $conn->execute($param['sql'], $params);
            $data = $data ? true : false;
            $code = $data ? 200 : 400;
            $msg  = $data ? '执行成功！' : '执行失败！';

        } catch (\Throwable $th) {

            $data = [];
            $code = 400;
            $msg  = $th->getMessage();
        }

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 创建表
    public function create($param)
    {
        $data = [];
        $code = 200;
        $msg  = '请求数据成功！';

        if (empty($param['table'])) return ['data'=>[],'code'=>400,'msg'=>'表名不能为空！'];
        if (!empty($param['field']) and !is_array($param['field'])) return ['data'=>[],'code'=>400,'msg'=>'字段格式不正确！'];
        if (empty($param['field'])) $param['field'] = [];

        $fieldStart = [
            'id'          => 'INTEGER PRIMARY KEY AUTOINCREMENT',
        ];
        $fieldEnd   = [
            'create_time' => 'TEXT',
            'update_time' => 'TEXT',
        ];
        $field = array_merge($fieldStart, $param['field'], $fieldEnd);

        $table = $param['table'];
        $query = "CREATE TABLE IF NOT EXISTS $table (";
        foreach ($field as $key => $val) $query .= "$key $val,";
        $query = rtrim($query, ',');
        $query .= ");";

        $conn   = Db::connect($this->db);
        $result = $conn->execute($query);

        if ($result == 0) {

            $data = true;
            $msg  = '表创建成功！';

        } else {

            $data = false;
            $code = 400;
            $msg  = '表创建失败！';
        }

        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
}