<?php

use App\Support\HtmlSanitizer;

if (!function_exists('safe_html')) {
    function safe_html(?string $html): ?string
    {
        return HtmlSanitizer::clean($html);
    }
}
