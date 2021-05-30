<?php


namespace Lemonlyue\Amqp\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * @Command
 */
class AmqpCommand extends HyperfCommand
{
    /**
     * @var string
     */
    protected $name = 'amqp:publish';

    public function configure()
    {
        parent::configure();
        $this->addArgument('generate', InputArgument::OPTIONAL, 'generate');
    }

    /**
     * @inheritDoc
     */
    public function handle()
    {
        $argument = $this->input->getOption('config');
        if ($argument) {
            $this->copySource(__DIR__ . '/../../publish/amqp.php', BASE_PATH . '/config/autoload/amqp.php');
            $this->line('The hyperf-amqp configuration file has been generated', 'info');
        }
    }

    protected function getOptions()
    {
        return [
            ['config', NULL, InputOption::VALUE_NONE, 'Publish the configuration for hyperf-amqp'],
        ];
    }

    /**
     * 复制文件到指定的目录中
     * @param $copySource
     * @param $toSource
     */
    protected function copySource($copySource, $toSource)
    {
        copy($copySource, $toSource);
    }
}