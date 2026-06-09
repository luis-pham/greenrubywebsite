@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    @php
        $languageCode = request()->route('languageCode');
        $updateRouteName = \Modules\BackEnd\Helpers\Utilities::getRouteName('backend.service.update');
        $updateRouteParams = ['id' => $obj->id, 'lastUrl' => Request::get('lastUrl')];
        if ($languageCode) {
            $updateRouteParams['languageCode'] = $languageCode;
        }
    @endphp
    {{ Form::model($obj, ['route' => [$updateRouteName, $updateRouteParams]]) }}
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-info-circle"></i> SỬA DỊCH VỤ</h6>
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
                            if (old($serviceGalleryKey) !== null) {
                                $listServiceImage = json_decode(old($serviceGalleryKey));
                                $listServiceImage = is_array($listServiceImage) ? $listServiceImage : ($listServiceImage ? [$listServiceImage] : []);
                            } else {
                                $listServiceImage = isset($galleryImages) ? $galleryImages : [];
                            }
                            $serviceGalleryValue = old($serviceGalleryKey) !== null ? old($serviceGalleryKey) : json_encode($listServiceImage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        @endphp
                        <div class="gallery" key="{{ $serviceGalleryKey }}">
                            <div id="list-image-{{ $serviceGalleryKey }}" class="list-image row">
                                @foreach ($listServiceImage as $img)
                                    @php
                                        $img = is_array($img) ? (object) $img : $img;
                                        $thumbnail = property_exists($img, 'thumbnail') ? $img->thumbnail : null;
                                        $imgLink = property_exists($img, 'link') ? $img->link : '';
                                        $thumbnailFull = Utilities::getFileLink(!$thumbnail ? $imgLink : $thumbnail);
                                        $imgTitle = property_exists($img, 'title') ? $img->title : (property_exists($img, 'name') ? $img->name : '');
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
                            {{ Form::hidden($serviceGalleryKey, $serviceGalleryValue) }}
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
                            @php
                                $serviceTypeValue = old('type');
                                if ($serviceTypeValue === null) {
 
                                    $serviceTypeValue = ($obj->type == 1) ? 1 : 2;
                                }
                                $serviceTypeValue = (int) $serviceTypeValue;
                            @endphp
                            <div>
                                <div class="form-check form-check-inline">
                                    <input type="radio"
                                           name="type"
                                           id="type-1"
                                           value="1"
                                           class="form-check-input"
                                           {{ $serviceTypeValue === 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="type-1">
                                        Dịch vụ bao gồm
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio"
                                           name="type"
                                           id="type-2"
                                           value="2"
                                           class="form-check-input"
                                           {{ $serviceTypeValue === 2 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="type-2">
                                        Dịch vụ không bao gồm
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>GIÁ NIÊM YẾT</label>
                            
                            <div id="price-input-section" style="display: none;">
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
                            {{ Form::select('group_id', $listGroup, null, ['class' => 'form-control', 'placeholder' => 'Chọn phân loại...', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            <label>ẢNH ĐẠI DIỆN</label>
                            {{ Form::hidden('image_link', old('image_link', $obj->image_link), ['class' => 'image-select', 'data-link-full' => Utilities::getFileLink($obj->image_link), 'data-file-manager-url' => route('backend.file.index', ['layout' => 'popup', 'type' => config('backend.fileType.image')])]) }}

                            <div class="d-flex justify-content-center align-items-center mt-4" style="background-color:rgb(233, 233, 233); padding: 10px; border-radius: 5px;">
                                <i class="fas fa-info-circle fa-2x mr-2"></i>
                                <span class="text-uppercase">Thông tin này sẽ được hiển thị công khai trên trang chủ và danh sách dịch vụ.</span>
                            </div>
                        </div>
                    </div>
                </div>
                
            

                <div class="card">
                    <div class="card-body">
                        {{ Form::hidden('status', $obj->status ?? 1) }}
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Lưu thông tin
                        </button>
                        <a href="{{ Utilities::getGoBackUrl(route('backend.service.index')) }}" class="btn btn-light btn-block">
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
            // Hàm toggle giá/miễn phí
            function togglePriceSection() {
                var typeValue = $('input[name="type"]:checked').val();
                
                if (typeValue == '1') {
                    // Type 1 = Bao gồm → Miễn phí
                    $('#price-input-section').hide();
                    $('#price-free-section').show();
                    // Set giá = 0 cho type 1
                    $('input[name="price"]').val(0);
                } else if (typeValue == '2') {
                    // Type 2 = Không bao gồm → Có giá
                    $('#price-input-section').show();
                    $('#price-free-section').hide();
                }
            }
            
            // Chạy khi load trang
            togglePriceSection();
            
            // Chạy khi thay đổi radio type
            $('input[name="type"]').on('change', function() {
                togglePriceSection();
            });
        });
    </script>
@endsection