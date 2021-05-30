<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Lemonlyue\Amqp;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'amqp',
                    'description' => 'The config for hyperf amqp',
                    'source' => __DIR__ . '/../publish/amqp.php',
                    'destination' => BASE_PATH . '/config/autoload/amqp.php',
                ]
            ]
        ];
    }
}
