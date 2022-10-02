<?php

use app\api\middleware\{api, handle};

// 中间件配置
return [
    // 中间件别名
    'alias'    => [
        'api'    => api::class,
        'handle' => handle::class,
    ],
    // 优先级设置，此数组中的中间件会按照数组中的顺序优先执行
    'priority' => [],
];
