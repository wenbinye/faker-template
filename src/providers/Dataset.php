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
     * @var array
     */
    private $data;

    /**
     * @param string $baseDir
     */
    private function addPath(string $baseDir): void
    {
        $this->searchPaths[$baseDir] = true;
    }

    private function setSearchPaths(array $paths): void
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
            $this->dataSet[$name] = $this->parseJsonLines($found);
        }

        return $this->dataSet[$name];
    }

    public function pickup($name)
    {
        if (!isset($this->data[$name])) {
            $this->data[$name] = iterator_to_array($this->dataset($name));
        }
        return self::randomElement($this->data[$name]);
    }

    protected function parseJsonLines(string $file)
    {
        $fp = fopen($file, "rb");
        $line = trim(fgets($fp));
        if ($line === '[') {
            $entries = json_decode(file_get_contents($file), true);
            if (!is_array($entries)) {
                throw new \InvalidArgumentException("dataset '$file' not array");
            }
            return new \ArrayIterator($entries);
        } else {
            return $this->iterateJsonFile($fp);
        }
    }

    private function iterateJsonFile($fp)
    {
        fseek($fp, 0);
        while ($line = fgets($fp)) {
            yield json_decode($line, true);
        }
        fclose($fp);
    }
}
