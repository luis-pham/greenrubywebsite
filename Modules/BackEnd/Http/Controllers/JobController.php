<?php
    namespace Modules\BackEnd\Http\Controllers;

    use Illuminate\Http\Request;
    use Illuminate\Routing\Controller;
    use Illuminate\Support\Facades\Session;
    use Illuminate\Support\MessageBag;
    use Modules\BackEnd\Helpers\Logging;
    use Modules\BackEnd\Http\Requests\JobRequest;
    use Modules\BackEnd\Services\AppJobService;

    /**
     * Job postings of the website's /tuyen-dung pages (Vietnamese only, so no language in the admin URL).
     * Saving revalidates the website (RevalidateFrontend, entity "job").
     */
    class JobController extends Controller
    {
        private $baseView = 'backend::job.';

        private const INPUT = [
            'title', 'slug', 'department', 'employment_type', 'location', 'address_locality', 'address_region',
            'summary', 'intro', 'description', 'requirements', 'benefits', 'apply_email', 'status', 'posted_at',
            'deadline', 'seo_title', 'seo_description', 'ord',
        ];

        public function index(Request $request)
        {
            $title = 'Quản lý tuyển dụng';
            \SEO::setTitle($title);

            $list = AppJobService::getPaging($request->only(['keyword', 'status']));

            return view($this->baseView . __FUNCTION__, compact('title', 'list'));
        }

        public function create()
        {
            $title = 'Thêm vị trí tuyển dụng';
            \SEO::setTitle($title);
            $obj = null;

            return view($this->baseView . __FUNCTION__, compact('title', 'obj'));
        }

        public function store(JobRequest $request)
        {
            try {
                $id = AppJobService::create($request->only(self::INPUT));
                Logging::logInfo('Thêm vị trí tuyển dụng thành công.', 'id = ' . $id);

                return redirect($this->indexUrl($request))->with('flash-message', 'Thêm vị trí tuyển dụng thành công!');
            } catch (\Exception $e) {
                Logging::logError('Thêm vị trí tuyển dụng lỗi.', 'Exception = ' . $e->getMessage());

                return redirect()->back()->withInput()->withErrors('Thêm vị trí tuyển dụng lỗi!');
            }
        }

        public function edit($id)
        {
            $title = 'Sửa vị trí tuyển dụng';
            \SEO::setTitle($title);

            $obj = AppJobService::find($id);
            if (!$obj) {
                return abort(404);
            }

            return view($this->baseView . __FUNCTION__, compact('title', 'obj'));
        }

        public function update(JobRequest $request, $id)
        {
            if (!AppJobService::find($id)) {
                return abort(404);
            }

            try {
                $data = $request->only(self::INPUT);
                $data['id'] = $id;
                AppJobService::update($data);
                Logging::logInfo('Sửa vị trí tuyển dụng thành công.', 'id = ' . $id);

                return redirect($this->indexUrl($request))->with('flash-message', 'Sửa vị trí tuyển dụng thành công!');
            } catch (\Exception $e) {
                Logging::logError('Sửa vị trí tuyển dụng lỗi.', 'id = ' . $id . '. Exception = ' . $e->getMessage());

                return redirect()->back()->withInput()->withErrors('Sửa vị trí tuyển dụng lỗi!');
            }
        }

        /** "Đóng tuyển" / "Mở lại" from the list (POST id, status = closed|published). */
        public function status(Request $request)
        {
            $id = (int) $request->input('id');
            $status = (string) $request->input('status');
            $job = AppJobService::find($id);
            if (!$job || !in_array($status, ['published', 'closed'], true)) {
                return redirect()->back()->withErrors('Thao tác không hợp lệ!');
            }
            if ($status === 'published' && AppJobService::isExpired($job)) {
                return redirect()->back()->withErrors('Hạn nộp hồ sơ của "' . $job->title . '" đã qua: sửa hạn nộp trước khi mở lại.');
            }
            AppJobService::setStatus($id, $status);
            Logging::logInfo($status === 'closed' ? 'Đóng vị trí tuyển dụng.' : 'Mở lại vị trí tuyển dụng.', 'id = ' . $id);

            return redirect()->back()->with('flash-message', $status === 'closed' ? 'Đã đóng vị trí "' . $job->title . '".' : 'Đã mở lại vị trí "' . $job->title . '".');
        }

        public function destroy(Request $request)
        {
            $data = $request->all();
            try {
                if (empty($data['id'])) {
                    throw new \Exception('Parameter invalid.');
                }
                AppJobService::delete($data['id']);

                Session::flash('flash-message', 'Xóa vị trí tuyển dụng thành công!');
                Logging::logInfo('Xóa vị trí tuyển dụng thành công.', 'id = ' . json_encode($data['id']));

                return response()->json(['msg' => 'success']);
            } catch (\Exception $e) {
                Session::flash('errors', new MessageBag(['Xóa vị trí tuyển dụng lỗi!']));
                Logging::logError('Xóa vị trí tuyển dụng lỗi.', 'Exception = ' . $e->getMessage());

                return response()->json(['msg' => 'fail', 'err' => $e->getMessage()]);
            }
        }

        public function orderUpdate(Request $request)
        {
            try {
                AppJobService::saveOrder((array) $request->input('id', []));

                return response()->json(['msg' => 'success']);
            } catch (\Exception $e) {
                Logging::logError('Sắp xếp vị trí tuyển dụng lỗi.', 'Exception = ' . $e->getMessage());

                return response()->json(['msg' => 'fail', 'err' => $e->getMessage()]);
            }
        }

        private function indexUrl(Request $request): string
        {
            return route('backend.job.index', ['lastUrl' => $request->get('lastUrl')]);
        }
    }
