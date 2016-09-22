<?php

namespace Mooneye\Yaml2XliffConverter\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InfoCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('info')
            ->setDescription('Displays statistics about the converted files.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);
        $style->block(sprintf('The convertion was successful'), null, 'info');
    }
}
