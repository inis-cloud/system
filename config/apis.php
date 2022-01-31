<?php
// +----------------------------------------------------------------------
// | 内置 - API 列表
// +----------------------------------------------------------------------

$docsify = 'https://docs.inis.cc/#/api/';
$author  = ['nickname'=>'兔子','url'=>'//inis.cn'];

return [
    [
        'title'   =>  '文章 - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'article',
        'content' =>  '负责文章的增删改查等',
        'scenes'  =>  ['展示文章','操作文章']
    ],
    [
        'title'   =>  '文章分类 - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'article-sort',
        'content' =>  '可以获取文章分类相关数据',
        'scenes'  =>  ['展示文章分类']
    ],
    [
        'title'   =>  '轮播 - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'banner',
        'content' =>  '可以获取轮播相关数据',
        'scenes'  =>  ['展示轮播']
    ],
    [
        'title'   =>  '缓存 - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'cache',
        'content' =>  '可以往服务器获取或设置缓存',
        'scenes'  =>  ['缓存','第三方API调用限制次数']
    ],
    [
        'title'   =>  '代理 - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'other?id=【代理】接口',
        'content' =>  '可以让后端代理请求目标API数据',
        'scenes'  =>  ['代理','解决跨域']
    ],
    [
        'title'   =>  '评论 - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'comments',
        'content' =>  '负责评论的增删改查等',
        'scenes'  =>  ['文章评论','页面评论','其他评论']
    ],
    [
        'title'   =>  '页面 - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'page',
        'content' =>  '可用获取页面相关数据',
        'scenes'  =>  ['展示页面','自定义页面']
    ],
    [
        'title'   =>  '公告 - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'placard',
        'content' =>  '可用获取公告相关数据',
        'scenes'  =>  ['展示公告']
    ],
    [
        'title'   =>  '搜索 - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'search',
        'content' =>  '搜索文章或分类下的文章',
        'scenes'  =>  ['搜索']
    ],
    [
        'title'   =>  '标签 - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'tag',
        'content' =>  '负责标签的增删改查等',
        'scenes'  =>  ['文章标签']
    ],
    [
        'title'   =>  '用户 - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'users',
        'content' =>  '负责用户的增删改查等',
        'scenes'  =>  ['登录','注册']
    ],
    [
        'title'   =>  '验证码 - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'verify-code',
        'content' =>  '生成或校验验证码',
        'scenes'  =>  ['生成验证码','校验验证码']
    ],
    [
        'title'   =>  '文件 - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'file',
        'content' =>  '可以获取后端目录下的文件信息等',
        'scenes'  =>  ['文件系统','随机图','随机文本']
    ],
    [
        'title'   =>  '聚合 - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'group',
        'content' =>  '可用于做站内信息统计等',
        'scenes'  =>  ['统计图表','站内各项数据情况']
    ],
    [
        'title'   =>  '友链 - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'links',
        'content' =>  '负责友链的增删改查等',
        'scenes'  =>  ['展示友链','操作友链']
    ],
    [
        'title'   =>  '友链分类 - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'links-sort',
        'content' =>  '可以查询友链分类相关数据',
        'scenes'  =>  ['展示友链分类']
    ],
    [
        'title'   =>  '音乐 - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'music',
        'content' =>  '可以用于制作音乐播放器等',
        'scenes'  =>  ['QQ音乐','网易音乐']
    ],
    [
        'title'   =>  '配置 - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'options',
        'content' =>  '负责配置信息的增删改查等',
        'scenes'  =>  ['自定义配置信息']
    ],
    [
        'title'   =>  '热搜 - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'other?id=【热搜】接口',
        'content' =>  '获取本日百度和CSDN的热搜数据',
        'scenes'  =>  ['百度热搜','CSDN热搜']
    ],
    [
        'title'   =>  '定位信息 - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'other?id=【定位信息】接口',
        'content' =>  '可以IP或经纬度获取地理位置或天气信息',
        'scenes'  =>  ['IP定位','经纬度定位','天气信息']
    ],
    [
        'title'   =>  '解析UA - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'other?id=获取【客户端】信息',
        'content' =>  '可以通过user-agent解析客户端信息',
        'scenes'  =>  ['客户端系统','客户端手机','客户端浏览器']
    ],
    [
        'title'   =>  '解析QQ - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'other?id=解析【qq】信息',
        'content' =>  '可以通过QQ帐号解析QQ昵称、邮件、头像等',
        'scenes'  =>  ['QQ解析']
    ],
    [
        'title'   =>  'PING检测 - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'other?id=【ping】检测',
        'content' =>  '通过IP或域名检查目标是否存活',
        'scenes'  =>  ['PING']
    ],
    [
        'title'   =>  'ICP备案查询 - 接口',
        'author'  =>  $author,
        'docsify' =>  $docsify . 'other?id=【icp备案】查询',
        'content' =>  '通过域名查询备案信息',
        'scenes'  =>  ['备案查询']
    ],
];