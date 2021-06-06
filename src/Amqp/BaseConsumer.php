<?php


namespace Lemonlyue\Amqp\Amqp;


use Hyperf\Amqp\Builder\ExchangeBuilder;
use Hyperf\Amqp\Consumer;
use Hyperf\Amqp\ConsumerFactory;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Result;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Lemonlyue\Amqp\Amqp\Producer\DelayProducer;
use Lemonlyue\Amqp\Constants\Amqp;
use Lemonlyue\Amqp\Exceptions\MessageException;
use Lemonlyue\Amqp\Model\AmqpModel;
use Lemonlyue\Amqp\Utils\AmqpLock;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

abstract class BaseConsumer extends ConsumerMessage
{
    /**
     * @var StdoutLoggerInterface|mixed
     */
    protected $looger;

    /**
     * @var array
     */
    private $argments;

    public function __construct()
    {
        $this->looger = ApplicationContext::getContainer()->get(StdoutLoggerInterface::class);
    }

    /**
     * @param $data
     * @return bool
     */
    private function beforeConsume($data)
    {
        try {
            $key = $data['key'];
            $task = AmqpModel::query()->where('key', $key)->first();
            if (empty($task)) {
                $task = AmqpModel::create([
                    'key' => $key,
                    'exchange'       => $this->getExchange(),
                    'routing_key'    => $this->getRoutingKey(),
                    'product_system' => $data['product_system'],
                    'request_data'   => json_encode($data['data']),
                    'retry_times'    => 0,
                    'status'         => Amqp::TASK_STATUS_RUNNING,
                ]);
            }
            ++$task->retry_count;
            Context::set(Amqp::CONTEXT_TASK_OBJECT, $task);
        } catch (\Throwable $throwable) {
            $this->looger->error($throwable->getMessage());
        }
        return true;
    }

    /**
     * @param $data
     * @param AMQPMessage $message
     * @return string
     * @throws \Exception
     */
    public function consumeMessage($data, AMQPMessage $message): string
    {
        $lock = new AmqpLock();
        $key = $data['key'];
        $result = $lock->addLock($key, function () use ($data) {
            $this->beforeConsume($data);
            $consumerFactory = new ConsumerFactory();
            $consumer = $consumerFactory();
            try {
                $task = Context::get(Amqp::CONTEXT_TASK_OBJECT);
                if (empty($task)) {
                    return Result::ACK;
                }
                $retryCount = config('amqp_retry.retry_count');
                /**
                 * The number of configuration retries exceeded
                 */
                if ($task->retry_count > $retryCount || $task->status === Amqp::TASK_STATUS_SUCCESS) {
                    return Result::ACK;
                }
                $requestData = json_decode($task->request_data, true);
                $data = $this->consume($requestData);
                $result = [
                    'code'    => Amqp::AMQP_SUCCESS_CODE,
                    'message' => 'consume success',
                    'data'    => $data,
                ];
            } catch (\Exception $exception) {
                $result = [
                    'code'    => Amqp::AMQP_ERROR_CODE,
                    'message' => $exception->getMessage(),
                ];
            }
            $this->afterConsume($result);
            return true;
        });
        return $result ? Result::REQUEUE : Result::ACK;
    }

    /**
     * @param array $result
     * @return bool
     */
    private function afterConsume(array $result)
    {
        try {
            $task = Context::get(Amqp::CONTEXT_TASK_OBJECT);
            if (empty($task)) {
                return false;
            }

            $task->status = $result['code'] === Amqp::AMQP_SUCCESS_CODE ? Amqp::TASK_STATUS_SUCCESS : Amqp::TASK_STATUS_ERROR;
            $task->response_data = json_encode($result, JSON_UNESCAPED_UNICODE);
            $exchangeName        = $this->getExchange();
            $routingKeyName      = $this->getRoutingKey();

            $retryCount = config('amqp_retry.retry_count');
            if ($task->retry_count > $retryCount) {
//                $exchangeName = ;
//                $routingKeyName = ;
            }

            if ($task->status === Amqp::TASK_STATUS_ERROR) {
                $delayQueueObj = new DelayProducer($exchangeName, $routingKeyName,
                    $task->retry_times * config('amqp_retry.retry_times_interval'), $task->key);
                $delayResult   = $delayQueueObj->produce();
                if (!$delayResult) {
                    throw new MessageException('delay queue error');
                }
            }
            $task->save();
        } catch (\Exception $exception) {
            $this->looger->error($exception->getMessage());
        }
        return true;
    }

    /**
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