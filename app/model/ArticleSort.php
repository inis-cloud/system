<?php

namespace app\model;

use think\Model;

class ArticleSort extends Model
{
    // 封装拓展字段数据 - 返回全部
    public static function ExpandAll($id = null, array $opt = [])
    {
        // 第二参数默认配置
        $config = ['page'=>1,'limit'=>5,'order'=>'create_time asc','withoutField'=>[],'whereOr'=>[],'where'=>['is_show'=>1]];
        
        // 重组第二参数
        foreach ($config as $key => $val) if(!in_array($key,$opt)) $config[$key] = $val;
        foreach ($opt as $key => $val) $config[$key] = $val;
        
        // 分类总数量
        $count  = count(self::whereOr($config['whereOr'])->where($config['where'])->field(['id'])->select());
        $data['page']   = ceil($count/$config['limit']);
        $data['count']  = $count;
        
        // 防止分页请求超出页码
        if ($config['page'] > $data['page']) $config['page'] = $data['page'];
        
        $sort = self::whereOr($config['whereOr'])->withAttr('expand', function ($value,$data){
            
            // 搜索分类下的文章
            $article = Article::where('sort_id','like','%|'.$data['id'].'|%')->field(['id'])->select();
            // 统计分类下的文章数量
            $value['count'] = sizeof($article);
            
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
    public static function article(int $id = null, array $opt = [])
    {
        // 第二参数默认配置
        $config = ['page'=>1,'limit'=>5,'order'=>'create_time asc','where'=>['is_show'=>1]];
        
        // 重组第二参数
        foreach ($config as $key => $val) if(!in_array($key,$opt)) $config[$key] = $val;
        foreach ($opt as $key => $val) $config[$key] = $val;
        
        // 获取当前分类
        $sort    = self::where($config['where'])->find($id);
        $where   = ['sort_id','like','%|'.$id.'|%'];
        
        if ($sort) {
            // 文章总数量
            $count   = count(Article::where([$where])->field(['id'])->select());
            $data['page']   = ceil($count/$config['limit']);;
            $data['count']  = $count;
            
            // 获取分类下关联的文章数据
            $article = Article::where([$where])->field(['id'])->page($config['page'])->limit($config['limit'])->order($config['order'])->select();
            // 封装分类下的文章ID
            foreach ($article as $val) $article_id[] = $val['id'];
            
            // 获取分类下的文章
            if(!empty($article_id)) $data['data'] = Article::ExpandAll($article_id, ['order'=>$config['order']]);
            else $data['data'] = [];
            
            $sort['expand'] = $data;
        }
        
        return $sort;
    }
    
    // OPT字段获取器 - 获取前修改
    public function getOptAttr($value)
    {
        $value = (!empty($value)) ? json_decode((is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value)) : $value;
        return $value;
    }
}