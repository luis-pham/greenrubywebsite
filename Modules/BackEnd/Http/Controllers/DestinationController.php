<?php
    namespace Modules\BackEnd\Http\Controllers;

    use App\Services\Content\ContentTranslations;
    use Illuminate\Http\Request;
    use Illuminate\Routing\Controller;
    use Illuminate\Support\Facades\Session;
    use Illuminate\Support\MessageBag;
    use Modules\BackEnd\Helpers\LanguageUtils;
    use Modules\BackEnd\Helpers\Logging;
    use Modules\BackEnd\Helpers\Utilities;
    use Modules\BackEnd\Http\Requests\DestinationRequest;
    use Modules\BackEnd\Services\AppDestinationService;

    /**
     * Destinations of the Next.js /destinations hub (one row per language; EN <-> VI pairs are set in
     * "Liên kết bản dịch", type "Điểm đến"). Only published rows are visible on the website.
     */
    class DestinationController extends Controller
    {
        private $baseView = 'backend::destination.';

        private const INPUT = [
            'name', 'heading', 'slug', 'bay', 'category', 'summary', 'content', 'facts', 'faq', 'sources',
            'latitude', 'longitude', 'coords_verified', 'coords_source', 'image_link', 'image_alt', 'image_credit',
            'status', 'editor_notes', 'fact_checked_at', 'seo_title', 'seo_description', 'ord', 'itinerary_ids', 'experience_ids',
        ];

        public function index(Request $request)
        {
            $language = LanguageUtils::getCurrentLanguage();
            $title = 'Quản lý điểm đến';
            \SEO::setTitle($title);

            $list = AppDestinationService::getPaging($request->only(['keyword', 'bay', 'category', 'status']), $language->id);

            Logging::logInfo('Xem danh sách điểm đến.');

            return view($this->baseView . __FUNCTION__, compact('title', 'list'));
        }

        public function create(Request $request)
        {
            $language = LanguageUtils::getCurrentLanguage();
            $title = 'Thêm điểm đến';
            \SEO::setTitle($title);

            $itineraryOptions = AppDestinationService::itineraryOptions($language->id);
            $itineraryIds = [];
            $experienceOptions = AppDestinationService::experienceOptions($language->id);
            $experienceIds = [];

            return view($this->baseView . __FUNCTION__, compact('title', 'itineraryOptions', 'itineraryIds', 'experienceOptions', 'experienceIds'));
        }

        public function store(DestinationRequest $request)
        {
            try {
                $language = LanguageUtils::getCurrentLanguage();
                $id = AppDestinationService::create($request->only(self::INPUT), $language->id);

                Logging::logInfo('Thêm điểm đến thành công.', 'id = ' . $id);

                return redirect($this->indexUrl($request))->with('flash-message', 'Thêm điểm đến thành công! Nhớ liên kết bản dịch EN/VI trong "Liên kết bản dịch".');
            } catch (\Exception $e) {
                Logging::logError('Thêm điểm đến lỗi.', 'Exception = ' . $e->getMessage());

                return redirect()->back()->withInput()->withErrors('Thêm điểm đến lỗi!');
            }
        }

        public function edit($id, Request $request)
        {
            $language = LanguageUtils::getCurrentLanguage();
            $title = 'Sửa điểm đến';
            \SEO::setTitle($title);

            $id = $request->route('id') ?? $id;
            $obj = AppDestinationService::find($id, $language->id);
            if (!$obj) {
                return abort(404);
            }

            $itineraryOptions = AppDestinationService::itineraryOptions($language->id);
            $itineraryIds = AppDestinationService::itineraryIds($obj->id);
            $experienceOptions = AppDestinationService::experienceOptions($language->id);
            $experienceIds = AppDestinationService::experienceIds($obj->id);
            $isLinked = ContentTranslations::isLinked('destination', (int) $obj->id);

            return view($this->baseView . __FUNCTION__, compact('title', 'obj', 'itineraryOptions', 'itineraryIds', 'experienceOptions', 'experienceIds', 'isLinked'));
        }

        public function update(DestinationRequest $request, $id)
        {
            $language = LanguageUtils::getCurrentLanguage();
            $id = $request->route('id') ?? $id;
            if (!AppDestinationService::find($id, $language->id)) {
                return abort(404);
            }

            try {
                $data = $request->only(self::INPUT);
                $data['id'] = $id;
                AppDestinationService::update($data, $language->id);

                Logging::logInfo('Sửa điểm đến thành công.', 'id = ' . $id);

                return redirect($this->indexUrl($request))->with('flash-message', 'Sửa điểm đến thành công!');
            } catch (\Exception $e) {
                Logging::logError('Sửa điểm đến lỗi.', 'id = ' . $id . '. Exception = ' . $e->getMessage());

                return redirect()->back()->withInput()->withErrors('Sửa điểm đến lỗi!');
            }
        }

        public function destroy(Request $request)
        {
            $data = $request->all();
            try {
                if (empty($data['id'])) {
                    throw new \Exception('Parameter invalid.');
                }
                AppDestinationService::delete($data['id'], LanguageUtils::getCurrentLanguage()->id);

                Session::flash('flash-message', 'Xóa điểm đến thành công!');
                Logging::logInfo('Xóa điểm đến thành công.', 'id = ' . json_encode($data['id']));

                return response()->json(['msg' => 'success']);
            } catch (\Exception $e) {
                Session::flash('errors', new MessageBag(['Xóa điểm đến lỗi!']));
                Logging::logError('Xóa điểm đến lỗi.', 'Exception = ' . $e->getMessage());

                return response()->json(['msg' => 'fail', 'err' => $e->getMessage()]);
            }
        }

        public function orderUpdate(Request $request)
        {
            try {
                AppDestinationService::saveOrder((array) $request->input('id', []), LanguageUtils::getCurrentLanguage()->id);

                return response()->json(['msg' => 'success']);
            } catch (\Exception $e) {
                Logging::logError('Sắp xếp điểm đến lỗi.', 'Exception = ' . $e->getMessage());

                return response()->json(['msg' => 'fail', 'err' => $e->getMessage()]);
            }
        }

        private function indexUrl(Request $request): string
        {
            $languageCode = $request->route('languageCode');

            return route(Utilities::getRouteName('backend.destination.index'), ['languageCode' => $languageCode, 'lastUrl' => $request->get('lastUrl')]);
        }
    }
