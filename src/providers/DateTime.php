<?php

namespace winwin\faker\providers;

use Faker\Generator;
use Faker\Provider\Base;

class DateTime extends Base
{
    public function date($format = 'Y-m-d', $since = '-1 year', $end = 'now'): string
    {
        return $this->generator->dateTimeBetween($since, $end)->format($format);
    }

    public function dateTime($format = 'Y-m-d H:i:s', $since = '-1 year', $end = 'now'): string
    {
        return $this->generator->dateTimeBetween($since, $end)->format($format);
    }
}
