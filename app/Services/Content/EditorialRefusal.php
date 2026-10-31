<?php

namespace App\Services\Content;

/**
 * An editorial guard refused a save (1E hardening): business data of a text-only language, a revision another editor
 * holds, a structural change to published child content, publishing without approval. The message is written for the
 * editor and is shown in the admin instead of the generic "update failed" text. Anything else keeps that text.
 */
class EditorialRefusal extends \RuntimeException
{
    public static function message(\Throwable $e, string $fallback): string
    {
        return $e instanceof self ? $e->getMessage() : $fallback;
    }
}
