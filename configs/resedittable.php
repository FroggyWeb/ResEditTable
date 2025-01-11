<?php

return [
    'ids' => [4, 5],

    'show-children' => true,

    'lang' => [
        'documents_list' => 'Список элементов',
        'create_child' => 'Добавить элемент',
    ],

    'columns' => [
        'price' => [],
        'popular' => [],
        'image' => ['renderer' => function ($patch) {
            return \Helper::phpThumb($patch, 'w=48,h=48,zc=1,f=webp');

        }],
    ],

];
