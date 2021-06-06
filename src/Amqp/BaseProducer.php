<?php


namespace Lemonlyue\Amqp\Amqp;


use Hyperf\Amqp\Builder\ExchangeBuilder;
use Hyperf\Amqp\Message\ProducerMessage;
use Lemonlyue\Amqp\Exceptions\MessageException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

abstract class BaseProducer extends ProducerMessage
{
    protected $type = 'x-delayed-message';

    protected $delayType = "fanout";

    protected $argments = [];

    /**
     * BaseProducer constructor.
     * @param $data
     * @param string $key
     * @param int $delay
     * @param null $poolName
     * @throws MessageException
     */
    public function __construct($data, string $key, int $delay = 0, $poolName = null)
    {
        $this->poolName = $poolName ?? 'default';

        if (empty($key)) {
            throw new MessageException('key is empty');
        }
        $this->payload = [
            'key' => $key,
            'product_system' => config('app_name'),
            'data' => $data,
        ];
        $this->properties['application_headers'] = new AMQPTable(['x-delay' => $delay * 1000]);
        $this->properties['delivery_mode'] = AMQPMessage::DELIVERY_MODE_PERSISTENT;
    }

    /**
     * get producer key
     *
     * @param $keyPrefix
     * @return mixed
     */
    public static function producerKey($keyPrefix)
    {
        return getUUID($keyPrefix);
    }

    /**
     * get exchange builder
     *
     * @return ExchangeBuilder
     */
    public function getExchangeBuilder(): ExchangeBuilder
    {
        $this->argments = array_merge($this->argments, ['x-delayed-type' => $this->delayType]);

        return (new ExchangeBuilder())->setExchange($this->getExchange())
            ->setType($this->getType())
            ->setArguments(new AMQPTable($this->argments));
    }
}