<?php

namespace Lemonlyue\Amqp\Constants;

/**
 * Class Amqp
 * @package Lemonlyue\Amqp\Constants
 */
class Amqp
{
    public const CONTEXT_TASK_OBJECT = 'context_task_object';// task object context

    // task status
    public const TASK_STATUS_RUNNING = 'running';
    public const TASK_STATUS_ERROR = 'error';
    public const TASK_STATUS_TERMINATED = 'terminated';
    public const TASK_STATUS_SUCCESS = 'success';

    public const AMQP_ERROR_CODE = 1;
    public const AMQP_SUCCESS_CODE = 0;

    // redis lock
    public const TASK_LOCK_KEY = 'amqp:lock:%s';// lock key
    public const TASK_LOCK_EXPIRE_TIME = 600;// default expire time
}