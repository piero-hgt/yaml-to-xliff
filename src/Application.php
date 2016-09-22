<?php

namespace Mooneye\Yaml2XliffConverter;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Application extends SymfonyApplication
{
    const BIN_NAME = 'yamlConvert2Xliff';
    const VERSION = '1.0';

    public function __construct()
    {
        parent::__construct(self::BIN_NAME, self::VERSION);
        $this->setAutoExit(true);
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultInputDefinition()
    {
        $inputDefinition = parent::getDefaultInputDefinition();
        return $inputDefinition;
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new Command\ConvertCommand();
        $commands[] = new Command\InfoCommand();

        return $commands;
    }

    /**
     * @inheritdoc
     */
    protected function doRunCommand(SymfonyCommand $command, InputInterface $input, OutputInterface $output)
    {
        if ($command instanceof ContainerAwareInterface) {
            $container = $this->createContainer();
            $command->setContainer($container);
        }

        $style = new SymfonyStyle($input, $output);
        $style->success('Starting Converter Job');

        return parent::doRunCommand($command, $input, $output);
    }

    /**
     * @return ContainerInterface
     */
    private function createContainer()
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new DependencyInjection\ConvertExtension());
        $container->loadFromExtension('yamlConvert2Xliff');
        $container->compile();
        return $container;
    }
}
