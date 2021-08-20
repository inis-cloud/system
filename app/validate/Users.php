<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

/**
 * Class User
 * @package app\validate
 */
class Users extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'id|ID'          =>  'require|number',
	    'account|帐号'   =>  'require|min:4|max:12|unique:users',
        'password|密码'  =>  'require|min:6',
        'email|邮箱'     =>  'require|email|unique:users',
        'nickname|昵称'  =>  'require|chsDash',
        'phone|电话'     =>  'mobile',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        
    ];

    /**
     * 验证场景
     * @var array
     */
    protected $scene = [
        // 读取
        'read'        => ['id'],
        // 注册
        'register'    => ['account','password','email','nickname','phone'],
        // 编辑
        'admin_edit'  => ['nickname','phone'],
        'api_edit'    => ['account','password','email','nickname','phone'],
        // 删除
        'delete'      => ['id'],
    ];
    
    // END
}
