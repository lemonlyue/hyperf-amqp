<?php

namespace Lemonlyue\Amqp\Facade;

use Hyperf\Utils\ApplicationContext;

/**
 * Class Redis
 */
class Redis
{
    /**
     * @return \Hyperf\Redis\Redis
     */
    public static function getRedis()
    {
        $container = ApplicationContext::getContainer();
        return $container->get(\Hyperf\Redis\Redis::class);
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        $redis = self::getRedis();
        return $redis->$method(...$arguments);
    }
}