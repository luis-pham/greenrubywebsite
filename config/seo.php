<?php

/*
| SEO content governance (docs/seo-audit/14-content-governance.md).
|
| override_roles: ad_role ids allowed to override a blocking cannibalization check, with a written reason
|   (1 = Quản trị hệ thống). Every check and override is written to app_seo_guard_log.
| guard: set SEO_GUARD=false to disable the publish / AI-queue gates (checks still run and are logged).
*/

return [
    'override_roles' => array_map('intval', array_filter(explode(',', (string) env('SEO_OVERRIDE_ROLES', '1')))),
    'guard' => (bool) env('SEO_GUARD', true),
];
