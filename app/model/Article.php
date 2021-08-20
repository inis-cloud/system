<?php

namespace app\model;

use think\Model;
use think\Response;
use inis\utils\helper;
use think\facade\Config;
use think\model\concern\SoftDelete;

class Article extends Model
{
    // 设置软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    
    // 封装拓展字段数据 - 返回全部
    public static function ExpandAll($id = null, array $opt = [])
    {
        // 第二参数默认配置
        $config = ['page'=>1,'limit'=>5,'order'=>'create_time desc','withoutField'=>['content'],'whereOr'=>[],'where'=>['is_show'=>1,'delete_time'=>null]];
        
        // 重组第二参数
        foreach ($config as $key => $val) if(!in_array($key,$opt)) $config[$key] = $val;
        foreach ($opt as $key => $val) $config[$key] = $val;
        
        // 文章总数量
        $count  = count(self::whereOr($config['whereOr'])->where($config['where'])->field(['id'])->select());
        $data['page']   = ceil($count/$config['limit']);
        $data['count']  = $count;
        
        // 防止分页请求超出页码
        if($config['page'] > $data['page']) $config['page'] = $data['page'];
        
        $article = self::withTrashed()->whereOr($config['whereOr'])->withAttr('expand',function ($value,$data) {
            
            $user = Users::field(['nickname','description','email','address_url','head_img','opt'])->findOrEmpty($data['users_id']);
            
            $pay = [];
            $pay_array = ['alipay','wechat_pay','qq_pay'];
            if (!empty($user['opt'])) foreach ($user['opt'] as $key => $val) if (in_array($key,$pay_array)) $pay[$key] = $val;
            
            if (!$user->isEmpty()) {
                // 文章作者信息
                $value['author'] = [
                    'nickname'      =>   $user['nickname'],
                    'head_img'      =>   $user['head_img'],
                    'email'         =>   $user['email'],
                    'address_url'   =>   $user['address_url'],
                    'description'   =>   $user['description'],
                    'pay'           =>   $pay,
                ];
            } else {
                $options = (new Options)->GetOpt();
                $value['author'] = [
                    'nickname'      =>   $options['title'],
                    'head_img'      =>   $options['site_img'],
                    'email'         =>   null,
                    'address_url'   =>   $options['site_url'],
                    'description'   =>   $options['description'],
                    'pay'           =>   $pay,
                ];
            }
            
            $tag = Tag::select(explode("|", $data['tag_id']));
            $sort = ArticleSort::select(explode("|", $data['sort_id']));
            
            // 随机颜色
            $color_arr = ['light','danger','dark','primary','success','info','warning'];
            $rand = array_rand($color_arr);
            $color = $color_arr[$rand];
            
            foreach ($tag  as $key => $val){
                $value['tag'][$key] = [
                    'id'    =>  $val['id'],
                    'name'  =>  $val['name'],
                    'color' =>  (new helper)->RandArray()
                ];
            }
            foreach ($sort as $key => $val){
                $value['sort'][$key] = [
                    'id'    =>  $val['id'],
                    'name'  =>  $val['name'],
                ];
            }
            
            // 统计评论
            $value['comments'] = sizeof(Comments::where(['article_id'=>$data['id']])->field(['article_id'])->select());
            
            // 随机图 - 开启随机图 - 判断封面是否为空
            $conf = Config::get('inis.random_img');
            if ($conf['enable']) {
                $value['img_src'] = (!empty($data['img_src'])) ? $data['img_src'] : (new helper)->RandomImg($conf['type'],$conf['path']);
            } else $value['img_src'] = $data['img_src'];
            
            return $value;
            
        })->where($config['where'])->withoutField($config['withoutField'])->order($config['order'])->page($config['page'])->limit($config['limit'])->select($id);
        
        $data['data'] = $article;
        
        // 只有单条数据
        if (!empty($id) and is_numeric($id)) $result = $article[0];
        else if (is_array($id)) $result = $article;
        else $result = $data;
        
        return $result;
    }

    // 后台管理文章的专用接口
    public static function ExpandArticle(int $page = 1, int $limit = 5, string $order = 'create_time desc', $param = null)
    {
        (empty($param['users_id'])) ? $where = '' : $where = 'users_id = '.$param['users_id'];
        
        // 屏蔽输出字段
        $without = ['content','img_src','font_count','sort_id','tag_id'];
        
        // 回收站
        if(!empty($param['trashed'])){
            
            $data = self::onlyTrashed()->withAttr('expand',function ($value,$data) {
                
                $user = Users::field(['nickname','description','head_img'])->findOrEmpty($data['users_id']);
                
                if (!$user->isEmpty()) {
                    // 文章作者信息
                    $value['author'] = [
                        'nickname'      =>   $user['nickname'],
                        'head_img'      =>   $user['head_img'],
                        'description'   =>   $user['description'],
                    ];
                } else {
                    $options = (new Options)->GetOpt();
                    $value['author'] = [
                        'nickname'      =>   $options['title'],
                        'head_img'      =>   $options['site_img'],
                        'description'   =>   $options['description'],
                    ];
                }
                
                // 统计评论
                $value['comments'] = sizeof(Comments::where(['article_id'=>$data['id']])->field(['article_id'])->select());
                
                return $value;
                
            })->where($where)->withoutField($without)->order($order)->page($page)->limit($limit)->select();
        }
        
        return $data;
    }
    
    // 封装搜索接口
    public static function search($value, array $opt = [], int $sort_id = null)
    {
        if (empty($opt)) $opt = ['page'=>1,'limit'=>5,'order'=>'create_time desc'];
        
        $map1   = ['title'   , 'like', '%'.$value.'%'];
        $map2   = ['content' , 'like', '%'.$value.'%'];
        $map3   = ['is_show'=>1];
        
        $whereOr = [$map1,$map2];
        $where   = $map3;
        
        // 精准分类搜索
        if (!empty($sort_id)) {
            // 搜索内容为空，返回分类下的全部数据
            if (empty($value)) $whereOr = [];
            $where = [['sort_id' , 'like', '%|'.$sort_id.'|%'],['is_show','=',1]];
        }
        
        // 防止显示隐藏文章
        if (empty($value)) $whereOr = $map3;
        
        $opt = [
            'page'   =>  $opt['page'], 
            'limit'  =>  $opt['limit'],
            'order'  =>  $opt['order'],
            'whereOr'=>  $whereOr,
            'where'  =>  $where
        ];
        
        $data = self::ExpandAll(null, $opt);
        
        return $data;
    }
    
    public static function comments(int $id = null, array $opt = [])
    {
        // 第二参数默认配置
        $config = ['page'=>1,'limit'=>5,'order'=>'create_time desc','withoutField'=>[],'whereOr'=>[],'where'=>['article_id'=>$id,'pid'=>0],'tree'=>true];
        // 重组第二参数
        foreach ($config as $key => $val) if(!in_array($key,$opt)) $config[$key] = $val;
        foreach ($opt as $key => $val) $config[$key] = $val;
        
        // 评论总数量
        $count = count(Comments::where($config['where'])->field(['id'])->select());
        $data['page']   = ceil($count/$config['limit']);;
        $data['count']  = $count;
        
        // 防止分页请求超出页码
        if($config['page'] > $data['page']) $config['page'] = $data['page'];
        
        $comments = Comments::ExpandAll(null, $config)['data'];
        
        foreach ($comments as $key => $val) $comments[$key]['son'] = Comments::FindSon($val['id'], $config['tree']);
        
        // 冒泡排序
        if (!$config['tree']) foreach ($comments as $key => $val) $comments[$key]['son'] = (new helper)->BubbSort($comments[$key]['son'], 'create_time');
        
        $data['data'] = $comments;
        
        return $data;
    }
    
    // 自动摘要
    public function getDescriptionAttr($value, $data)
    {
        if (empty($value)) {
            $content = self::where(['id'=>$data['id']])->field(['content'])->column(['content']);
            $value  = (new helper)->StringToText(implode($content));
        }
        
        return $value;
    }
    
    // OPT字段获取器 - 获取前修改
    public function getOptAttr($value)
    {
        $value = (!empty($value)) ? json_decode((is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value)) : $value;
        return $value;
    }
}