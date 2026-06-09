<?php
    namespace Modules\BackEnd\Http\Controllers;

    use Illuminate\Http\Request;
    use Illuminate\Routing\Controller;
    use Illuminate\Support\Facades\Session;
    use Illuminate\Support\MessageBag;
    use Modules\BackEnd\Helpers\LanguageUtils;
    use Modules\BackEnd\Helpers\Logging;
    use Modules\BackEnd\Helpers\Utilities;
    use Modules\BackEnd\Services\AppTestimonialService;
    use Modules\BackEnd\Http\Requests\TestimonialRequest;

    class TestimonialController extends Controller
    {
        private $baseView = 'backend::testimonial.';
        
        public function index(Request $request)
        {
            $language = LanguageUtils::getCurrentLanguage();
            
            $title = 'Quản lý đánh giá';

            \SEO::setTitle($title);

            $param = [
                'is_disabled_paginate' => true
            ];
            if ($request->keyword) {
                $param['keyword'] = $request->keyword;
            }

            $list = AppTestimonialService::getPaging($param, $language->id);

            Logging::logInfo('Xem danh sách đánh giá.');

            return view($this->baseView . __FUNCTION__, compact('title', 'list'));
        }

        public function create(Request $request)
        {
            $language = LanguageUtils::getCurrentLanguage();
            
            $title = 'Thêm đánh giá';

            \SEO::setTitle($title);

            return view($this->baseView . __FUNCTION__, compact('title'));
        }

        public function store(TestimonialRequest $request)
        {
            try {
                $language = LanguageUtils::getCurrentLanguage();
                $languageCode = $request->route('languageCode');
                $data = $request->only([
                    'fullname',
                    'position',
                    'avatar',
                    'content',
                    'ord'
                ]);

                $id = AppTestimonialService::create($data, $language->id);

                $lastUrl = $request->get('lastUrl');
                $route = route(Utilities::getRouteName('backend.testimonial.index'), ['languageCode' => $languageCode, 'lastUrl' => $lastUrl]);

                Logging::logInfo('Thêm đánh giá thành công.', 'id = ' . $id);

                return redirect($route)->with('flash-message', 'Thêm đánh giá thành công!');
            } catch (\Exception $e) {
                Logging::logError('Thêm đánh giá lỗi.', 'Exception = ' . $e->getMessage());

                return redirect()->back()->withErrors('Thêm đánh giá lỗi!');
            }
        }
        
        public function show($id, Request $request)
        {
            $language = LanguageUtils::getCurrentLanguage();
            
            $title = 'Chi tiết đánh giá';

            \SEO::setTitle($title);

            $routeId = $request->route('id');
            if ($routeId !== null) {
                $id = $routeId;
            }

            $obj = AppTestimonialService::find($id);
            if (!$obj) {
                return abort(404);
            }

            Logging::logInfo('Xem chi tiết đánh giá.', 'id = ' . $id);

            return view($this->baseView . __FUNCTION__, compact('title', 'obj'));
        }

        public function edit($id, Request $request)
        {
            $language = LanguageUtils::getCurrentLanguage();
            
            $title = 'Sửa đánh giá';

            \SEO::setTitle($title);

            $routeId = $request->route('id');
            if ($routeId !== null) {
                $id = $routeId;
            }

            $obj = AppTestimonialService::find($id);
            if (!$obj) {
                return abort(404);
            }        

            return view($this->baseView . __FUNCTION__, compact('title', 'obj'));
        }

        public function update(TestimonialRequest $request, $id)
        {
            $language = LanguageUtils::getCurrentLanguage();
            $languageCode = $request->route('languageCode');
            
            $routeId = $request->route('id');
            if ($routeId !== null) {
                $id = $routeId;
            }

            $obj = AppTestimonialService::find($id);
            if (!$obj) {
                return abort(404);
            }

            try {
                $data = $request->only([
                    'fullname',
                    'position',
                    'avatar',
                    'content',
                    'icon',
                    'ord'
                ]);
                $data['id'] = $id;

                AppTestimonialService::update($data, $language->id);

                $lastUrl = $request->get('lastUrl');
                $route = route(Utilities::getRouteName('backend.testimonial.index'), ['languageCode' => $languageCode, 'lastUrl' => $lastUrl]);

                Logging::logInfo('Sửa đánh giá thành công.', 'id = ' . $id);

                return redirect($route)->with('flash-message', 'Sửa đánh giá thành công!');
            } catch (\Exception $e) {
                Logging::logError('Sửa đánh giá lỗi.', 'id = ' . $id . '. Exception = ' . $e->getMessage());

                return redirect()->back()->withErrors('Sửa đánh giá lỗi!');
            }
        }

        public function destroy(Request $request)
        {
            $data = $request->all();
            try
            {
                if (!$data['id']) {
                    throw new \Exception('Parameter invalid.');
                }

                $language = LanguageUtils::getCurrentLanguage();

                AppTestimonialService::delete($data['id'], $language->id);
                
                Session::flash('flash-message', 'Xóa đánh giá thành công!');
                Logging::logInfo('Xóa đánh giá thành công.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE));
                
                return response()->json(['msg' => 'success']);
            }
            catch (\Exception $e)
            {
                Session::flash('errors', new MessageBag(['Xóa đánh giá lỗi!']));
                Logging::logError('Xóa tiện ích lỗi.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE) . '. Exception = ' . $e->getMessage());
                
                return response()->json([
                    'msg' => 'fail',
                    'err' => $e->getMessage()
                ]);
            }
        }

        public function orderUpdate(Request $request)
        {
            $language = LanguageUtils::getCurrentLanguage();
            $data = $request->only('id');
            try
            {
                AppTestimonialService::saveOrder($data['id'], $language->id);

                Logging::logInfo('Sắp xếp đánh giá thành công.');

                return response()->json(['msg' => 'success']);
            } catch (\Exception $e) {
                Logging::logError('Sắp xếp đánh giá lỗi.', 'Exception = ' . $e->getMessage());

                return response()->json([
                    'msg' => 'fail',
                    'err' => $e->getMessage()
                ]);
            }
        }
    }