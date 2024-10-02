<?php

// config for Altwaireb/World
return [
    'insert_activations_only' => false,
    'countries' => [
        'activation' => [
            'default' => true,
            'only' => [
                'iso2' => [],
                'iso3' => [],
            ],
            'except' => [
                'iso2' => [],
                'iso3' => [],
            ],
        ],
        'chunk_length' => 50,
    ],

    'states' => [
        'activation' => [
            'default' => true,
        ],
        'chunk_length' => 200,
    ],

    'cities' => [
        'activation' => [
            'default' => true,
        ],
        'chunk_length' => 200,
    ],
];
