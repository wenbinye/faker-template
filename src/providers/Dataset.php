<?php

namespace winwin\faker\providers;

use Faker\Generator;
use Faker\Provider\Base;

class Dataset extends Base
{
    /**
     * @var string[]
     */
    private $searchPaths;

    /**
     * @var array
     */
    private $dataSet;

    /**
     * @param string $baseDir
     */
    private function addPath(string $baseDir)
    {
        $this->searchPaths[$baseDir] = true;
    }

    private function setSearchPaths(array $paths)
    {
        $this->searchPaths = [];
        foreach ($paths as $path) {
            $this->addPath($path);
        }
    }

    public static function create(Generator $generator, array $searchPaths): Dataset
    {
        $provider = new static($generator);
        $provider->setSearchPaths($searchPaths);

        return $provider;
    }

    public function dataset($name)
    {
        if (!isset($this->dataSet[$name])) {
            $pathName = str_replace('.', '/', $name);
            $tryFiles = [];
            $found = false;
            foreach ($this->searchPaths as $path => $ignore) {
                $filename = $path.'/'.$pathName.'.json';
                $tryFiles[] = $filename;
                if (file_exists($filename)) {
                    $found = $filename;
                    break;
                }
            }
            if (!$found) {
                throw new \InvalidArgumentException("cannot find dataset '$name', try ".implode(',', $tryFiles));
            }
            $entries = json_decode(file_get_contents($found), true);
            if (!is_array($entries)) {
                throw new \InvalidArgumentException("dataset '$found' not array");
            }
            $this->dataSet[$name] = $entries;
        }

        return $this->dataSet[$name];
    }

    public function pickup($name)
    {
        return self::randomElement($this->dataset($name));
    }
}
