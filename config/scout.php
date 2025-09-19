<?php
// config/scout.php

return [
    // ...
    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://meilisearch:7700'),
        'key'  => env('MEILISEARCH_KEY', null),

        // ⬇⬇⬇ ДОДАЙ ОЦЕ ⬇⬇⬇
        'index-settings' => [
            'products' => [
                // що дозволено фільтрувати/фасетити
                'filterableAttributes' => [
                    'is_active',
                    'category_id',
                    'attrs.color',
                    'attrs.size',
                    'price',
                ],
                // за чим дозволено сортувати (у тебе price/newest)
                'sortableAttributes' => [
                    'price',
                    'id',        // для "new" (сортуємо за id desc)
                ],
                'searchableAttributes' => [
                    'name',
                    'name_uk',
                    'name_en',
                    'name_ru',
                    'name_pt',
                    'description',
                    'description_uk',
                    'description_en',
                    'description_ru',
                    'description_pt',
                    'sku',
                    'attrs.color',
                    'attrs.color_uk',
                    'attrs.color_en',
                    'attrs.color_ru',
                    'attrs.color_pt',
                    'attrs.size',
                    'attrs.size_uk',
                    'attrs.size_en',
                    'attrs.size_ru',
                    'attrs.size_pt',
                ],
                // опційно (можеш не чіпати — Meili обере сам)
                // 'searchableAttributes' => ['name', 'sku'],
                // трошки QoL
                'faceting' => ['maxValuesPerFacet' => 200],
                'pagination' => ['maxTotalHits' => 1000],
            ],
        ],
    ],
];
