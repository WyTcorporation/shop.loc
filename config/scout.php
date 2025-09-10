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
                // опційно (можеш не чіпати — Meili обере сам)
                // 'searchableAttributes' => ['name', 'sku'],
                // трошки QoL
                'faceting' => ['maxValuesPerFacet' => 200],
                'pagination' => ['maxTotalHits' => 1000],
            ],
        ],
    ],
];
