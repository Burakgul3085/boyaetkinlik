<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Private gift page kill switch
    |--------------------------------------------------------------------------
    |
    | When false, the secret route returns 404 as if the page does not exist.
    |
    */
    'enabled' => (bool) env('PRIVATE_GIFT_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Pretty URL path (no long token)
    |--------------------------------------------------------------------------
    |
    | Production URL: https://boyaetkinlik.com/{path}
    | Example path: buraktanzeynebe
    |
    */
    'path' => (string) env('PRIVATE_GIFT_PATH', 'buraktanzeynebe'),

    'page_title' => 'Biraz Gülümse',

    'sender' => 'Burak',

    /*
    | Path relative to storage/app.
    | Place file at: storage/app/private/gift/profile.jpg
    */
    'photo_relative' => env('PRIVATE_GIFT_PHOTO', 'private/gift/profile.jpg'),

    'music' => [
        'primary' => [
            'artist' => 'Gripin',
            'title' => 'Nasip',
            'url' => env('PRIVATE_GIFT_TRACK_URL', 'https://youtu.be/uLxvx25Vhls'),
            // 1 dakika 42 saniye
            'start_seconds' => (int) env('PRIVATE_GIFT_TRACK_START', 102),
            'youtube_id' => env('PRIVATE_GIFT_YOUTUBE_ID', 'uLxvx25Vhls'),
            'autoplay_on_scene' => true,
        ],
        'secondary' => [
            'artist' => '',
            'title' => 'Yazdı Kâtip',
            'url' => env('PRIVATE_GIFT_SECOND_TRACK_URL'),
        ],
    ],

];
