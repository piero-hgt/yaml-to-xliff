<?php

namespace Mooneye\Yaml2XliffConverter\Command;

use Mooneye\Yaml2XliffConverter\Exceptions\FileNotFoundException;
use Mooneye\Yaml2XliffConverter\XML\Writer;
use Mooneye\Yaml2XliffConverter\YAML\Reducer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\ProgressBar;

class ConvertCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('convert')
            ->setDescription('Converting YAML files to XML stuff.')
            ->addArgument(
                'input-file',
                InputArgument::REQUIRED,
                'YAML file to convert'
            )
            ->addArgument(
                'output-file',
                InputArgument::OPTIONAL,
                'XML output dir'
            )
            ->addOption(
                'source-language',
                's',
                InputOption::VALUE_REQUIRED,
                'Source language',
                'de'
            )
            ->addOption(
                'target-language',
                't',
                InputOption::VALUE_REQUIRED,
                'Target language',
                'de'
            )
            ->addOption(
                'keep-spaces',
                'k',
                InputOption::VALUE_NONE,
                'Preserver Spaces?',
                false
            )
            ->addOption(
                'use-id',
                'i',
                InputOption::VALUE_NONE,
                'Use ID for trans-units?',
                false
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);

        $inputFile = $this->prepareInputFile($input);

        $style->block(
            sprintf('Converting YAML: %s ', $inputFile),
            null,
            'info'
        );

        $outputFile = $this->prepareOutputFile($input, $inputFile);

        $style->block(
            sprintf('XML Output: %s ', $outputFile),
            null,
            'info'
        );

        $sourceLanguage = $input->getOption('source-language');
        $targetLanguage = $this->prepareTargetLanguage($input);

        $style->block(
            sprintf('Target Language: %s ', $targetLanguage),
            null,
            'info'
        );

        $keepSpaces = $input->getOption('keep-spaces');

        $useId = $input->getOption('use-id');

        $yml = $this->prepareYML($inputFile);

        $ymlCount = count($yml);
        $style->block(
            sprintf('Total entries: %s ', $ymlCount),
            null,
            'info'
        );
        $progressBar = $style->createProgressBar($ymlCount);

        $this->convert(
            $yml,
            $outputFile,
            $sourceLanguage,
            $targetLanguage,
            $keepSpaces,
            $useId,
            $progressBar
        );

        $progressBar->finish();

        $style->newLine(2);
    }

    /**
     * @return Writer
     */
    private function createWriter()
    {
        return $this->container
            ->get('factory.xml_writer')
            ->createWriter();
    }

    /**
     * @return Reducer
     */
    private function createYAMLReducer()
    {
        return $this->container
            ->get('factory.yaml_reducer')
            ->createReducer();
    }

    /**
     * @param array $yml
     * @param string $outputFile
     * @param string $sourceLanguage
     * @param string $targetLanguage
     * @param bool $keepSpaces
     * @param bool $useId
     * @param ProgressBar $progress
     */
    private function convert(
        $yml,
        $outputFile,
        $sourceLanguage,
        $targetLanguage,
        $keepSpaces,
        $useId,
        ProgressBar $progress
    )
    {
        $writer = $this->createWriter();
        $writer->openUri($outputFile);
        $writer->startDocument();
        $writer->startFile($sourceLanguage, $targetLanguage);

        foreach ($yml as $source => $target) {
            $writer->writeTransUnit(
                $source,
                $target,
                $useId,
                $keepSpaces
            );
            $progress->advance();
        }

        $writer->endFile();
        $writer->endDocument();
    }

    /**
     * @param InputInterface $input
     * @param $inputFile
     * @return mixed
     */
    private function prepareOutputFile(InputInterface $input, $inputFile)
    {
        $outputFile = $input->getArgument('output-file');
        if (null === $outputFile) {
            $outputFile = dirname($inputFile) . '/' . basename($inputFile, 'yml') . 'xliff';
        }
        return $outputFile;
    }

    /**
     * @param InputInterface $input
     * @return mixed
     * @throws \Exception
     */
    private function prepareInputFile($input)
    {
        $inputFile = $input->getArgument('input-file');
        if (false === file_exists($inputFile)) {
            throw new FileNotFoundException('Input file not found.');
        }
        return $inputFile;
    }

    /**
     * @param InputInterface $input
     * @return mixed
     */
    private function prepareTargetLanguage($input)
    {
        $targetLanguage = $input->getOption('target-language');
        switch($targetLanguage){
            case 'en':
                return 'en-US';
                break;
            default:
                return 'de-DE';
        }
    }

    /**
     * @param $inputFile
     * @return array
     */
    private function prepareYML($inputFile)
    {
        $yamlReducer = $this->createYAMLReducer();
        return $yamlReducer->getReduced($inputFile);
    }
}
