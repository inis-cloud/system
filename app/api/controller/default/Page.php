<?php
declare (strict_types = 1);

namespace app\api\controller\default;

use Parsedown;
use think\Request;
use think\facade\{Cache, Lang};
use inis\utils\{markdown};
use app\validate\{Page as vPage};
use think\exception\ValidateException;
use app\model\mysql\{Visit, Page as PageModel};

class Page extends Base
{
    /**
     * 显示资源列表
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function index(Request $request)
    {
        // 获取请求参数
        $param  = $request->param();
        
        $data   = [];
        $code   = 400;
        $msg    = lang('参数不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['one','all'];
        
        $mode   = (empty($param['id']) and empty($param['alias'])) ? 'all' : 'one';
        
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
        $code   = 400;
        $msg    = lang('参数不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['save','remove'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        // 清除缓存
        Cache::tag('page')->clear();
        
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
        $data   = [];
        $code   = 400;
        $msg    = lang('参数不存在！');
        $result = [];
        
        // 获取请求参数
        $param = $request->param();
        
        // 存在的方法
        $method = ['all','one','sql'];
        
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
        $msg    = lang('参数不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['save'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        // 清除缓存
        Cache::tag('page')->clear();
        
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
        $msg    = lang('参数不存在！');
        $result = [];
        
        // 存在的方法
        $method = ['remove'];
        
        // 动态方法且方法存在
        if (in_array($IID, $method)) $result = $this->$IID($param);
        // 动态返回结果
        if (!empty($result)) foreach ($result as $key => $val) $$key = $val;
        
        // 清除缓存
        Cache::tag('page')->clear();
        
        return $this->json($data, $msg, $code);
    }
    
    // 获取一条数据
    public function one($param)
    {
        $data = [];
        $code = 400;
        $msg  = lang('无数据！');
        
        $id    = (!empty($param['id']))    ? $param['id']    : null;
        $alias = (!empty($param['alias'])) ? $param['alias'] : null;
        $parse = (!empty($param['mode']))  ? $param['mode']  : 'html';

        if (empty($id) and empty($alias)) return ['data'=>$data,'code'=>$code,'msg'=>lang('id 或 alias 至少有一个不能为空！')];
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'page'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            
            $check = PageModel::whereOr(['id'=>$id,'alias'=>$alias])->findOrEmpty();
            if ($check->isEmpty()) $data = [];
            else {
                
                // 获取数据库数据
                if (!empty($id)) $data = PageModel::ExpandAll($id);
                else $data = PageModel::ExpandAll(null, ['where'=>['alias'=>$alias]])['data'][0];
                
                // setBreaksEnabled(true) 自动换行 setMarkupEscaped(true) 转义HTML setUrlsLinked(false) 防止自动链接
                if ($parse == 'html' || $parse == 'htm') $data['content'] = Parsedown::instance()->setUrlsLinked(false)->text($data['content']);
                // 解析自定义标签
                $data['content'] = markdown::parse($data['content']);
            }
            if ($this->ApiCache) Cache::tag(['page',$cache_name])->set($cache_name, json_encode($data));
        }
        
        // 浏览量自增
        $this->visit($param);
        
        $code = 200;
        $msg  = lang('无数据！');
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = lang('成功！');
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 获取全部数据
    public function all($param)
    {
        $data = [];
        $code = 400;
        $msg  = lang('无数据！');
        
        if (empty($param['page']))  $param['page']  = 1;
        if (empty($param['limit'])) $param['limit'] = 5;
        if (empty($param['order'])) $param['order'] = 'create_time asc';
        
        $opt = [
            'page'   =>  (int)$param['page'], 
            'limit'  =>  (int)$param['limit'],
            'order'  =>  (string)$param['order'],
            'withoutField'=>['content']
        ];
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'page'], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            
            // 获取数据库数据
            $data = PageModel::ExpandAll(null, $opt);
            if ($this->ApiCache) Cache::tag(['page'])->set($cache_name, json_encode($data));
        }
        
        $code = 200;
        $msg  = lang('无数据！');
        // 逆向思维，节省代码行数
        if (empty($data)) $code = 204;
        else $msg = lang('成功！');
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }
    
    // 记录浏览量
    public function visit($param)
    {
        if (!empty($param['alias'])) $page = PageModel::where(['alias'=>$param['alias']])->field(['id'])->findOrEmpty();
        
        $id = (!empty($param['id'])) ? (int)$param['id'] : $page->id;
        
        $today = strtotime(date('Y-m-d',time()));
        
        $visit = Visit::where(['create_time'=>$today])->findOrEmpty();
        if ($visit->isEmpty()) {
            $visit = new Visit;
            $visit->opt = json_encode(['article'=>[],'page'=>[]]);
            $visit->json_time = $today;
        }
        
        if (!empty($visit->opt)) $opt = json_decode($visit->opt);
        
        if (!isset($opt->page)) $opt->page = [['id'=>$id,'visit'=>1]];
        else {
            
            if ($this->helper->InArray(['id',$id], $opt->page)) foreach ($opt->page as $key => $val) {
                if ($val->id == $id) $val->visit += 1;
            } else $opt->page[] = ['id'=>$id,'visit'=>1];
        }
        
        $visit->opt = json_encode($opt);
        
        $visit->save();
    }
    
    // SQL接口
    public function sql($param)
    {
        $where   = (empty($param['where']))   ? [] : $param['where'];
        $whereOr = (empty($param['whereOr'])) ? [] : $param['whereOr'];
        $page    = (!empty($param['page']))   ? $param['page']  : 1;
        $limit   = (!empty($param['limit']))  ? $param['limit'] : 5;
        $order   = (!empty($param['order']))  ? $param['order'] : 'create_time desc';
        
        $data = [];
        $code = 200;
        $msg  = lang('成功！');
        
        $opt  = [
            'page' => $page,
            'limit'=> $limit,
            'order'=> $order,
            'where'=> $where,
            'whereOr'=> $whereOr,
        ];
        
        // 设置缓存名称
        $cache_name = json_encode(array_merge(['IAPI'=>'page/sql','where'=>$where,'whereOr'=>$whereOr], $param));
        
        // 检查是否存在请求的缓存数据
        if (Cache::has($cache_name) and $this->ApiCache) $data = json_decode(Cache::get($cache_name));
        else {
            $data = PageModel::ExpandAll(null, $opt);
            if ($this->ApiCache) Cache::tag(['page',$cache_name])->set($cache_name, json_encode($data));
        }
        
        return ['data'=>$data,'code'=>$code,'msg'=>$msg];
    }

    // 新增或者修改数据
    public function save($param)
    {
        $data   = [];
        $code   = 400;
        $msg    = lang('成功！');
        
        // 允许用户提交并存储的字段
        $obtain = ['title','alias','content','is_show','opt','longtext'];

        if (empty($param['id'])) $item = new PageModel;
        else {
            $item = PageModel::findOrEmpty((int)$param['id']);
            if ($item->isEmpty()) return ['data'=>[],'code'=>204,'msg'=>lang('无数据！')];
        }

        try {
                
            validate(vPage::class)->check($param);

            // 字符对象
            if (!empty($param['opt'])) {
                // 校验字符串对象合法性
                $param['opt'] = is_string($param['opt']) ? $this->helper->stringJson($param['opt']) : $param['opt'];
                // 不编码中文
                $param['opt'] = json_encode($param['opt'], JSON_UNESCAPED_UNICODE);
            }

            // 存储数据
            foreach ($param as $key => $val) {
                // 判断字段是否允许存储，防提权
                if (in_array($key, $obtain)) $item->$key = $val;
            }

            // 权限判断
            if (!in_array(request()->user->level, ['admin'])) $msg = lang('无权限！');
            else if (request()->user->status != 1) $msg = lang('账号被禁用！');
            else {
                
                $item->save();
                $code = 200;
                $data = (int)$item->id;
            }
            
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $msg  = $e->getError();
        }
        
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }

    // 删除数据
    public function remove($param)
    {
        $data = [];
        $code = 400;
        $msg  = lang('成功！');
        
        $id = !empty($param['id']) ? $param['id']  : null;
        
        if (empty($id)) $msg = lang('请提交 id！');
        else {
            
            $id = array_filter(explode(',', $id));

            // 权限判断
            if (!in_array(request()->user->level, ['admin'])) $msg = lang('无权限！');
            else if (request()->user->status != 1) $msg = lang('账号被禁用！');
            else {
                $code = 200;
                PageModel::destroy($id);
            }
        }
        
        return ['data'=>$data,'msg'=>$msg,'code'=>$code];
    }
}
