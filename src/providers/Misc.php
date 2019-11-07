<?php

namespace winwin\faker\providers;

class Misc
{
    public function same($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }

    public function iterate(\Iterator $generator)
    {
        if ($generator->valid()) {
            $value = $generator->current();
            $generator->next();

            return $value;
        }

        throw new \OverflowException('generator overflow');
    }

    public function distribute($n, $precision = 2)
    {
        $avg = 1 / $n;
        $p = [];
        $total = 1;
        for ($i = 0; $i < $n; ++$i) {
            if ($i == $n - 1) {
                $p[] = $total;
            } elseif ($total > 0) {
                $p1 = min($total, $avg * mt_rand(0, 300) / 100);
                $p[] = round($p1, $precision);
                $total = 1 - array_sum($p);
            } else {
                $p[] = 0;
            }
        }

        return $p;
    }

    public function exhaust($values)
    {
        if (empty($values)) {
            yield [];

            return;
        }
        $keys = array_keys($values);
        $key = end($keys);
        $val = $values[$key];
        unset($values[$key]);

        foreach ($this->exhaust($values) as $rest) {
            foreach ($val as $item) {
                $rest[$key] = $item;
                yield $rest;
            }
        }
    }
}
