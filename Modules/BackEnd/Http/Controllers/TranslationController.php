<?php
namespace Modules\BackEnd\Http\Controllers;

use App\Services\Content\ContentTranslations;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BackEnd\Helpers\Logging;
use Modules\BackEnd\Services\AdLanguageService;

/**
 * "Liên kết bản dịch": pair each default-language row with its translation in the other language.
 * Pairs saved here drive hreflang, the sitemap, the language switcher and the Content API alternates.
 */
class TranslationController extends Controller
{
    private $baseView = 'backend::translation.';

    public function index(Request $request)
    {
        $type = $this->type($request);
        [$source, $target] = $this->languages();

        $title = 'Liên kết bản dịch';
        \SEO::setTitle($title);

        $targetItems = ContentTranslations::items($type, (int) $target->id)->keyBy('id');
        $rows = [];
        foreach (ContentTranslations::items($type, (int) $source->id) as $item) {
            $linked = ContentTranslations::isLinked($type, $item->id);
            $effectiveId = ContentTranslations::effectiveCounterpartId($type, $item->id, $source, $target);
            $rows[] = (object) [
                'item' => $item,
                'linked' => $linked,
                'selected' => $linked ? (ContentTranslations::linkedId($type, $item->id, (int) $target->id) ?: 'none') : '',
                'effective' => $effectiveId ? $targetItems->get($effectiveId) : null,
            ];
        }
        $pairedTargetIds = collect($rows)->pluck('effective')->filter()->pluck('id')->all();
        $unpairedTargets = $targetItems->except($pairedTargetIds)->values();

        $types = collect(ContentTranslations::TYPES)->map(fn ($t) => $t['label']);

        return view($this->baseView . __FUNCTION__, compact('title', 'type', 'types', 'source', 'target', 'rows', 'targetItems', 'unpairedTargets'));
    }

    public function update(Request $request)
    {
        $type = $this->type($request);
        [$source, $target] = $this->languages();
        $pairs = (array) $request->input('pair', []);

        $chosen = array_filter($pairs, fn ($v) => is_numeric($v));
        if (count($chosen) !== count(array_unique($chosen))) {
            return redirect()->back()->withErrors('Mỗi bản ' . strtoupper($target->code) . ' chỉ được gán cho một bản ' . strtoupper($source->code) . '.');
        }

        try {
            $changed = 0;
            foreach ($pairs as $sourceId => $value) {
                $sourceId = (int) $sourceId;
                $value = (string) ($value ?? ''); // "Tự động" arrives as null (ConvertEmptyStringsToNull)
                $current = ContentTranslations::isLinked($type, $sourceId)
                    ? (string) (ContentTranslations::linkedId($type, $sourceId, (int) $target->id) ?: 'none')
                    : '';
                if ($value === $current) {
                    continue;
                }

                if ($value === '') {
                    ContentTranslations::unlink($type, $sourceId);
                } elseif ($value === 'none') {
                    ContentTranslations::link($type, [$sourceId]);
                } else {
                    ContentTranslations::link($type, [$sourceId, (int) $value]);
                }
                $changed++;
            }

            Logging::LogInfo('Sửa liên kết bản dịch thành công.', "type = $type, changed = $changed");

            return redirect(route('backend.translation.index', ['type' => $type]))->with('flash-message', "Đã lưu $changed thay đổi liên kết bản dịch.");
        } catch (\Exception $e) {
            Logging::LogError('Sửa liên kết bản dịch lỗi.', 'Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Sửa liên kết bản dịch lỗi: ' . $e->getMessage());
        }
    }

    private function type(Request $request): string
    {
        $type = (string) $request->input('type', 'experience');

        return isset(ContentTranslations::TYPES[$type]) ? $type : 'experience';
    }

    /** [default language, the other one]. */
    private function languages(): array
    {
        $source = AdLanguageService::getDefaultLanguage();
        $target = AdLanguageService::getAll()->firstWhere('id', '!=', $source->id);

        return [$source, $target];
    }
}
