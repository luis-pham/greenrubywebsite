<?php

$ids = fn (string $key, string $default) => array_values(array_filter(array_map('intval', explode(',', (string) env($key, $default)))));

/*
| Reservations desk (rsv.greenruby.cruises, app greenruby-rsv). Login = CMS admin accounts, but only
| the users below (by id, or by CMS role) may open it: it shows guests' personal data and payments.
*/
return [
    'user_ids' => $ids('RSV_USER_IDS', '1'),
    'role_ids' => $ids('RSV_ROLE_IDS', '1'),

    // Every contact-form message is stored, then e-mailed here.
    'contact_notify_to' => env('CONTACT_NOTIFY_TO', 'hello@greenrubycruises.com'),

    'statuses' => ['new', 'contacted', 'quoted', 'won', 'lost', 'spam'],
];
