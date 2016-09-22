<?php

namespace Mooneye\Yaml2XliffConverter\XLIFF;

class WriterFactory
{
    /**
     * @return Writer
     */
    public function createWriter()
    {
        return new Writer();
    }
}
