<?php

namespace winwin\faker;

use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use winwin\faker\providers\Dataset;
use winwin\faker\providers\Increment;
use winwin\faker\providers\Input;
use winwin\faker\providers\Misc;

class GenerateCommand extends Command
{
    protected function configure()
    {
        $this->addOption('locale', 'l', InputOption::VALUE_REQUIRED, 'locale');
        $this->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'config file');
        $this->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'output file', 'php://stdout');
        $this->addOption('number', 'r', InputOption::VALUE_REQUIRED, 'number of record', 1);
        $this->addOption('date', null, InputOption::VALUE_REQUIRED, 'date', date('Y-m-d'));

        foreach ($this->parseOptions() as $opt) {
            $this->addOption($opt['name'], null, $opt['mode'], $opt['description'] ?? null, $opt['default'] ?? null);
        }
        $this->addArgument('template', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('config')) {
            Config::setConfigFile($input->getOption('config'));
        }
        $outputFile = $input->getOption('output');
        $locale = $input->getOption('locale') ?? Config::get('locale', 'zh_CN');
        $rowNumber = $input->getOption('number');
        $templateName = $input->getArgument('template');
        $templateDir = Config::get('template', '.');
        $datasetPaths = (array) Config::get('dataset', '.');

        if ('inf' === $rowNumber) {
            $rowNumber = PHP_INT_MAX;
        }

        $faker = Factory::create($locale);
        $faker->addProvider(Dataset::create($faker, $datasetPaths));
        $faker->addProvider(new Increment());
        $faker->addProvider(new Misc());
        $faker->addProvider(new Input($input));

        $template = $this->loadTemplate($faker, sprintf('%s/%s.php', $templateDir, $templateName));
        $entries = [];
        $i = 0;
        while ($i < $rowNumber) {
            ++$i;
            if (0 === $i % 1000) {
                $output->writeln(date('c')." <info>generate $i rows</info>");
            }
            try {
                $entries[] = $template();
            } catch (\OverflowException $e) {
                break;
            }
        }
        $jsonOptions = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        file_put_contents($outputFile, json_encode($entries, $jsonOptions));
    }

    private function loadTemplate(Generator $faker, string $templateFile): callable
    {
        if (!file_exists($templateFile)) {
            throw new \InvalidArgumentException("Cannot not load template '$templateFile'");
        }
        $data = require $templateFile;
        if (!is_array($data)) {
            throw new \InvalidArgumentException("Invalid template '$templateFile'");
        }

        return function () use ($faker, &$data, $templateFile) {
            $values = [];
            foreach ($data as $key => $template) {
                if (is_string($template)) {
                    $parts = explode('.', $template);
                    $value = $values;
                    while ($parts) {
                        $value = $value[array_shift($parts)] ?? null;
                    }
                } elseif (is_array($template)) {
                    if (!isset($template[0]) && !is_array($template[0])) {
                        throw new \InvalidArgumentException("Invalid template for key '$key' in '$templateFile'");
                    }
                    $method = array_shift($template);
                    $value = call_user_func_array([$faker, $method], $template);
                } elseif ($template instanceof \Closure) {
                    $value = $template($faker, $values);
                    if ($value instanceof \Generator) {
                        $data[$key] = $value;
                        $value = $faker->generator($value);
                    }
                } elseif ($template instanceof \Generator) {
                    $value = $faker->generator($template);
                } else {
                    throw new \InvalidArgumentException("Invalid template for key '$key' in '$templateFile'");
                }
                $values[$key] = $value;
            }
            foreach ($values as $key => $val) {
                if ('_' === $key[0]) {
                    unset($values[$key]);
                }
            }

            return $values;
        };
    }

    private function parseOptions()
    {
        $options = Config::get('options');
        if (empty($options)) {
            return [];
        }
        if (!is_array($options)) {
            throw new \InvalidArgumentException('Invalid config options');
        }
        foreach ($options as &$option) {
            if (!is_array($option)) {
                throw new \InvalidArgumentException('Invalid config options');
            }
            if (empty($option['name'])) {
                throw new \InvalidArgumentException('Invalid config options');
            }
            if (isset($option['mode'])) {
                $constant = constant(InputOption::class.'::VALUE_'.strtoupper($option['mode']));
                if (null === $constant) {
                    throw new \InvalidArgumentException('Invalid config mode for option '.$option['name']);
                }
                $option['mode'] = $constant;
            } else {
                $option['mode'] = InputOption::VALUE_REQUIRED;
            }
        }

        return $options;
    }
}
