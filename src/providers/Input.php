<?php

namespace winwin\faker\providers;

use Symfony\Component\Console\Input\InputInterface;

class Input
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * Input constructor.
     *
     * @param InputInterface $input
     */
    public function __construct(InputInterface $input)
    {
        $this->input = $input;
    }

    public function inputOption($name)
    {
        return $this->input->getOption($name);
    }

    public function inputArgument($name)
    {
        return $this->input->getArgument($name);
    }
}
