<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Lemonlyue\Amqp\Generator;

use Hyperf\Command\Annotation\Command;
use Hyperf\Devtool\Generator\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @Command
 */
class AmqpConsumerCommand extends GeneratorCommand
{
    /**
     * AmqpConsumerCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('amqp:amqp-consumer');
        $this->setDescription('Create a new amqp consumer class');
    }

    /**
     * Execute the console command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;

        $this->output = $output;

        $name = $this->qualifyClass($this->getNameInput());

        $exchange = $this->getExchangeInput();

        $routingKey = $this->getRoutingKeyInput();

        $queue = $this->getQueueInput();

        $path = $this->getPath($name);

        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if (($input->getOption('force') === false) && $this->alreadyExists($this->getNameInput())) {
            $output->writeln(sprintf('<fg=red>%s</>', $name . ' already exists!'));
            return 0;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        file_put_contents($path, $this->buildClass($name, $exchange, $routingKey, $queue));

        $output->writeln(sprintf('<info>%s</info>', $name . ' created successfully.'));

        return 0;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the class'],
            ['exchange', InputArgument::REQUIRED, 'The name of the class'],
            ['routingKey', InputArgument::REQUIRED, 'The name of the class'],
            ['queue', InputArgument::REQUIRED, 'The name of the class'],
        ];
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     * @param null $exchange
     * @param null $routingKey
     * @param null $queue
     * @return string
     */
    protected function buildClass($name, $exchange = null, $routingKey = null, $queue = null)
    {
        $stub = file_get_contents($this->getStub());

        return $this->replaceNamespace($stub, $name)
            ->replaceExchange($stub, $exchange)
            ->replaceRoutingKey($stub, $routingKey)
            ->replaceQueue($stub, $queue)
            ->replaceClass($stub, $name);
    }

    /**
     * Replace the exchange for the given stub.
     *
     * @param $stub
     * @param $exchange
     * @return $this
     */
    protected function replaceExchange(&$stub, $exchange)
    {
        $stub = str_replace(
            ['%EXCHANGE%'],
            [$exchange],
            $stub
        );

        return $this;
    }

    /**
     * Replace the routing key for the given stub.
     *
     * @param $stub
     * @param $routingKey
     * @return $this
     */
    protected function replaceRoutingKey(&$stub, $routingKey)
    {
        $stub = str_replace(
            ['%ROUTINGKEY%'],
            [$routingKey],
            $stub
        );

        return $this;
    }

    /**
     * Replace the queue for the given stub.
     *
     * @param $stub
     * @param $queue
     * @return $this
     */
    protected function replaceQueue(&$stub, $queue)
    {
        $stub = str_replace(
            ['%QUEUE%'],
            [$queue],
            $stub
        );

        return $this;
    }

    /**
     * Get the desired exchange from the input.
     *
     * @return string
     */
    protected function getExchangeInput()
    {
        return trim($this->input->getArgument('exchange'));
    }

    /**
     * Get the desired routing key from the input.
     *
     * @return string
     */
    protected function getRoutingKeyInput()
    {
        return trim($this->input->getArgument('routingKey'));
    }

    /**
     * Get the desired queue from the input.
     *
     * @return string
     */
    protected function getQueueInput()
    {
        return trim($this->input->getArgument('queue'));
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return $this->getConfig()['stub'] ?? __DIR__ . '/stubs/amqp-consumer.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @return string
     */
    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\\Amqp\\Consumer';
    }
}
