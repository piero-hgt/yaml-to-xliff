<?php

namespace Mooneye\Yaml2XliffConverter\Command;

use Mooneye\Yaml2XliffConverter\XLIFF\FileNotFoundException;
use Mooneye\Yaml2XliffConverter\XLIFF\Writer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Helper\ProgressBar;
use Traversable;

class ConvertCommand extends ContainerAwareCommand
{
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
            ->addArgument(
                'source-language',
                InputArgument::OPTIONAL,
                'Source language'
            )
            ->addArgument(
                'target-language',
                InputArgument::OPTIONAL,
                'Target language'
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
        $sourceLanguage = $this->prepareSourceLanguage($input);
        $targetLanguage = $this->prepareTargetLanguage($input);
        $yml = $this->prepareYML($inputFile);

        $ymlCount = count($yml);

        $progressBar = $style->createProgressBar($ymlCount);

        $this->convert(
            $yml,
            $outputFile,
            $sourceLanguage,
            $targetLanguage,
            $progressBar,
            false
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
     * @param ProgressBar $progress
     * @param bool $keepSpaces
     */
    private function convert(
        $yml,
        $outputFile,
        $sourceLanguage,
        $targetLanguage,
        ProgressBar $progress,
        $keepSpaces
    )
    {
        $writer = $this->createWriter();
        $writer->openUri($outputFile);
        $writer->startDocument();
        $writer->startFile($sourceLanguage, $targetLanguage);

        $id = 0;
        foreach ($yml as $source => $target) {
            $id++;
            $writer->writeTransUnit(
                $id++,
                $source,
                $target,
                false,
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
        if (NULL === $outputFile) {
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
        if (FALSE === file_exists($inputFile)) {
            throw new FileNotFoundException('Input file not found.');
        }
        return $inputFile;
    }

    /**
     * @param InputInterface $input
     * @return mixed
     */
    private function prepareSourceLanguage($input)
    {
        $sourceLanguage = $input->getArgument('source-language');
        if (NULL === $sourceLanguage) {
            $sourceLanguage = 'de';
        }
        return $sourceLanguage;
    }

    /**
     * @param InputInterface $input
     * @return mixed
     */
    private function prepareTargetLanguage($input)
    {
        $targetLanguage = $input->getArgument('target-language');
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
    protected function flat(array $source, $flattened = [], $currentKey = '')
    {
        foreach ($source as $key => $value) {
            $newKey = ('' === $currentKey) ? $key : $currentKey . '.' . $key;
            if (TRUE === is_array($value)) {
                $flattened = $this->flat($value, $flattened, $newKey);
            } else {
                $flattened[$newKey] = $value;
            }
        }
        return $flattened;
    }
}
