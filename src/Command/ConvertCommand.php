<?php

namespace Mooneye\Yaml2XliffConverter\Command;

use Mooneye\Yaml2XliffConverter\XLIFF\FileNotFoundException;
use Mooneye\Yaml2XliffConverter\XLIFF\Writer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Helper\ProgressBar;

class ConvertCommand extends ContainerAwareCommand
{
    private $id;

    protected function configure()
    {
        $this->setName('convert')
            ->setDescription('Converting YAML files to XLIFF stuff.')
            ->addArgument(
                'input-file',
                InputArgument::REQUIRED,
                'YAML file to convert'
            )
            ->addArgument(
                'output-file',
                InputArgument::OPTIONAL,
                'XLIFF output dir'
            )
            ->addOption(
                'source-language',
                's',
                InputOption::VALUE_OPTIONAL,
                'Source language',
                'de'
            )
            ->addOption(
                'target-language',
                't',
                InputOption::VALUE_OPTIONAL,
                'Target language',
                'de'
            )
            ->addOption(
                'keep-spaces',
                'k',
                InputOption::VALUE_OPTIONAL,
                'Preserver Spaces?',
                false
            )
            ->addOption(
                'use-id',
                'i',
                InputOption::VALUE_OPTIONAL,
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
            sprintf('XLIFF Output: %s ', $outputFile),
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

        $this->id = 0;
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
            ->get('xliff_writer_factory')
            ->createWriter();
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
                $this->getId($useId),
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
        $yml = Yaml::parse($inputFile);
        return $this->flat($yml);
    }

    /**
     * @param  array $source
     * @param  array $flattened
     * @param  string $currentKey
     * @return array
     */
    private function flat(array $source, $flattened = [], $currentKey = '')
    {
        foreach ($source as $key => $value) {
            $newKey = ('' === $currentKey) ? $key : $currentKey . '.' . $key;
            if (true === is_array($value)) {
                $flattened = $this->flat($value, $flattened, $newKey);
            } else {
                $flattened[$newKey] = $value;
            }
        }
        return $flattened;
    }

    /**
     * @param bool $useId
     * @return int|null
     */
    private function getId($useId)
    {
        if(true === $useId) {
            return $this->id++;
        } else {
            return null;
        }
    }
}
