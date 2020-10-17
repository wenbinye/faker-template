<?php

return [
    '_row' => function($faker) {
        while (true) {
            $pet = $faker->pickup('result/pet');
            $date = strtotime($faker->date());
            foreach (range(0, random_int(1, 2)) as $offset) {
                yield [
                    'pet_id' => $pet['id'],
                    'date' => date('Y-m-d', $date + 86400 * $offset)
                ];
            }
        }
    },
    'id' => ['incrementId'],
    'pet_id' => '_row.pet_id',
    'visit_date' => '_row.date',
    'description' => ['sentence', 3]
];