@extends('backend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');
    $queryStr = request()->query();
    $listDuration = CruiseUtils::getListDuration();
    $routeParams = $queryStr;
    $routeParams['languageCode'] = $languageCode;
    if($itinerary){
        $routeParams['id'] = $itinerary->id;
    }

    $formRouteName = $isEdit ? 'backend.itinerary.update' : 'backend.itinerary.store';
    $formMethod = $isEdit ? 'PUT' : 'POST';
    $listExclusiveServiceOption = $listExclusiveService->pluck('name','id');
    $listInclusiveServiceOption = $listInclusiveService->pluck('name','id');
    $listActivityOption = $listActivity->pluck('name','id');

    $duration = old('duration',$itinerary?->duration ?? array_key_first(CruiseUtils::getListDuration()));
@endphp

@section('content')
    @include('backend::shared.message')
    <div class="card">
        <div class="card-header">
            <h1 class="h4 m-0">{{ $title }}</h1>
        </div>
        <div class="card-body">
            {{
                Form::open([
                    'id' => 'frm',
                    'route' => [Utilities::getRouteName($formRouteName),['languageCode' => $languageCode,'id' => $itinerary?->id ?? null]],
                    'method' => $formMethod,
                    'class' => 'row',
                    'files' => true
                ])
            }}
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h1 class="h5 m-0">Thông tin chung</h1>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-12">
                                    <label class="col-form-label">Tên hành trình <span class="text-danger">*</span></label>
                                    {{
                                        Form::text(
                                            'name',
                                            $itinerary->name ?? null,
                                            [
                                                'class' => 'form-control',
                                                'autocomplete' => 'off',
                                                'placeholder' => 'Nhập tên hành trình...',
                                                'disabled' => $readOnly
                                            ]
                                        )
                                    }}
                                </div>
                                <div class="form-group col-12">
                                    <label class="col-form-label">Mô tả ngắn <span class="text-danger">*</span></label>
                                    {{
                                        Form::textarea(
                                            'description',
                                            $itinerary->description ?? null,
                                            [
                                                'class' => 'form-control',
                                                'autocomplete' => 'off',
                                                'placeholder' => 'Nhập mô tả...',
                                                'disabled' => $readOnly,
                                                'rows' => 3,
                                            ]
                                        )
                                    }}
                                </div>
                                <div class="form-group col-12 col-lg-6">
                                    <label class="col-form-label">Thời lượng</label>
                                    {{
                                        Form::select(
                                            'duration',
                                            $listDuration,
                                            $duration,
                                            [
                                                'id' => 'itineraryDurationSelect',
                                                'class' => 'form-control',
                                                'disabled' => $readOnly
                                            ]
                                        )
                                    }}
                                </div>
                                <div class="form-group col-12 col-lg-6">
                                    <label class="col-form-label">Vịnh</label>
                                    {{
                                        Form::select(
                                            'bay',
                                            config('backend.itineraryBay'),
                                            $itinerary->bay ?? 1,
                                            [
                                                'id' => 'itineraryBaySelect',
                                                'class' => 'form-control',
                                                'disabled' => $readOnly
                                            ]
                                        )
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h1 class="h5 m-0">Lịch trình</h1>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="col-form-label">Điểm đến chính/ Địa danh tham quan <span class="text-danger">*</span></label>
                                    {{
                                        Form::text(
                                            'destination',
                                            $itinerary ? CruiseUtils::formatDisplayItineraryDestination($itinerary->destination) : null,
                                            [
                                                'class' => 'form-control',
                                                'placeholder' => 'Ví dụ: Hạ Long, Lan Hạ, Hang Sáng Tối',
                                                'autocomplete' => 'off',
                                                'disabled' => $readOnly
                                            ]
                                        )
                                    }}
                                </div>
                                <div class="col-12">
                                    <div id="itinerary-day-container">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h1 class="h5 m-0">Ảnh bìa</h1>
                        </div>
                        <div class="card-body">
                            @if(!$readOnly)
                                {{
                                   Form::hidden(
                                       'cover_link',
                                        $itinerary->cover_link ?? null,
                                        [
                                           'class' => 'image-select',
                                           'data-link-full' => Utilities::getFileLink(old('cover_link',$itinerary->cover_link ?? null)),
                                           'data-file-manager-url' => route('backend.file.index', ['layout' => 'popup', 'type' => config('backend.fileType.image')])
                                        ]
                                   )
                               }}
                            @else
                                <img class="img-fluid" alt="Itinerary Cover Image" src="{{ \Modules\BackEnd\Helpers\Utilities::getFileLink($itinerary->cover_link) }}" />
                            @endif
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h1 class="h5 m-0">Ảnh đại diện</h1>
                        </div>
                        <div class="card-body">
                            @if(!$readOnly)
                                {{
                                   Form::hidden(
                                       'image_link',
                                        $itinerary->image_link ?? null,
                                        [
                                           'class' => 'image-select',
                                           'data-link-full' => Utilities::getFileLink(old('image_link',$itinerary->image_link ?? null)),
                                           'data-file-manager-url' => route('backend.file.index', ['layout' => 'popup', 'type' => config('backend.fileType.image')])
                                        ]
                                   )
                               }}
                            @else
                                <img class="img-fluid" alt="Itinerary Profile Image" src="{{ \Modules\BackEnd\Helpers\Utilities::getFileLink($itinerary->image_link) }}" />
                            @endif
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h1 class="h5 m-0">Thư viện</h1>
                        </div>
                        <div class="card-body">
                            @php
                                $itineraryGalleryKey = 'image_gallery';
                                if (old($itineraryGalleryKey) !== null) {
                                    $listItineraryImage = json_decode(old($itineraryGalleryKey));
                                    $listItineraryImage = is_array($listItineraryImage) ? $listItineraryImage : ($listItineraryImage ? [$listItineraryImage] : []);
                                } else {
                                    $listItineraryImage = $itinerary->galleryImages ?? [];
                                }
                                $imageGalleryValue = old($itineraryGalleryKey) !== null ? old($itineraryGalleryKey) : json_encode($listItineraryImage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                            @endphp
                            <div class="gallery" key="{{ $itineraryGalleryKey }}">
                                <div id="list-image-{{ $itineraryGalleryKey }}" class="list-image row">
                                    @foreach ($listItineraryImage as $img)
                                        @php
                                            $img = is_array($img) ? (object) $img : $img;
                                            $thumbnail = $img->thumbnail;
                                            $imgLink = $img->link;
                                            $thumbnailFull = Utilities::getFileLink(!$thumbnail ? $imgLink : $thumbnail);
                                            $imgTitle = $img->title ?? $img->name ?? "";
                                        @endphp
                                        <div class="item col-4 col-lg-3" data-obj="{{ json_encode($img, JSON_UNESCAPED_UNICODE) }}">
                                            <div class="box-dragdrop position-relative">
                                                <div class="image-wrapper position-relative">
                                                    <a href="{{ Utilities::getFileLink($imgLink) }}" data-fancybox="gallery-{{ $itineraryGalleryKey }}">
                                                        <img src="{{ $thumbnailFull }}" alt="{{ $imgTitle }}" class="position-absolute w-100 h-100" />
                                                    </a>
                                                    @if(!$readOnly)
                                                        <div class="action position-absolute">
                                                            <a href="javascript:void(0)" class="btn-delete btn btn-danger btn-sm" title="Xóa">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="name position-absolute w-100 text-center">
                                                    <span class="give-ellipsis after-2-lines">{{ $imgTitle }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if(!$readOnly)
                                        <div class="item col-4 col-lg-3">
                                            <div class="image-wrapper position-relative">
                                                <a href="javascript:void(0)" class="btn-open-modal-select icon d-block position-absolute w-100 h-100">
                                                    <i class="far fa-plus"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                @if(!$readOnly)
                                    {{ Form::hidden($itineraryGalleryKey, $imageGalleryValue) }}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        @php
                            $important_note_container_id = 'important_note';
                        @endphp
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h1 class="h5 m-0">Lưu ý quan trọng</h1>
                            @if(!$readOnly)
                                <a
                                    href="javascript:void(0)"
                                    class="btn-add-input-item"
                                    data-target="important_note"
                                >
                                    <i class="fas fa-plus"></i>
                                </a>
                            @endif
                        </div>
                        <div class="card-body">
                            <div
                                class="dynamic-input-container d-flex flex-column"
                                style="gap:1rem"
                                id="{{$important_note_container_id}}"
                                data-item-input-class=""
                                data-placeholder="Nhập nội dung lưu ý..."
                            ></div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="row">
                        <div class="col-4">
                            <div class="card">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <h1 class="h5 m-0">Bao gồm</h1>
                                </div>
                                <div class="card-body">
                                    {{
                                        Form::select(
                                            'listServiceId[]',
                                            $listInclusiveServiceOption,
                                            $itinerary?->itineraryServices->pluck('id') ?? [],
                                            [
                                                'class' => 'form-control select2-multiple',
                                                'multiple' => true,
                                                'disabled' => $readOnly
                                            ],
                                            $listInclusiveService->mapWithKeys((fn($service,$idx) => [$service->id => ['data-image' => \Modules\BackEnd\Helpers\Utilities::getFileLink($service->image_link)]]))->toArray()
                                        )
                                    }}
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <h1 class="h5 m-0">Không bao gồm</h1>
                                </div>
                                <div class="card-body">
                                    {{
                                        Form::select(
                                            'listServiceId[]',
                                            $listExclusiveServiceOption,
                                            $itinerary?->itineraryServices->pluck('id') ?? [],
                                            [
                                                'class' => 'form-control select2-multiple',
                                                'multiple' => true,
                                                'disabled' => $readOnly
                                            ],
                                            $listExclusiveService->mapWithKeys((fn($service,$idx) => [$service->id => ['data-image' => \Modules\BackEnd\Helpers\Utilities::getFileLink($service->image_link)]]))->toArray()
                                        )
                                    }}
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <h1 class="h5 m-0">Trải nghiệm</h1>
                                </div>
                                <div class="card-body">
                                    {{
                                        Form::select(
                                            'listActivityId[]',
                                            $listActivityOption,
                                            $itinerary?->itineraryActivities->pluck('id') ?? [],
                                            [
                                                'class' => 'form-control select2-multiple',
                                                'multiple' => true,
                                                'disabled' => $readOnly
                                            ],
                                            $listActivity->mapWithKeys((fn($activity,$idx) => [$activity->id => ['data-image' => \Modules\BackEnd\Helpers\Utilities::getFileLink($activity->image_link)]]))->toArray()
                                        )
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            {{ Form::close() }}
        </div>
    </div>
@endsection

@section('footer')
    <footer class="c-footer c-footer-sticky pl-0 pr-0">
        <div class="container-fluid">
            @if(!$readOnly)
                <button type="button" class="btn btn-primary btn-sm" onclick="$('#frm').submit()">
                    <i class="fas fa-save"></i> Lưu lại
                </button>
            @else
                @can('itinerary-update')
                    <a href="{{ route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.itinerary.edit'),$routeParams) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-pencil-alt"></i> Sửa
                    </a>
                @endcan
                @can('itinerary-delete')
                    <a
                        href="javascript:void(0)"
                        class="btn btn-danger btn-sm btn-delete-one"
                        data-id="{{$itinerary?->id ?? null}}"
                        data-ajax-url="{{ route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.itinerary.destroy'),$routeParams)}}"
                        data-ajax-url-go-back="{{ route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.itinerary.index'),array_merge($queryStr,['languageCode' => $languageCode])) }}"
                    >
                        <i class="fas fa-trash-alt"></i> Xóa
                    </a>
                @endcan
            @endif

            <a href="{{ route(Utilities::getRouteName('backend.itinerary.index'),array_merge($queryStr,['languageCode' => $languageCode])) }}" class="btn btn-light btn-sm">
                <i class="fas fa-undo"></i> Quay lại
            </a>
        </div>
    </footer>
@endsection

@include('backend::config.shared.modal-gallery-image.modal-select')

@section('styles')
<link href="{{ asset('assets/backend/css/modules/itinerary/detail.css') }}" rel="stylesheet"/>
<link href="{{ asset('/assets/backend/css/modules/config/index.css') }}" rel="stylesheet">
@endsection

@section('scripts')
@include('backend::shared.text-editor-script')
<script src="{{ asset('/assets/backend/plugins/fancybox/jquery.fancybox.min.js') }}"></script>
<script src="{{ asset('/assets/backend/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('/assets/backend/plugins/touchpunch/jquery.ui.touch-punch.min.js') }}"></script>
<script src="{{ asset('/assets/backend/plugins/mustache/mustache.js') }}"></script>
<script src="{{ asset('/assets/backend/plugins/mustache/jquery.mustache.js') }}"></script>
@if(!$readOnly)
<script src="{{ asset('/assets/backend/js/modules/shared/gallery.js') }}"></script>
@endif
<script type="text/javascript">
@php
    $listImportantNote = old('important_note',$itinerary->important_note ?? []);
    $listImportantNoteImageLinkValue = collect($listImportantNote)->pluck('image_link');
    $listImportantNoteImageLinkFull = $listImportantNoteImageLinkValue->map(fn($link) => Utilities::getFileLink($link));
@endphp
window.listImportantNote = @json($listImportantNote);
window.duration = @json($duration);
window.listItineraryDay = @json(old('itinerary_days',$itinerary->itineraryDays ?? []));
window.readOnly = @json($readOnly);
window.fileManagerUrl = @json(route('backend.file.index', ['layout' => 'popup', 'type' => config('backend.fileType.image')]));
window.listImportantNoteImageLinkFull = @json($listImportantNoteImageLinkFull);
window.listImportantNoteImageLinkValue = @json($listImportantNoteImageLinkValue)
</script>
<script src="{{ asset('assets/backend/js/shared/select2-multiple.js?v=1.0.1') }}" type="text/javascript"></script>
<script src="{{ asset('assets/backend/js/modules/itinerary/detail.js') }}" type="text/javascript"></script>
@endsection
