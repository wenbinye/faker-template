<?php

return [
    'vet_id' => function($faker) {
        foreach ($faker->dataset('result/vet') as $vet) {
            yield $vet['id'];
        }
    },
    'specialty_id' => 'result/specialty.id'
];
