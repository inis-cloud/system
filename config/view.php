<?php
// +----------------------------------------------------------------------
// | 模板设置
// +----------------------------------------------------------------------

return [
    // 模板引擎类型使用Think
    'type'           => 'Think',
    // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写 3 保持操作方法
    'auto_rule'      => 1,
    // 模板目录名
    'view_dir_name'  => 'view',
    // 模板后缀
    'view_suffix'    => 'html',
    // 模板文件名分隔符
    'view_depr'      => DIRECTORY_SEPARATOR,
    // 模板引擎普通标签开始标记
    'tpl_begin'      => '{',
    // 模板引擎普通标签结束标记
    'tpl_end'        => '}',
    // 标签库标签开始标记
    'taglib_begin'   => '{',
    // 标签库标签结束标记
    'taglib_end'     => '}',
    // 模板缓存
    'tpl_cache'      => false,

    // 预加载自定义模板标签
    // 'taglib_pre_load'     =>    'app\tag\Demo',
    
    // 预先加载的标签库 - 去空去重 - 转字符串
    'taglib_pre_load'=> env('app.tag_pre_load', false) ? implode(',', array_filter(array_unique([
        'app\tag\Links',
        'app\tag\Demo',
    ]))) : '',
];
