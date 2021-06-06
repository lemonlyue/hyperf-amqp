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

use Hyperf\Utils\Collection;
use Hyperf\Utils\Filesystem\Filesystem;

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
                    'id' => 'config',
                    'description' => 'The config for hyperf amqp',
                    'source' => __DIR__ . '/../publish/amqp_retry.php',
                    'destination' => BASE_PATH . '/config/autoload/amqp_retry.php',
                ],
                [
                    'id' => 'database',
                    'description' => 'The database for hyperf amqp.',
                    'source' => __DIR__ . '/../database/migrations/create_task_table.php',
                    'destination' => $this->getMigrationFileName(),
                ],
            ]
        ];
    }

    protected function getMigrationFileName(): string
    {
        $timestamp = date('Y_m_d_His');
        $filesystem = new Filesystem();

        return Collection::make(BASE_PATH . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem) {
                return $filesystem->glob($path . '*_create_task_table.php');
            })
            ->push(BASE_PATH . "/migrations/{$timestamp}_create_task_table.php")
            ->first();
    }
}
