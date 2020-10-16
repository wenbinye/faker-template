<?php

return [
    'id' => ['incrementId'],
    'name' => function($faker) {
        return $faker->dataset('pet_type');
    }
];
