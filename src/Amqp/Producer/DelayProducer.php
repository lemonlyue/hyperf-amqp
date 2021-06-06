<?php


namespace Lemonlyue\Amqp\Amqp\Producer;


use Hyperf\Amqp\Producer;
use Hyperf\Utils\ApplicationContext;
use Lemonlyue\Amqp\Amqp\BaseProducer;

class DelayProducer extends BaseProducer
{
    public function __construct($exchangeName, $routingKey, $delay, $key, $poolName = null)
    {
        $this->exchange   = $exchangeName;
        $this->routingKey = $routingKey;
        parent::__construct([], $key, $delay, $poolName);
    }

    public function produce()
    {
        $producer = ApplicationContext::getContainer()->get(Producer::class);

        return $producer->produce($this);
    }
}