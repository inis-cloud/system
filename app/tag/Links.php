<?php
namespace app\tag;
// namespace app\common\taglib;

use think\template\TagLib;
use app\model\mysql\{Links as iLinks};

class Links extends TagLib {
    
    /**
     * 定义标签列表
     */
    protected $tags=  [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'list'     => ['close'=>1],
        'data'     => ['close'=>0],
    ];

    /**
     * 这是一个闭合标签的简单演示
     */
    public function tagList($tag, $content)
    {
        // $item  = iLinks::select();
        
        /*
        $parse  = '<?';
        $parse .= '$item = [["id"=>1],["id"=>2]];';
        $parse .= 'foreach($item as $key=>$val){';
        $parse .= 'echo $val["id"];';
        $parse .= '} ?>';
        $parse .= $content;
        */
        $parse = '<?php
            $item = \app\model\mysql\Links::select();
        ?>';
        $parse .= $content;
        
        return $parse;
    }
    
    public function tagData($tag, $content)
    {
        $parse = [1,2,3];
        return $parse;
    }
    
}