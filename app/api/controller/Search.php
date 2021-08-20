<?php
declare (strict_types = 1);

namespace app\api\controller;

use think\Request;
use app\model\Article;

class Search extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        // 获取请求参数
        $param = $request->param();
        
        if(empty($param['page']))  $param['page']  = 1;
        if(empty($param['limit'])) $param['limit'] = 5;
        if(empty($param['order'])) $param['order'] = 'create_time asc';
        
        $value   = (empty($param['value'])) ? '' : $param['value'];
        $sort_id = (empty($param['sort_id'])) ? '' : $param['sort_id'];
        
        $data = [];
        $msg  = 'ok';
        $code = 200;
        
        $opt  = [
            'page'  =>  (int)$param['page'],
            'limit' =>  (int)$param['limit'],
            'order' =>  (string)$param['order']
        ];
        
        $data = Article::search($value, $opt, (int)$sort_id);
        
        return $this->create($data, $msg, $code);
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
