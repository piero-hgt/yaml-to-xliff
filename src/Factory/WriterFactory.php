<?php

namespace Mooneye\Yaml2XliffConverter\Factory;

use Mooneye\Yaml2XliffConverter\XML\IDGenerator;
use Mooneye\Yaml2XliffConverter\XML\Writer;

class WriterFactory
{
    /**
     * @var IDGenerator
     */
    private $generator;

    public function __construct(IDGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @return Writer
     */
    public function createWriter()
    {
        return new Writer($this->generator);
    }
}
