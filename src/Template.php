<?php


namespace winwin\faker;


use Faker\Generator;

class Template implements \Iterator
{
    /**
     * @var Generator
     */
    private $faker;

    /**
     * @var array
     */
    private $template;

    /**
     * @var array
     */
    private $templateData;

    /**
     * @var int
     */
    private $maxItems = PHP_INT_MAX;

    /**
     * @var int
     */
    private $pos = 0;

    /**
     * @var array
     */
    private $current;

    /**
     * Template constructor.
     * @param Generator $faker
     * @param array $template
     */
    public function __construct(Generator $faker, array $template)
    {
        $this->faker = $faker;
        $this->template = $template;
    }

    /**
     * @param int $maxItems
     */
    public function setMaxItems(int $maxItems): void
    {
        $this->maxItems = $maxItems;
    }

    private function generateForString(string $template)
    {
        $parts = explode('.', $template);
        if (!isset($values[$parts[0]])) {
            $datasetKey = '_' . $parts['0'];
            if (!isset($values[$datasetKey])) {
                $values[$datasetKey] = $this->faker->pickup($parts[0]);
            }
            $parts[0] = $datasetKey;
        }
        $value = $values;
        while ($parts) {
            $value = $value[array_shift($parts)] ?? null;
        }
        return $value;
    }

    private function generateForArray(array $template)
    {
        if (!isset($template[0]) && !is_array($template[0])) {
            throw new \InvalidArgumentException("Invalid template");
        }
        $method = array_shift($template);
        return call_user_func_array([$this->faker, $method], $template);
    }

    private function generateForClosure(\Closure $template, string $key, array $values)
    {
        $value = $template($this->faker, $values);
        if ($value instanceof \Iterator) {
            $this->template[$key] = $value;
            return $this->faker->iterate($value);
        } else {
            return $value;
        }
    }

    public function generate(): array
    {
        $values = [];
        foreach ($this->template as $key => $template) {
            if (is_string($template)) {
                $value = $this->generateForString($template);
            } elseif (is_array($template)) {
                $value = $this->generateForArray($template);
            } elseif ($template instanceof \Closure) {
                $value = $this->generateForClosure($template, (string) $key, $values);
            } elseif ($template instanceof \Iterator) {
                $value = $this->faker->iterate($template);
            } else {
                throw new \InvalidArgumentException("Invalid template");
            }
            $values[$key] = $value;
        }
        foreach ($values as $key => $val) {
            if ('_' === $key[0]) {
                unset($values[$key]);
            }
        }

        return $values;
    }

    public function current()
    {
        return $this->current;
    }

    public function next()
    {
        $this->pos++;
    }

    public function key()
    {
        return $this->pos;
    }

    public function valid()
    {
        if ($this->pos >= $this->maxItems) {
            return false;
        }
        try {
            $this->current = $this->generate();
            return true;
        } catch (\OverflowException $e) {
            return false;
        }
    }

    public function rewind()
    {
        $this->pos = 0;
        $this->templateData = $this->template;
    }
}