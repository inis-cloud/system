<?php

namespace app\model\mysql;

use think\Model;
use inis\utils\{helper};

class Tag extends Model
{
    // 封装拓展字段数据 - 返回全部
    public static function ExpandAll(int $id = null, array $opt = [])
    {
        // 第二参数默认配置
        $config = ['page'=>1,'limit'=>5,'order'=>'create_time desc','withoutField'=>[],'whereOr'=>[],'where'=>['is_show'=>1]];
        
        // 重组第二参数
        foreach ($config as $key => $val) if(!in_array($key,$opt)) $config[$key] = $val;
        foreach ($opt as $key => $val) $config[$key] = $val;
        
        // 标签总数量
        $count  = count(self::whereOr($config['whereOr'])->where($config['where'])->field(['id'])->select());
        $data['page']   = ceil($count/$config['limit']);
        $data['count']  = $count;
        
        // 防止分页请求超出页码
        if($config['page'] > $data['page']) $config['page'] = $data['page'];
        
        $tag = self::whereOr($config['whereOr'])->withAttr('expand', function ($value,$data){
            
            // 搜索标签下的文章
            $article = Article::where('tag_id','like','%|'.$data['id'].'|%')->field(['id'])->select();
            
            // 统计标签下的文章数量
            $value['count'] = sizeof($article);
            $value['color'] = (new helper)->RandArray();
            
            return $value;
            
        })->where($config['where'])->withoutField($config['withoutField'])->order($config['order'])->page($config['page'])->limit($config['limit'])->select($id);
        
        $data['data'] = $tag;
        
        // 只有单条数据
        if (!empty($id) and is_numeric($id)) $result = $tag[0];
        else if (is_array($id)) $result = $tag;
        else $result = $data;
        
        return $result;
    }
    
    // 关联分类下的文章 - 自定义一对多
    public static function article(int $id = null, array $opt = [])
    {
        // 第二参数默认配置
        $config = ['page'=>1,'limit'=>5,'order'=>'create_time desc','where'=>['is_show'=>1]];
        
        // 重组第二参数
        foreach ($config as $key => $val) if(!in_array($key,$opt)) $config[$key] = $val;
        foreach ($opt as $key => $val) $config[$key] = $val;
        
        // 获取当前分类
        $tag    = self::where($config['where'])->find($id);
        $where   = ['tag_id','like','%|'.$tag['id'].'|%'];
        
        if ($tag) {
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
            
            $tag['expand'] = $data;
        }
        
        return $tag;
    }

    // 标签 - 文章 - 管理模型 - 保存
    public function TagSave(int $article_id, $tag_id = [], $tag_name = [])
    {
        $add_tag_id = [];
        
        if (is_string($tag_id))   $tag_id   = array_filter(explode(',', $tag_id ?? ''));
        if (is_string($tag_name)) $tag_name = array_filter(explode(',', $tag_name ?? ''));
        
        // 新增标签 - 并获取新增标签的ID
        foreach ($tag_name as $key => $val){
            
            $add_tag = self::create(['name'=>$val,'article_id'=>$article_id]);
            
            $add_tag_id[$key] = $add_tag->id;
        }
        
        // 合本本次需要更新的标签
        $tag_arr = array_merge($tag_id,$add_tag_id);
        
        // 更新文章下的 tag_id
        if(!empty($tag_arr)){
            
            $tag_id = implode("|", str_replace(',','|',$tag_arr));
            Article::update(['tag_id'=>'|'.$tag_id.'|'],['id'=>$article_id]);
            
        }else{
            // 标签为空，清空全部标签
            Article::update(['tag_id'=>''],['id'=>$article_id]);
        }
    }

    // 标签 - 删除
    public static function DelTag($id)
    {
        $data = (new Article)->where('tag_id','like','%|'.$id.'|%')->field(['tag_id','id'])->select();
        
        foreach ($data as $key => $val){
            
            $tag_id = explode('|', $val['tag_id'] ?? '');
            
            foreach ($tag_id as $k => $v) if($v == $id) unset($tag_id[$k]);
            
            $tag_id = implode('|',array_filter($tag_id));
            
            if(!empty($tag_id)){
                
                $tag_id = '|'.$tag_id.'|';
                
                // 更新标签
                (new Article)->update(['tag_id'=>$tag_id],['id'=>$val['id']]);
                
            }else{
                
                // 标签为空，清空全部标签
                (new Article)->update(['tag_id'=>''],['id'=>$val['id']]);
            }
        }
    }
    
    // OPT字段获取器 - 获取前修改
    public function getOptAttr($value)
    {
        $value = (!empty($value)) ? json_decode((is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value)) : $value;
        return $value;
    }

}