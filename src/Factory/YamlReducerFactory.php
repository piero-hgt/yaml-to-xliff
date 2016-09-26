<?php

namespace Mooneye\Yaml2XliffConverter\Factory;

use Mooneye\Yaml2XliffConverter\YAML\Reducer;

class YamlReducerFactory
{
    /**
     * @return Reducer
     */
    public function createReducer()
    {
        return new Reducer();
    }

}
