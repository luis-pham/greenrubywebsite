<?php

/*
| Writing app (write.greenrubycruises.com, greenruby-write).
|
| public_view: when true, anyone with the link can open the app without logging in, read-only
| (articles, plan, media library). Saving, publishing, uploads and AI actions still need a login.
| Turn on to share drafts with the team, then off again:
|   .env  WRITE_PUBLIC_VIEW=true|false
|   docker exec green-ruby-cms php artisan config:cache
*/

return [
    'public_view' => (bool) env('WRITE_PUBLIC_VIEW', false),
];
