<?php
namespace app\tag;
// namespace app\common\taglib;

use think\template\TagLib;
use app\model\mysql\{Links as iLinks};

class Demo extends TagLib {
    
    /**
     * 定义标签列表
     */
    protected $tags=  [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'open'      => ['close'=>0],
        'close'     => ['close'=>1],
    ];

    /**
     * 这是一个闭合标签的简单演示
     */
    public function tagClose($tag, $content)
    {

        $parse = '<?php ';
        $parse .= 'echo "这是一个闭合标签的简单演示1";';
        $parse .= ' ?>';
        $parse .= $content;
        return $parse;

        // return '这是一个闭合标签的简单演示';
    }
    
    public function tagOpen($tag, $content)
    {
        $parse = '<?php ';
        $parse .= 'echo "这是一个非闭合标签的简单演示";';
        $parse .= ' ?>';
        return $parse;
        // return '这是一个非闭合标签的简单演示';
    }
    
}