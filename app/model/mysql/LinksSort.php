<?php

namespace app\model\mysql;

use think\Model;

class LinksSort extends Model
{
    // 封装拓展字段数据 - 返回全部
    public static function ExpandAll(int $id = null, array $opt = [])
    {
        // 第二参数默认配置
        $config = ['page'=>1,'limit'=>5,'order'=>'create_time desc','withoutField'=>[],'whereOr'=>[],'where'=>['is_show'=>1]];
        
        // 重组第二参数
        foreach ($config as $key => $val) if(!in_array($key,$opt)) $config[$key] = $val;
        foreach ($opt as $key => $val) $config[$key] = $val;
        
        // 分类总数量
        $count  = count(self::whereOr($config['whereOr'])->where($config['where'])->field(['id'])->select());
        $data['page']   = ceil($count/$config['limit']);
        $data['count']  = $count;
        
        // 防止分页请求超出页码
        if($config['page'] > $data['page']) $config['page'] = $data['page'];
        
        $sort = self::whereOr($config['whereOr'])->withAttr('expand', function ($value,$data){
            
            $data = Links::where(['sort_id'=>$data['id'],'is_show'=>1])->field(['id'])->select();
            
            // 随机颜色
            $arr = ['light','danger','dark','primary','success','info','warning'];
            $rnd = array_rand($arr);
            
            $value['count'] = sizeof($data);
            $value['color'] = $arr[$rnd];
            
            return $value;
            
        })->where($config['where'])->withoutField($config['withoutField'])->order($config['order'])->page($config['page'])->limit($config['limit'])->select($id);
        
        $data['data'] = $sort;
        
        // 只有单条数据
        if (!empty($id) and is_numeric($id)) $result = $sort[0];
        else if (is_array($id)) $result = $sort;
        else $result = $data;
        
        return $result;
    }
    
    // 关联分类下的友链 - 自定义一对多
    public static function links(int $id = null, array $opt = [])
    {
        // 第二参数默认配置
        $config = ['page'=>1,'limit'=>5,'order'=>'create_time desc','where'=>['is_show'=>1]];
        
        // 重组第二参数
        foreach ($config as $key => $val) if(!in_array($key,$opt)) $config[$key] = $val;
        foreach ($opt as $key => $val) $config[$key] = $val;
        
        // 获取当前分类
        $sort = self::where($config['where'])->find($id);
        $where= ['sort_id'=>$sort->id,'is_show'=>1];
        
        // 友链总数量
        $count   = count(Links::where($where)->field(['id'])->select());
        $data['page']   = ceil($count/$config['limit']);;
        $data['count']  = $count;
        
        // 获取分类下关联的友链数据
        $links = Links::where($where)->page($config['page'])->limit($config['limit'])->order($config['order'])->select();
        
        $data['data']   = $links;
        
        $sort['expand'] = $data;
        
        return $sort;
    }
    
    // OPT字段获取器 - 获取前修改
    public function getOptAttr($value)
    {
        $value = !empty($value) ? json_decode($value, true) : [];
        $value = array_merge([], $value);
        return (object)$value;
    }
}