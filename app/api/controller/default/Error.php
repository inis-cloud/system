<?php

namespace app\api\controller\default;

use think\facade\{Lang};

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
        return $this->json([], Lang::get('不存在该API'), 404);
    }
}