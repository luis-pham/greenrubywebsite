<?php
namespace Modules\BackEnd\Http\Controllers;

use App\Services\Content\ContentTranslations;
use App\Services\Content\Freshness;
use App\Services\Content\TranslatableFields;
use App\Services\Content\TranslationFactory;
use App\Services\Content\TranslationMatrix;
use App\Services\Locale\LocaleRegistry;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Helpers\Logging;
use Modules\BackEnd\Services\AdLanguageService;

/**
 * "Liên kết bản dịch" (phase 1E-b): the N-language translation matrix. One row per default-language entity, one
 * column per other editable language, each cell with its publication, workflow and freshness. Actions: create a
 * translation (TranslationFactory: unpublished text draft), open its editor, attach an existing row, detach one row,
 * inspect the link history, undo one logged change. Every link change goes through the 1C-4 transactional service
 * (ContentTranslations::attach/detach/undoEntry), is logged, and can never regroup or drop another member.
 * The previous EN↔VI pairing form is kept as the "Ghép EN/VI" view (?view=pairs), unchanged.
 */
class TranslationController extends Controller
{
    private $baseView = 'backend::translation.';

    public function index(Request $request)
    {
        if ($request->input('view') !== 'pairs') {
            \Illuminate\Support\Facades\Gate::authorize('translation-edit');

            return $this->matrix($request);
        }
        \Illuminate\Support\Facades\Gate::authorize('config-update'); // the EN/VI pairing form, as before
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

    private function matrix(Request $request)
    {
        $type = $this->matrixType($request);
        $filters = array_filter($request->only(['language', 'state', 'outdated']));
        $matrix = TranslationMatrix::build($type, $filters);
        $title = 'Liên kết bản dịch';
        \SEO::setTitle($title);
        $types = collect(TranslatableFields::TYPES)->map(fn ($t) => $t['label']);
        $grouped = TranslatableFields::of($type)['grouped'];
        $allLanguages = LocaleRegistry::editable()->filter(fn ($l) => !$l->is_default)->values();

        return view($this->baseView . 'matrix', $matrix + compact('title', 'type', 'types', 'filters', 'grouped', 'allLanguages'));
    }

    public function create(Request $request)
    {
        $type = $this->matrixType($request);
        $language = LocaleRegistry::byCode((string) $request->input('language'));
        abort_unless($language, 404);
        try {
            $result = TranslationFactory::create($type, (int) $request->input('source_id'), $language, Auth::id());
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors('Không tạo được bản dịch: ' . $e->getMessage());
        }
        Logging::LogInfo('Tạo bản dịch nháp.', "type = $type, source = {$request->input('source_id')}, id = {$result['id']}, language = {$language->code}");
        $edit = \Modules\BackEnd\Helpers\AdminLinks::edit($type, $result['id'], $language);

        return redirect($edit ?? route('backend.translation.index', ['type' => $type]))
            ->with('flash-message', "Đã tạo bản nháp {$language->code} #{$result['id']} (chưa xuất bản). Hãy dịch nội dung và đặt slug.");
    }

    public function attach(Request $request)
    {
        $type = $this->groupedType($request);
        $reason = $this->reason($request);
        try {
            ContentTranslations::attach($type, (int) $request->input('source_id'), (int) $request->input('target_id'), Auth::id(), $reason);
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors('Không gắn được bản dịch: ' . $e->getMessage());
        }
        Logging::LogInfo('Gắn bản dịch có sẵn.', "type = $type, source = {$request->input('source_id')}, target = {$request->input('target_id')}");

        return redirect(route('backend.translation.index', ['type' => $type]))->with('flash-message', 'Đã gắn bản dịch vào nhóm.');
    }

    public function detach(Request $request)
    {
        $type = $this->groupedType($request);
        $id = (int) $request->input('id');
        $reason = $this->reason($request);
        $languageId = (int) DB::table('content_translations')->where('entity_type', $type)->where('entity_id', $id)->value('language_id');
        $language = LocaleRegistry::byId($languageId);
        // An EN/VI member leaves its pair only with an explicit confirmation (never silently).
        if ($language && LocaleRegistry::isLegacy($language) && !$request->boolean('confirm_legacy')) {
            return redirect()->back()->withErrors('Gỡ bản ' . strtoupper($language->code) . ' khỏi cặp EN/VI cần đánh dấu xác nhận.');
        }
        ContentTranslations::detach($type, $id, Auth::id(), $reason);
        Logging::LogInfo('Gỡ một bản dịch khỏi nhóm.', "type = $type, id = $id, reason = $reason");

        return redirect(route('backend.translation.index', ['type' => $type]))->with('flash-message', "Đã gỡ #{$id} khỏi nhóm (các bản khác giữ nguyên).");
    }

    public function history(Request $request)
    {
        $type = $this->groupedType($request);
        $id = (int) $request->input('id');
        $title = 'Lịch sử liên kết bản dịch';
        \SEO::setTitle($title);
        $entries = ContentTranslations::history($type, $id);
        $languages = LocaleRegistry::all()->keyBy('id');

        return view($this->baseView . 'history', compact('title', 'type', 'id', 'entries', 'languages'));
    }

    public function undo(Request $request)
    {
        $type = $this->groupedType($request);
        $reason = $this->reason($request);
        try {
            ContentTranslations::undoEntry((int) $request->input('log_id'), Auth::id(), $reason);
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
        Logging::LogInfo('Hoàn tác một thay đổi liên kết bản dịch.', "log = {$request->input('log_id')}, reason = $reason");

        return redirect(route('backend.translation.history', ['type' => $type, 'id' => $request->input('id')]))->with('flash-message', 'Đã hoàn tác.');
    }

    public function confirmFresh(Request $request)
    {
        $type = $this->matrixType($request);
        try {
            Freshness::confirm($type, (int) $request->input('id'), Auth::id());
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect()->back()->with('flash-message', 'Đã đánh dấu: bản dịch đã đối chiếu với bản gốc hiện tại.');
    }

    private function matrixType(Request $request): string
    {
        $type = (string) $request->input('type', 'cruise');

        return TranslatableFields::has($type) ? $type : 'cruise';
    }

    private function groupedType(Request $request): string
    {
        $type = $this->matrixType($request);
        abort_unless(TranslatableFields::of($type)['grouped'], 422, 'Loại này không có nhóm bản dịch.');

        return $type;
    }

    /** A reason of at least 10 characters (logged with every link change). */
    private function reason(Request $request): string
    {
        $reason = trim((string) $request->input('reason'));
        abort_if(mb_strlen($reason) < 10, 422, 'Ghi lý do (ít nhất 10 ký tự).');

        return $reason;
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

            return redirect(route('backend.translation.index', ['type' => $type, 'view' => 'pairs']))->with('flash-message', "Đã lưu $changed thay đổi liên kết bản dịch.");
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
