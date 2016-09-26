<?php

namespace Mooneye\Yaml2XliffConverter\YAML;

use Symfony\Component\Yaml\Yaml;

class Reducer
{
    /**
     * @param $inputFile
     * @return array
     */
    public  function getReduced($inputFile){
        $yml = Yaml::parse($inputFile);
        return $this->reduce($yml);
    }

    /**
     * @param  array $source
     * @param  array $reduced
     * @param  string $currentKey
     * @return array
     */
    private function reduce(array $source, $reduced = [], $currentKey = '')
    {
        foreach ($source as $key => $value) {
            $newKey = ('' === $currentKey) ? $key : $currentKey . '.' . $key;
            if (true === is_array($value)) {
                $reduced = $this->reduce($value, $reduced, $newKey);
            } else {
                $reduced[$newKey] = $value;
            }
        }
        return $reduced;
    }
}
