<?php


namespace Lemonlyue\Amqp\Model;

use Hyperf\DbConnection\Model\Model;


/**
 * Class AmqpModel
 * @package Lemonlyue\Amqp\Model
 */
class AmqpModel extends Model
{
    public function __construct()
    {
        $this->setTable(config('amqp_retry.task_table'));
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['key', 'routing_key', 'exchange', 'product_system', 'request_data', 'retry_times', 'status'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'retry_times' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}