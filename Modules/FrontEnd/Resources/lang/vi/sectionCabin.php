<?php

return [
    'labels' => [
        'ocean_view' => 'View biển',
        'main_deck' => 'Tầng chính',
        'upper_deck' => 'Tầng trên',
        'upper_deck_front' => 'Tầng trên · Mũi tàu',
        'upper_deck_rear' => 'Tầng trên · Đuôi tàu',
        'bathtub' => 'Bồn tắm',
        'jacuzzi' => 'Jacuzzi',
        'jacuzzi_balcony' => 'Jacuzzi ban công',
        'butler' => 'Quản gia',
        'in_room_dining' => 'Ăn tại phòng',
        'on_board' => 'Trên tàu',
        'guests' => ':count khách',
        'cabin' => 'Cabin',
    ],
    'categories' => [
        'serenity_deluxe' => 'Phòng Deluxe',
        'ocean_breeze_premium' => 'Phòng Premium',
        'royal_romance_suite' => 'Suite',
        'imperial_suite' => 'Signature Suite',
    ],
    'summaries' => [
        'serenity_deluxe' => 'Phòng view biển với bồn tắm trên tầng chính.',
        'ocean_breeze_premium' => 'Phòng premium tầng trên với bồn tắm và view vịnh.',
        'royal_romance_suite' => 'Suite jacuzzi, quản gia và tầm nhìn toàn cảnh.',
        'imperial_suite' => 'Suite lớn nhất với jacuzzi ban công và quản gia.',
    ],
    'badges' => [
        'ai_concierge' => 'Trợ lý AI',
        'largest_suite' => 'Suite lớn nhất',
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
