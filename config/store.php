<?php

return [
    'admin' => [
        'name' => env('ADMIN_NAME', 'Admin Toko'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    'low_stock_threshold' => max(1, (int) env('STORE_LOW_STOCK_THRESHOLD', 5)),

    'cart_lifetime_hours' => max(1, (int) env('STORE_CART_LIFETIME_HOURS', 72)),

    'seed_demo_catalog' => env('STORE_SEED_DEMO', false),
];
