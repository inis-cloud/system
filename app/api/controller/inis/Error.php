<?php
namespace app\api\controller\inis;

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
        return $this->create([], '不存在该API', 404);
    }
}