<?php

namespace winwin\faker;

use Faker\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends Command
{
    protected function configure()
    {
        $this->addOption('locale', 'l', InputOption::VALUE_REQUIRED, 'locale');
        $this->addOption('template', null, InputOption::VALUE_REQUIRED, 'template directory');
        $this->addOption('data', null, InputOption::VALUE_REQUIRED, 'date set directory');
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
        if ($input->getOption('date')) {
            $input->setOption('date', date('Y-m-d', strtotime($input->getOption('date'))));
        }
        $outputFile = $input->getOption('output');
        $locale = $input->getOption('locale') ?? Config::get('locale', 'zh_CN');
        $rowNumber = $input->getOption('number');
        $templateName = $input->getArgument('template');
        $templateDir = $input->getOption('template') ?? Config::get('template', '.');
        $datasetPaths = (array)($input->getOption('data') ?? Config::get('dataset', '.'));

        if ('inf' === $rowNumber) {
            $rowNumber = PHP_INT_MAX;
        }
        if (!empty($outputFile) && $outputFile !== 'php://stdout') {
            $outputDir = dirname($outputFile);
            if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
                throw new \RuntimeException("Cannot create output directory '$outputDir'");
            }
        }
        $faker = (new GeneratorFactory())
            ->setLocale($locale)
            ->setDatasetPaths($datasetPaths)
            ->setInput($input)
            ->create();

        $template = $this->loadTemplate($faker, sprintf('%s/%s', $templateDir, $templateName));
        $template->setMaxItems($rowNumber);
        $fp = fopen($outputFile, 'wb');
        foreach ($template as $i => $item) {
            if ($i > 0 && $i % 1000 === 0) {
                $output->writeln(date('c') . " <info>generate $i rows</info>");
            }
            fwrite($fp, json_encode($item, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
        }
        fclose($fp);
    }

    private function loadTemplate(Generator $faker, string $templateFile): Template
    {
        if (strrpos($templateFile, '.php') === false) {
            $templateFile .= '.php';
        }
        if (!file_exists($templateFile)) {
            throw new \InvalidArgumentException("Cannot not load template '$templateFile'");
        }
        $data = require $templateFile;
        if (!is_array($data)) {
            throw new \InvalidArgumentException("Invalid template '$templateFile'");
        }

        return new Template($faker, $data);
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
                $constant = constant(InputOption::class . '::VALUE_' . strtoupper($option['mode']));
                if (null === $constant) {
                    throw new \InvalidArgumentException('Invalid config mode for option ' . $option['name']);
                }
                $option['mode'] = $constant;
            } else {
                $option['mode'] = InputOption::VALUE_REQUIRED;
            }
        }

        return $options;
    }
}
