@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    @php
        $languageCode = request()->route('languageCode');
        $storeRouteName = \Modules\BackEnd\Helpers\Utilities::getRouteName('backend.service.store');
        $storeRouteParams = ['lastUrl' => Request::get('lastUrl')];
        if ($languageCode) {
            $storeRouteParams['languageCode'] = $languageCode;
        }
    @endphp
    {{ Form::open(['route' => [$storeRouteName, $storeRouteParams]]) }}
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-info-circle"></i> THÊM DỊCH VỤ MỚI</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Tên dịch vụ <span class="text-danger">*</span></label>
                            {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Ví dụ: Set tiệc tối hoàn hôn...', 'maxlength' => 255, 'autocomplete' => 'off','style' => 'font-size: 20px; font-weight: bold;']) }}
                        </div>
                        <div class="form-group">
                            <label>Mô tả dịch vụ</label>
                            {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => 'Mô tả chi tiết các tiện ích và giá trị dịch vụ mang lại...', 'rows' => 20, 'maxlength' => 500, 'autocomplete' => 'off']) }}
                        </div>

                        
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-images"></i> ẢNH VÀ VIDEO DỊCH VỤ</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $serviceGalleryKey = 'service_gallery';
                            $listServiceImage = old($serviceGalleryKey, []);
                            if (is_string($listServiceImage)) {
                                $listServiceImage = $listServiceImage ? (json_decode($listServiceImage) ?: []) : [];
                            }
                            if (!is_array($listServiceImage)) {
                                $listServiceImage = $listServiceImage ? [$listServiceImage] : [];
                            }
                        @endphp
                        <div class="gallery" key="{{ $serviceGalleryKey }}">
                            <div id="list-image-{{ $serviceGalleryKey }}" class="list-image row">
                                @foreach ($listServiceImage as $img)
                                    @php
                                        $thumbnail = is_object($img) && property_exists($img, 'thumbnail') ? $img->thumbnail : null;
                                        $thumbnailFull = Utilities::getFileLink(!$thumbnail ? $img->link : $thumbnail);
                                        $imgLink = is_object($img) ? $img->link : $img;
                                        $imgTitle = is_object($img) && property_exists($img, 'title') ? $img->title : '';
                                    @endphp
                                    <div class="item col-4 col-lg-3" data-obj="{{ json_encode($img, JSON_UNESCAPED_UNICODE) }}">
                                        <div class="box-dragdrop position-relative">
                                            <div class="image-wrapper position-relative">
                                                <a href="{{ Utilities::getFileLink($imgLink) }}" data-fancybox="gallery-{{ $serviceGalleryKey }}">
                                                    <img src="{{ $thumbnailFull }}" alt="{{ $imgTitle }}" class="position-absolute w-100 h-100" />
                                                </a>
                                                <div class="action position-absolute">
                                                    <a href="#" class="btn-delete btn btn-danger btn-sm" title="Xóa">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="name position-absolute w-100 text-center">
                                                <span class="give-ellipsis after-2-lines">{{ $imgTitle }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <div class="item col-4 col-lg-3">
                                    <div class="image-wrapper position-relative">
                                        <a href="#" class="btn-open-modal-select icon d-block position-absolute w-100 h-100">
                                            <i class="far fa-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            {{ Form::hidden($serviceGalleryKey, is_string(old($serviceGalleryKey)) ? old($serviceGalleryKey) : json_encode($listServiceImage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0">CẤU HÌNH DỊCH VỤ</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>LOẠI HÌNH</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    {{ Form::radio('type', 1, true, ['class' => 'form-check-input', 'id' => 'type-1']) }}
                                    <label class="form-check-label" for="type-1">
                                        Dịch vụ bao gồm
                                    </label>
                                </div>  
                                <div class="form-check form-check-inline">
                                    {{ Form::radio('type', 2, false, ['class' => 'form-check-input', 'id' => 'type-2']) }}
                                    <label class="form-check-label" for="type-2">
                                        Dịch vụ không bao gồm
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            
                            <div id="price-input-section" style="display: none;">
                                <label>GIÁ NIÊM YẾT</label>
                                {{ Form::number('price', null, ['class' => 'form-control', 'placeholder' => 'Ví dụ: 100000', 'min' => 0, 'autocomplete' => 'off']) }}
                            </div>
                            
                            <div id="price-free-section" style="display: none;">
                                <div class="alert alert-success mb-0 d-flex align-items-center flex-column">
                                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                                    <span>DỊCH VỤ MIỄN PHÍ</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>PHÂN LOẠI</label>
                            {{ Form::select('group_id', $listGroup, old('group_id', $listGroup->keys()->first()), ['class' => 'form-control', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            <label>ẢNH ĐẠI DIỆN</label>
                            {{ Form::hidden('image_link', old('image_link'), ['class' => 'image-select', 'data-link-full' => Utilities::getFileLink(old('image_link')), 'data-file-manager-url' => route('backend.file.index', ['layout' => 'popup', 'type' => config('backend.fileType.image')])]) }}
                            <div class="d-flex justify-content-center align-items-center mt-4" style="background-color:rgb(233, 233, 233); padding: 10px; border-radius: 5px;">
                                <i class="fas fa-info-circle fa-2x mr-2"></i>
                                <span class="text-uppercase" style="font-size: 12px;">Thông tin này sẽ được hiển thị công khai trên trang chủ và danh sách dịch vụ.</span>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
                <div class="card">
                    <div class="card-body">
                    {{ Form::hidden('status', 1) }}

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Lưu thông tin
                        </button>
                        <a href="{{ Utilities::getGoBackUrl(route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.service.index'), $languageCode ? ['languageCode' => $languageCode] : [])) }}" class="btn btn-light btn-block">
                            <i class="fas fa-arrow-left"></i> Hủy bỏ
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @include('backend::config.shared.modal-gallery-image.modal-select')
        @include('backend::shared.modal-confirm-delete-image')

    {{ Form::close() }}
@endsection

@section('styles')
    <link href="{{ asset('/assets/backend/css/modules/config/index.css') }}" rel="stylesheet" />
    <style>
        .box-image-link .img-image-link {
            max-width: 100% !important;
            width: 100%;
        }
    </style>
@endsection


@section('scripts')
    <script src="{{ asset('/assets/backend/plugins/fancybox/jquery.fancybox.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/touchpunch/jquery.ui.touch-punch.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/mustache/mustache.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/mustache/jquery.mustache.js') }}"></script>
    <script src="{{ asset('/assets/backend/js/modules/shared/gallery.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            function togglePriceSection() {
                var typeValue = $('input[name="type"]:checked').val();
                
                if (typeValue == '1') {
                    // Type 1 = Bao gồm → Miễn phí
                    $('#price-input-section').hide();
                    $('#price-free-section').show();
                    $('input[name="price"]').val(0);
                } else if (typeValue == '2') {
                    // Type 2 = Không bao gồm → Có giá
                    $('#price-input-section').show();
                    $('#price-free-section').hide();
                }
            }
            
            togglePriceSection();
            
            $('input[name="type"]').on('change', function() {
                togglePriceSection();
            });
        });
    </script>
@endsection