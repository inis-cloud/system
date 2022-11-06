<?php

namespace app\model\mysql;

use Parsedown;
use think\Model;
use inis\utils\{helper, markdown};

class Comments extends Model
{
    // 封装拓展字段数据 - 查询所有评论 - 返回全部
    public static function ExpandAll($id = null, array $opt = [])
    {
        // 第二参数默认配置
        $config = ['page'=>1,'limit'=>5,'order'=>'create_time desc','field'=>[],'withoutField'=>[],'whereOr'=>[],'where'=>['pid'=>'0'],'group'=>[]];
        
        // 重组第二参数
        foreach ($config as $key => $val) if(!in_array($key,$opt)) $config[$key] = $val;
        foreach ($opt as $key => $val) $config[$key] = $val;
        
        // 总数量
        $count  = count(self::whereOr($config['whereOr'])->where($config['where'])->field(['id'])->group($config['group'])->select());
        $data['page']   = ceil($count/$config['limit']);
        $data['count']  = $count;
        
        // 防止分页请求超出页码
        if($config['page'] > $data['page']) $config['page'] = $data['page'];
        
        $comments = self::whereOr($config['whereOr'])->withAttr('expand', function ($value,$data) {
            
            $helper = new helper;
            
            // 默认随机头像
            $value['head_img'] = $helper->RandomImg("local", "admin/images/anime/");
            
            $user = Users::where(['email'=>$data['email']])->field(['head_img'])->findOrEmpty();
            
            if (!$user->isEmpty()) $value['head_img'] = $user->head_img;
            else if (preg_match('|^[1-9]\d{4,10}@qq\.com$|i', $data['email'])) {
                
                // 解析QQ邮箱并封装头像
                $qq = str_replace('@qq.com', '', $data['email']);
                
                $value['head_img'] = (!is_numeric($qq)) ? $value['head_img'] : '//q1.qlogo.cn/g?b=qq&nk='.$qq.'&s=640';
            }
            
            $value['url'] = (!empty($data['url'])) ? $data['url'] : null;
            
            // 判断是否登录后评论
            if (!empty($data['users_id'])) {
                
                // 获取用户数据
                $user = Users::field(['nickname','sex','email','head_img','description','address_url'])->findOrEmpty((int)$data['users_id']);
                
                if (empty($user['head_img']) and preg_match('|^[1-9]\d{4,10}@qq\.com$|i', $user['email'])) {
                    
                    $qq = str_replace('@qq.com', '', $user['email']);
                    
                    // 解析QQ邮箱并封装头像
                    $user['head_img'] = (!is_numeric($qq)) ? null : '//q1.qlogo.cn/g?b=qq&nk='.$qq.'&s=640';
                }
                
                // 评论头像,未设置则使用随机头像
                $value['head_img'] = (!empty($user['head_img'])) ? $user['head_img'] : $value['head_img'];
                
                $value['user']  = $user;
                
                $value['url']   = $user['address_url'];
            }
            
            // 解析代理信息
            $value['agent'] = [
                'browser'=>$helper->GetClientBrowser($data['agent']),
                'os'     =>$helper->GetClientOS($data['agent']),
                'mobile' =>$helper->GetClientMobile($data['agent']),
            ];
            
            // 获取该评论下的文章标题
            if (!empty($data['article_id'])) {
                $article = Article::field(['id','title'])->findOrEmpty($data['article_id']);
                if (!$article->isEmpty()) $value['article'] = $article;
            }
            
            $value['description'] = $helper->StringToText(!empty($data['content']) ? $data['content'] : '');
            if (!empty($data['content'])) {
                // setBreaksEnabled(true) 自动换行 setMarkupEscaped(true) 转义HTML setUrlsLinked(false) 防止自动链接
                $value['html']    = Parsedown::instance()->setUrlsLinked(false)->text($data['content']);
                // 解析自定义标签
                $value['html']    = markdown::parse($value['html']);
            }
            
            $value['pid'] = $data['pid'] != 0 ? self::field(['id','nickname','email','url'])->find($data['pid']) : [];
            
            return $value;
            
        })->where($config['where'])->group($config['group'])->field($config['field'])->withoutField($config['withoutField'])->order($config['order'])->page($config['page'])->limit($config['limit'])->select($id);
        
        $data['data'] = $comments;
        
        // 只有单条数据
        if (!empty($id) and is_numeric($id)) $result = $comments[0];
        else if (is_array($id)) $result = $comments;
        else $result = $data;
        
        return $result;
    }
    
    // 找儿子算法 - ($tree == true) ? '无限递归' : '二级评论';
    public static function FindSon(int $id = null, $tree = true)
    {
        $items  = self::ExpandAll(null, ['where'=>['pid'=>$id]])['data'];
        $result = $items;
        
        foreach ($items as $key => $val) {
            
            $item = self::where(['pid'=>$val['id']])->field(['id'])->findOrEmpty();
            
            if ($tree) {
                // 找到儿子了
                if (!$item->isEmpty()) {
                    $result[$key]['son'] = self::FindSon($val['id'], $tree);
                } else $result[$key]['son'] = [];   // 儿子还没出生
            } else {
                // 找到儿子了
                if (!$item->isEmpty()) {
                    $data = self::FindSon($val['id'], $tree);
                    foreach ($data as $val) $result[] = $val;
                }
            }
        }
        
        return json_decode($result, true);
    }
    
    // 封装拓展字段数据 - 根据文章的ID查询全部 - 返回全部
    public static function article($id, int $page = 1, int $limit = 5, string $order = 'create_time desc', array $where = ['pid'=>'0'])
    {
        $where['article_id'] = $id;
        
        $data = self::withAttr('expand', function ($value,$data){
            
            // 默认头像
            $value['head_img'] = 'http://q1.qlogo.cn/g?b=qq&nk=1211515059&s=640';
            
            // 解析QQ邮箱并封装头像
            if(preg_match('|^[1-9]\d{4,10}@qq\.com$|i',$data['email'])){
                
                $qq = str_replace('@qq.com', '', $data['email']);
                
                $value['head_img'] = 'http://q1.qlogo.cn/g?b=qq&nk='.$qq.'&s=640';
            }
            
            // 封装代理信息
            $agent = [
                'browser'=>(new helper())->GetClientBrowser($data['agent']),
                'os'     =>(new helper())->GetClientOS($data['agent']),
                'mobile' =>(new helper())->GetClientMobile($data['agent']),
            ];
            
            $value['article'] = Article::field(['title'])->find($data['article_id'])['title'];
            $value['reply'] = self::ExpandcChild($data['id']);
            // 代理信息
            $value['agent'] = $agent;
            
            return $value;
            
        })->where($where)->page($page)->limit($limit)->order($order)->select();

        return $data;
    }
    
    // URL修改器
    public function getUrlAttr($value)
    {
        // 过滤域名http(s):// 和末位 / 
        return CommTrimURL($value);
    }
    
    // users_id 修改器
    public function getUsersIdAttr($value, $data)
    {
        if (!empty($data['email'])) {
            
            $user = Users::where(['email'=>$data['email']])->field(['id'])->findOrEmpty();
            if (!$user->isEmpty()) $value = $user->id;
            
        } else if (!empty($data['url'])) {
            
            $map1 = ['address_url','=',$data['url']];
            $map2 = ['nickname', 'like', '%'.$data['nickname'].'%'];
            
            $user = Users::where([$map1,$map2])->field(['id'])->findOrEmpty();
            if (!$user->isEmpty()) $value = $user->id;
        }
        
        return $value;
    }
    
    // nickname 修改器
    public function getNicknameAttr($value, $data)
    {
        if (!empty($data['users_id'])) {
            
            $user = Users::field(['nickname'])->findOrEmpty((int)$data['users_id']);
            if (!$user->isEmpty()) $value = $user->nickname;
            
        } else if (!empty($data['email'])) {
            
            $user = Users::where(['email'=>$data['email']])->field(['nickname'])->findOrEmpty();
            if (!$user->isEmpty()) $value = $user->nickname;
        }
        
        return $value;
    }
    
    // OPT字段获取器 - 获取前修改
    public function getOptAttr($value)
    {
        $value = !empty($value) ? (is_array($value) ? $value : json_decode($value, true)) : [];
        $value = array_merge([], $value ?? []);
        return (object)$value;
    }
}