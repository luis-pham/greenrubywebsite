<?php

return [
    'name' => 'BackEnd',
    'adUserAdmin' => 1,
    'displayDateFormat' => 'd/m/Y',
    'displayDateTimeFormat' => 'd/m/Y H:i',
    'displayTimeFormat' => 'H:i',
    'paginationLimit' => 20,
    'fileUploadMaxSize' => 204800,
    'fileTypeImage' => ['jpg', 'jpeg', 'gif', 'png', 'svg'],
    'fileTypeAudio' => ['mp3', 'mid', 'amr', 'wav', 'wma'],
    'fileTypeVideo' => ['mp4', 'avi', 'wmv'],
    'fileTypeOther' => ['zip', 'rar', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'csv'],
    'fileType' => [
        'image' => 1,
        'audio' => 2,
        'video' => 3,
        'other' => 4,
    ],
    'uploadPath' => '/upload',
    'logLevel' => [1,2,3],
    'logType' => [
        'info' => 1,
        'error' => 2,
        'system' => 3
    ],
    'userStatus' => [
        'unactive' => 0,
        'actived' => 1,
        'locked' => 2
    ],
    'userTheme' => [
        'default' => 0,
        'dark' => 1
    ],
    'userRole' => [
        'admin' => 1,
        'member' => 2
    ],
    'categoryType' => [
        'article' => 1
    ],
    'groupType' => [
        'faq' => 1,
        'cabin' => 2,
        'expActivity' => 3,
        'service' => 4,
        'suitableAudience' => 5
    ],
    'groupTabType' => [
        'expActivity' => [
            'onboard_activities' => 1,
            'outdoor_activities' => 2,
        ]
    ],
    'fileAttachObjectType' => [
        'cruise' => 1,
        'cabin' => 2,
        'expActivity' => 3,
        'service' => 4,
        'itinerary' => 5
    ],
    'configInput' => [
        'textbox' => 1,
        'textarea' => 2,
        'texteditor' => 3,
        'selectbox' => 4,
        'image' => 5,
        'gallery' => 6,
        'sourceData' => 7
    ],
    'appServiceType' => [
        'inclusive' => 1,
        'exclusive' => 2,
    ],
    'listAccommodationSlug' => ['accommodation','phong-o','cabin'],
    'facilityProfileSlugs' => [
        'accommodation' => 'cabin',
        'phong-o' => 'cabin',
        'cabin' => 'cabin',
        'restaurant' => 'onboard',
        'nha-hang' => 'onboard',
        'bar' => 'onboard',
        'quay-bar' => 'onboard',
        'gym' => 'onboard',
        'phong-gym' => 'onboard',
        'swimming-pool' => 'onboard',
        'be-boi' => 'onboard',
        'library' => 'onboard',
        'thu-vien' => 'onboard',
        'event-room' => 'event',
        'phong-su-kien' => 'event',
    ],
    'facilityProfileSections' => [
        'cabin' => ['view', 'cabin_class', 'price', 'rooms', 'amenities', 'capacity', 'area', 'over_capacity', 'discount', 'audience'],
        'onboard' => [],
        'event' => ['capacity', 'area'],
    ],
    'itineraryBay' => [
        1 => 'Vịnh Hạ Long',
        2 => 'Vịnh Lan Hạ'
    ]
];
