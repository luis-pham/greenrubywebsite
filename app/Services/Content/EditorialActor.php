<?php

namespace App\Services\Content;

use Illuminate\Support\Facades\Auth;
use Modules\BackEnd\Entities\AdUser;

/**
 * The admin user a CLI import acts as (`--actor=<ad_user id>`, phase 2C). It must be an existing, active admin
 * account: it is recorded as translator, revision author, row stamp (created_by/updated_by) and audit actor.
 * Without one the CLI acts as null (the trusted operator), which cannot create rows whose admin service needs a
 * signed-in user (article categories: AppCategoryService through BaseModel's created_by).
 */
final class EditorialActor
{
    /** Types whose creation needs a real admin user (TranslationFactory goes through an admin service). */
    public const NEEDS_ACTOR = ['article_category'];

    /**
     * Resolves and signs in the --actor option (null/'' = the CLI, signed out).
     * @throws \InvalidArgumentException with a message for the operator
     */
    public static function signIn($option): ?int
    {
        if ($option === null || $option === '') {
            Auth::guard('admin')->forgetUser(); // the CLI: recorded as null

            return null;
        }
        if (!ctype_digit((string) $option)) {
            throw new \InvalidArgumentException("--actor must be an ad_user id, got '{$option}'.");
        }
        $user = AdUser::query()->find((int) $option);
        if (!$user) {
            throw new \InvalidArgumentException("Unknown --actor {$option}: no such admin user (ad_user).");
        }
        $active = (int) config('backend.userStatus.actived', 1);
        if ($user->status !== null && (int) $user->status !== $active) {
            throw new \InvalidArgumentException("--actor {$option} ({$user->username}) is not an active admin account (status {$user->status}).");
        }
        Auth::guard('admin')->setUser($user); // observers and model stamps record the actor
        Auth::shouldUse('admin');

        return (int) $user->id;
    }

    /** Why TranslationFactory cannot create $type for $actor now, or null. */
    public static function refusal(string $type, ?int $actor): ?string
    {
        if (in_array($type, self::NEEDS_ACTOR, true) && $actor === null) {
            return 'creating an article category needs a real admin user: pass --actor=<ad_user id> (recorded in the audit)';
        }

        return null;
    }
}
