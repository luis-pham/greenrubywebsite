<?php

return [
    'labels' => [
        'ocean_view' => 'Ocean view',
        'main_deck' => 'Main Deck',
        'upper_deck' => 'Upper Deck',
        'upper_deck_front' => 'Upper Deck · Front',
        'upper_deck_rear' => 'Upper Deck · Rear',
        'bathtub' => 'Bathtub',
        'jacuzzi' => 'Jacuzzi',
        'jacuzzi_balcony' => 'Jacuzzi Balcony',
        'butler' => 'Butler',
        'in_room_dining' => 'In-room Dining',
        'on_board' => 'On Board',
        'guests' => ':count Guests',
        'cabin' => 'Cabin',
    ],
    'categories' => [
        'serenity_deluxe' => 'Deluxe Cabin',
        'ocean_breeze_premium' => 'Premium Cabin',
        'royal_romance_suite' => 'Suite',
        'imperial_suite' => 'Signature Suite',
    ],
    'summaries' => [
        'serenity_deluxe' => 'Ocean-view cabin with bathtub on Main Deck.',
        'ocean_breeze_premium' => 'Upper Deck premium with bathtub and bay views.',
        'royal_romance_suite' => 'Suite with jacuzzi, butler, and panoramic views.',
        'imperial_suite' => 'Our largest suite with jacuzzi balcony and butler.',
    ],
    'badges' => [
        'ai_concierge' => 'AI Concierge',
        'largest_suite' => 'Largest Suite',
    ],
    'spec_sets' => [
        'serenity_deluxe' => [
            ['icon' => 'eye', 'label' => 'ocean_view'],
            ['icon' => 'home', 'label' => 'main_deck'],
            ['icon' => 'bath', 'label' => 'bathtub'],
            ['icon' => 'maximize', 'label' => 'area'],
        ],
        'ocean_breeze_premium' => [
            ['icon' => 'eye', 'label' => 'ocean_view'],
            ['icon' => 'home', 'label' => 'upper_deck'],
            ['icon' => 'bath', 'label' => 'bathtub'],
            ['icon' => 'maximize', 'label' => 'area'],
        ],
        'royal_romance_suite' => [
            ['icon' => 'eye', 'label' => 'ocean_view'],
            ['icon' => 'home', 'label' => 'upper_deck'],
            ['icon' => 'bath', 'label' => 'jacuzzi'],
            ['icon' => 'bell', 'label' => 'butler'],
        ],
        'imperial_suite' => [
            ['icon' => 'eye', 'label' => 'ocean_view'],
            ['icon' => 'bath', 'label' => 'jacuzzi_balcony'],
            ['icon' => 'coffee', 'label' => 'in_room_dining'],
            ['icon' => 'bell', 'label' => 'butler'],
        ],
    ],
    'deck_badges' => [
        'serenity_deluxe' => 'main_deck',
        'ocean_breeze_premium' => 'upper_deck',
        'royal_romance_suite' => 'upper_deck_front',
        'imperial_suite' => 'upper_deck_rear',
    ],
];
