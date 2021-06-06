<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    'retry_count' => (int)env('AMQP_RETRY_COUNT', 3),// 重试次数
    'retry_time_interval' => (int)env('AMQP_RETRY_TIME_INTERVAL', 1),// 重试间隔时间
    'task_table' => env('AMQP_TASK_TABLE', 'task'),// 记录消费消息表名
];