<?php

namespace App\Services\Editor;

use App\Services\Locale\LocaleRegistry;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Modules\BackEnd\Entities\AdLanguage;

/**
 * Languages of the editorial app (greenruby-write) and its editor API (phase 1E-e): the registry's editable
 * languages (editing or public), not a hard-coded EN/VI list. An unknown or disabled language is refused with a
 * validation error, never silently treated as English. en/vi keep their ids (2/1), so their results are unchanged.
 */
final class EditorLanguages
{
    /** Scripts measured in characters, not words (no spaces between words). */
    private const CHARACTER_SCRIPTS = ['ja', 'zh', 'ko'];

    /** @return Collection<int, AdLanguage> */
    public static function all(): Collection
    {
        return LocaleRegistry::editable()->values();
    }

    /** code => ad_language.id of every editable language ('vi' => 1, 'en' => 2, …). */
    public static function ids(): array
    {
        return self::all()->mapWithKeys(fn (AdLanguage $l) => [strtolower((string) $l->code) => (int) $l->id])->all();
    }

    public static function idOf(?string $code): ?int
    {
        return $code === null ? null : (self::ids()[strtolower(trim($code))] ?? null);
    }

    /** The id of $code, or a 422 naming the editable languages. */
    public static function require(?string $code, string $field = 'lang'): int
    {
        $id = self::idOf($code);
        if ($id === null) {
            throw ValidationException::withMessages([$field => 'Ngôn ngữ không hợp lệ hoặc chưa được bật để biên tập. Chọn một trong: ' . implode(', ', array_keys(self::ids())) . '.']);
        }

        return $id;
    }

    /** Code of an ad_language id ('vi', 'en', 'ja'…), or null. */
    public static function codeOf(int $id): ?string
    {
        $language = LocaleRegistry::byId($id);

        return $language ? strtolower((string) $language->code) : null;
    }

    /** The language's AI style guide file, or null: drafting in a language without one is refused. */
    public static function styleGuide(string $code): ?string
    {
        $code = strtolower($code);
        $path = resource_path($code === 'en' ? 'ai/style-guide.md' : "ai/style-guide-{$code}.md");

        return is_file($path) ? $path : null;
    }

    /** SEO length guidance: characters for title/description, and whether the body is counted in characters. */
    public static function seoLimits(string $code): array
    {
        $chars = self::countsCharacters($code);

        return $chars
            ? ['titleMax' => 32, 'descriptionMax' => 80, 'metric' => 'characters']
            : ['titleMax' => 60, 'descriptionMax' => 160, 'metric' => 'words'];
    }

    public static function countsCharacters(string $code): bool
    {
        return in_array(explode('-', strtolower($code))[0], self::CHARACTER_SCRIPTS, true);
    }

    /** GET /api/v1/editor/locales payload. */
    public static function describe(): array
    {
        return self::all()->map(fn (AdLanguage $l) => [
            'code' => strtolower((string) $l->code),
            'nativeName' => $l->getAttribute('native_name') ?? $l->name,
            'englishName' => $l->getAttribute('english_name') ?? $l->name,
            'status' => LocaleRegistry::status($l),
            'legacy' => LocaleRegistry::isLegacy($l),
            'aiDrafting' => self::styleGuide((string) $l->code) !== null,
        ] + self::seoLimits((string) $l->code))->values()->all();
    }
}
