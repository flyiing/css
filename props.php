<?php

/**
 * @return array Описания css-свойств
 */

return [

    'color' => [
        'type' => 'color',
        'default' => '#000000',
    ],
    'background-color' => [
        'type' => 'color',
        'default' => '#ffffff',
    ],

    'border' => [
        'type' => 'border',
        'default' => 1,
    ],
    'border-top' => [
        'parent' => 'border',
    ],
    'border-right' => [
        'parent' => 'border',
    ],
    'border-bottom' => [
        'parent' => 'border',
    ],
    'border-left' => [
        'parent' => 'border',
    ],

    'padding' => [
        'type' => 'size-px',
        'default' => 1,
    ],
    'padding-top' => [
        'parent' => 'padding',
    ],
    'padding-right' => [
        'parent' => 'padding',
    ],
    'padding-bottom' => [
        'parent' => 'padding',
    ],
    'padding-left' => [
        'parent' => 'padding',
    ],

];
