<?php

namespace winwin\faker\providers;

class Increment
{
    private $offset = [];

    public function incrementId(string $name = 'default', int $start = 1): int
    {
        if (isset($this->offset[$name])) {
            ++$this->offset[$name];
        } else {
            $this->offset[$name] = $start;
        }

        return $this->offset[$name];
    }
}
