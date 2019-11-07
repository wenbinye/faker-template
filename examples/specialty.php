<?php

return [
    'id' => ['incrementId'],
    'name' => function($faker) {
        return new ArrayIterator($faker->dataset('specialty'));
    }
];
