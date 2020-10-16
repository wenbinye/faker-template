<?php


namespace winwin\faker;


use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Console\Input\InputInterface;
use winwin\faker\providers\Dataset;
use winwin\faker\providers\DateTime;
use winwin\faker\providers\Increment;
use winwin\faker\providers\Input;
use winwin\faker\providers\Misc;

class GeneratorFactory
{
    /**
     * @var string|null
     */
    private $locale;
    /**
     * @var string[]|null
     */
    private $datasetPaths;
    /**
     * @var InputInterface|null
     */
    private $input;

    public function create(): Generator
    {
        $faker = Factory::create($this->locale ?? 'zh_CN');
        if (!empty($this->datasetPaths)) {
            $faker->addProvider(Dataset::create($faker, $this->datasetPaths));
        }
        $faker->addProvider(new Increment());
        $faker->addProvider(new Misc());
        $faker->addProvider(new DateTime($faker));
        if ($this->input !== null) {
            $faker->addProvider(new Input($this->input));
        }
        return $faker;
    }

    /**
     * @param string $locale
     * @return GeneratorFactory
     */
    public function setLocale(string $locale): GeneratorFactory
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @param string[] $datasetPaths
     * @return GeneratorFactory
     */
    public function setDatasetPaths(array $datasetPaths): GeneratorFactory
    {
        $this->datasetPaths = $datasetPaths;
        return $this;
    }

    /**
     * @param InputInterface $input
     * @return GeneratorFactory
     */
    public function setInput(InputInterface $input): GeneratorFactory
    {
        $this->input = $input;
        return $this;
    }
}