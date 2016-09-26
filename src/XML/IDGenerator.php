<?php

namespace Mooneye\Yaml2XliffConverter\XML;

class IDGenerator
{
    private $id;

    public function __construct()
    {
        $this->id = 0;
    }

    /**
     * @return int
     */
    public function get()
    {
       return $this->id++;
    }
}
