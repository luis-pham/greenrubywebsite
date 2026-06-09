@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    @php
        $languageCode = request()->route('languageCode');
        $updateRouteName = \Modules\BackEnd\Helpers\Utilities::getRouteName('backend.exp-activity.update');
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
                        <h6 class="m-0"><i class="fas fa-info-circle"></i> THÔNG TIN HOẠT ĐỘNG TRẢI NGHIỆM</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Tên hoạt động <span class="text-danger">*</span></label>
                            {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Ví dụ: Tắm biển, Câu cá...', 'style' => 'font-weight: bold;', 'maxlength' => 255, 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            <label>Mô tả ngắn <span class="text-danger">*</span></label>
                            {{ Form::textarea('summary', null, ['class' => 'form-control', 'placeholder' => 'Tóm tắt về hoạt động...', 'rows' => 3, 'maxlength' => 500, 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            <label>Nội dung chi tiết</label>
                            {{ Form::textarea('content', null, ['class' => 'form-control tinymce', 'placeholder' => 'Giới thiệu đầy đủ về hoạt động...', 'rows' => 6, 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-images"></i> ẢNH VÀ VIDEO HOẠT ĐỘNG</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $activityGalleryKey = 'activity_gallery';
                            if (old($activityGalleryKey) !== null) {
                                $listActivityImage = json_decode(old($activityGalleryKey));
                                $listActivityImage = is_array($listActivityImage) ? $listActivityImage : ($listActivityImage ? [$listActivityImage] : []);
                            } else {
                                $listActivityImage = isset($galleryImages) ? $galleryImages : [];
                            }
                            $activityGalleryValue = old($activityGalleryKey) !== null ? old($activityGalleryKey) : json_encode($listActivityImage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        @endphp
                        <div class="gallery" key="{{ $activityGalleryKey }}">
                            <div id="list-image-{{ $activityGalleryKey }}" class="list-image row">
                                @foreach ($listActivityImage as $img)
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
                                                <a href="{{ Utilities::getFileLink($imgLink) }}" data-fancybox="gallery-{{ $activityGalleryKey }}">
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
                            {{ Form::hidden($activityGalleryKey, $activityGalleryValue) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-tag"></i> PHÂN LOẠI VÀ THỜI GIAN</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group mt-0">
                            <label>LOẠI HOẠT ĐỘNG</label>
                            {{ Form::select('group_id', $listGroup, null, ['class' => 'form-control', 'placeholder' => 'Chọn loại hoạt động...', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group mt-2">
                            <label>NỔI BẬT</label>
                            <div class="col-form-label p-0">
                                {{ Form::hidden('is_featured', 0) }}
                                <label class="c-switch c-switch-label c-switch-success mb-0">
                                    {{ Form::checkbox('is_featured', 1, old('is_featured', $obj->is_featured) == 1, ['class' => 'c-switch-input', 'autocomplete' => 'off']) }}
                                    <span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group mt-2">
                            <label>THỜI LƯỢNG (PHÚT)</label>
                            {{ Form::number('duration', null, ['class' => 'form-control', 'placeholder' => 'Ví dụ: 60', 'min' => 0, 'autocomplete' => 'off']) }}
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-12 mb-3 mb-md-0">
                                <div class="form-group">
                                    <label class="d-block" style="min-height: 2.5rem;">THỜI GIAN BẮT ĐẦU</label>
                                    {{ Form::time('start_time', null, ['class' => 'form-control', 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label class="d-block" style="min-height: 2.5rem;">THỜI GIAN KẾT THÚC</label>
                                    {{ Form::time('end_time', null, ['class' => 'form-control', 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="m-0"><i class="fas fa-tasks"></i> LƯU Ý THAM GIA</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary border-0" id="btn-add-note">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="notes-list">
                        </div>
                        <input type="hidden" name="note" id="note-hidden-field">
                    </div>
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="m-0"><i class="fas fa-users"></i> ĐỐI TƯỢNG PHÙ HỢP</h6>
                            <button type="button" class="btn btn-outline-primary btn-sm btn-add-audience">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="selected-audiences"></div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-image"></i> ẢNH ĐẠI DIỆN</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            {{ Form::hidden('image_link', old('image_link', $obj->image_link), ['class' => 'image-select', 'data-link-full' => Utilities::getFileLink(old('image_link', $obj->image_link)), 'data-file-manager-url' => route('backend.file.index', ['layout' => 'popup', 'type' => config('backend.fileType.image')])]) }}
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-image"></i> ẢNH BÌA</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            {{ Form::hidden('cover_link', old('cover_link', $obj->cover_link), ['class' => 'image-select', 'data-link-full' => Utilities::getFileLink(old('cover_link', $obj->cover_link)), 'data-file-manager-url' => route('backend.file.index', ['layout' => 'popup', 'type' => config('backend.fileType.image')])]) }}
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Lưu thông tin
                        </button>
                        <a href="{{ Utilities::getGoBackUrl(route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.exp-activity.index'), $languageCode ? ['languageCode' => $languageCode] : [])) }}" class="btn btn-light btn-block">
                            <i class="fas fa-arrow-left"></i> Hủy bỏ
                        </a>
                    </div>
                </div>

                
            </div>
                
        </div>
            

    {{ Form::close() }}

    @include('backend::shared.modal-audience', ['listAudience' => $listSuitableAudience, 'modalTitle' => 'CHỌN ĐỐI TƯỢNG PHÙ HỢP'])
    @include('backend::config.shared.modal-gallery-image.modal-select')
    @include('backend::config.shared.modal-gallery-image.modal-edit')
    @include('backend::shared.modal-confirm-delete-image')

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
        (function () {
            // Thêm note mới
            $('#btn-add-note').on('click', function() {
                var html = '<div class="note-item mb-2">' +
                    '<div class="position-relative">' +
                        '<input type="text" class="form-control form-control-sm note-input" placeholder="Nhập lưu ý..." style="padding-right: 35px;">' +
                        '<button type="button" class="btn btn-sm btn-outline-danger border-0 btn-remove-note" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); padding: 2px 6px;">' +
                            '<i class="fas fa-times"></i>' +
                        '</button>' +
                    '</div>' +
                '</div>';
                $('#notes-list').append(html);
            });

            // Xóa note
            $(document).on('click', '.btn-remove-note', function() {
                $(this).closest('.note-item').remove();
            });

            // Load notes hiện có vào UI
            @if($obj->note)
                try {
                    var existingNotes = JSON.parse(@json($obj->note));
                    if (Array.isArray(existingNotes)) {
                        existingNotes.forEach(function(note) {
                            var html = '<div class="note-item mb-2">' +
                                '<div class="position-relative">' +
                                    '<input type="text" class="form-control form-control-sm note-input" value="' + note + '" placeholder="Nhập lưu ý..." style="padding-right: 35px;">' +
                                    '<button type="button" class="btn btn-sm btn-outline-danger border-0 btn-remove-note" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); padding: 2px 6px;">' +
                                        '<i class="fas fa-times"></i>' +
                                    '</button>' +
                                '</div>' +
                            '</div>';
                            $('#notes-list').append(html);
                        });
                    }
                } catch (e) {
                    console.error('Error parsing notes:', e);
                }
            @endif

            // Audience Modal
            function renderAudience(id, name, icon) {
                if ($('#selected-audiences [data-id="' + id + '"]').length) return;
                var iconHtml = icon ? '<i class="' + icon + ' text-primary mr-2"></i>' : '<i class="fas fa-tag text-primary mr-2"></i>';
                var html = '<div class="d-inline-flex align-items-center bg-light border rounded px-2 py-1 mr-2 mb-2" data-id="' + id + '">' +
                    '<input type="hidden" name="audience_group_ids[]" value="' + id + '">' +
                    iconHtml + '<span class="small text-uppercase mr-2">' + $('<div>').text(name).html() + '</span>' +
                    '<button type="button" class="btn btn-link text-danger p-0" onclick="$(this).closest(\'div\').remove()"><i class="fas fa-times"></i></button></div>';
                $('#selected-audiences').append(html);
            }

            // Load dữ liệu cũ
            @if(isset($suitableAudiences) && count($suitableAudiences) > 0)
                var existingAudiences = @json($suitableAudiences);
                var listAudienceMap = @json($listSuitableAudience->mapWithKeys(function($item) {
                    $icon = null;
                    if ($item->description) {
                        $descData = json_decode($item->description, true);
                        $icon = $descData['icon'] ?? null;
                    }
                    return [$item->id => ['name' => $item->name, 'icon' => $icon]];
                }));
                
                Object.keys(existingAudiences).forEach(function(id) {
                    var name = existingAudiences[id];
                    var icon = listAudienceMap[id] ? listAudienceMap[id].icon : null;
                    renderAudience(id, name, icon);
                });
            @endif

            $('.btn-add-audience').on('click', function () {
                $('#audience-modal .audience-item').removeClass('selected in-list').each(function () {
                    if ($('#selected-audiences [data-id="' + $(this).data('id') + '"]').length) $(this).addClass('selected in-list');
                });
                $('#audience-modal .audience-selection-count').text($('#audience-modal .selected').length);
                $('#audience-modal').modal('show');
            });

            $(document).on('click', '#audience-modal .audience-item', function () {
                var $this = $(this);
                if ($this.hasClass('in-list')) {
                    $('#selected-audiences [data-id="' + $this.data('id') + '"]').remove();
                    $this.removeClass('selected in-list');
                } else {
                    $this.toggleClass('selected');
                }
                $('#audience-modal .audience-selection-count').text($('#audience-modal .selected').length);
            });

            $(document).on('click', '.btn-confirm-audience-selection', function () {
                $('#audience-modal .selected').each(function () {
                    renderAudience($(this).data('id'), $(this).data('name'), $(this).data('icon'));
                });
            });

            // Submit form - chỉ notes
            $('form').on('submit', function() {
                var notes = [];
                $('.note-input').each(function() {
                    var value = $(this).val().trim();
                    if (value) notes.push(value);
                });
                $('#note-hidden-field').val(JSON.stringify(notes));
            });
        })();
    </script>
@endsection