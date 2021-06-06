<?php


namespace Lemonlyue\Amqp\Utils;


use Exception;
use Lemonlyue\Amqp\Constants\Amqp;
use Lemonlyue\Amqp\Facade\Redis;

class AmqpLock
{
    /**
     * lock
     *
     * @param $key
     * @param string $expire
     * @return string
     * @throws Exception
     */
    public function lock($key, $expire = Amqp::TASK_LOCK_KEY)
    {
        if (empty($key) || (int)$expire <= 0) {
            return false;
        }

        $token = self::generateToken();
        $lockKey = $this->getLockKey($key);
        $result = Redis::set($lockKey, $token, ['nx', 'ex' => $expire]);
        return $result ? $token : '';
    }

    /**
     * add lock operation
     *
     * @param $key
     * @param array $func
     * @param array $params
     * @param int $expire
     * @param bool $isReleaseLock
     * @return bool
     * @throws Exception
     */
    public function addLock($key, $func = [], $params = [], $expire = Amqp::TASK_LOCK_EXPIRE_TIME, $isReleaseLock = true)
    {
        // prevent concurrent
        while (!$token = $this->lock($key, $expire)) {
            sleep(1);
        }
        if (!empty($func)) {
            try {
                $func($params);
            } catch (Exception $exception) {
                return false;
            } finally {
                if ($isReleaseLock) {
                    $this->releaseLock($key, $token);
                }
            }
        }
        return true;
    }

    /**
     * get lock key
     *
     * @param $key
     * @return string
     */
    public function getLockKey($key)
    {
        return sprintf(Amqp::TASK_LOCK_KEY, $key);
    }

    /**
     * generate token
     *
     * @return string
     * @throws Exception
     */
    public static function generateToken()
    {
        [$t1, $t2] = explode(' ', microtime());
        $random = random_int(1000000, 9999999);

        return sprintf('%.0f', ((float)$t1 + (float)$t2)) . $random;
    }

    /**
     * release lock
     *
     * @param $key
     * @param string $token
     * @return bool
     */
    public function releaseLock($key, string $token)
    {
        if (empty($key) || empty($token)) {
            return false;
        }
        $lockKey = $this->getLockKey($key);
        $lockToekn = Redis::get($lockKey);
        if ($lockToekn === $token) {
            Redis::del($key);
            return true;
        }
        return false;
    }
}