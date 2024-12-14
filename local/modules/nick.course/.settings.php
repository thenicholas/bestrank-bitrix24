<?php

return [
    'controllers' => [
        'value' => [
            'defaultNamespace' => '\\Nick\\Course\\Controller'
        ],
        'readonly' => true
    ],
    'intranet.customSection' => [
        'value' => [
            'provider' => '\\Nick\\Course\\Integration\\Intranet\\CustomSectionProvider',
        ],
    ],
];
