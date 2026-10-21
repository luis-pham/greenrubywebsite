<?php

/*
| Writing app (write.greenrubycruises.com, greenruby-write).
|
| public_view: when true, anyone with the link can open the app without logging in, read-only
| (articles, plan, media library). Saving, publishing, uploads and AI actions still need a login.
| Turn on to share drafts with the team, then off again:
|   .env  WRITE_PUBLIC_VIEW=true|false
|   docker restart green-ruby-cms && docker exec green-ruby-cms php artisan config:cache
| (.env is bind-mounted as a single file: editors replace the file, so the container only sees the
| change after a restart.)
*/

return [
    'public_view' => (bool) env('WRITE_PUBLIC_VIEW', false),
];
