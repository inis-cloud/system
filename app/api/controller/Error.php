<?php
namespace app\api\controller;

/**
 * Class Error
 * @package app\controller
 */
class Error extends Base
{
    // 404
    /**
     * @return \think\Response
     */
    public function index()
    {
        return $this->create([], '资源不存在', 404);
    }
}