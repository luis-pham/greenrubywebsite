@extends('backend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');
    $queryStr = request()->query();
    $routeParams = $queryStr;
    $routeParams['languageCode'] = $languageCode;
    if($cruise){
        $routeParams['id'] = $cruise->id;
    }
    $listAmenityOption = $listAmenity->pluck('name','id')->toArray();
    $listServiceOption = $listService->pluck('name','id')->toArray();
    $formRouteName = $isEdit ? 'backend.cruise.update' : 'backend.cruise.store';
    $formMethod = $isEdit ? 'PUT' : 'POST';
@endphp

@section('content')
    @include('backend::shared.message')
    <div class="card">
        <div class="card-header">
            <h1 class="h4 m-0">{{ $title }}</h1>
        </div>
        <div class="card-body">
            @if(!$readOnly)
                {!!
                    Form::open([
                        'route' => [\Modules\BackEnd\Helpers\Utilities::getRouteName($formRouteName),['languageCode' => $languageCode,'id' => $cruise?->id]],
                        'method' => $formMethod,
                        'id' => 'frm',
                        'files' => true,
                    ])
                !!}
            @endif
                <div class="row">
                    <div class="col-6 col-lg-7 col-xl-8">
                        <div class="card">
                            <div class="card-header">
                                <h1 class="h5 m-0">Thông tin cơ bản</h1>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-12">
                                        <label class="col-form-label">Tên du thuyền <span class="text-danger">*</span></label>
                                        {!!
                                            Form::text(
                                                'name',
                                                $cruise->name ?? null,
                                                [
                                                    'class' => "form-control" . ($errors->has('name') ? ' is-invalid' : ''),
                                                    'placeholder' => 'Nhập tên du thuyền...',
                                                    'autocomplete' => 'off',
                                                    'disabled' => $readOnly
                                                ]
                                            )
                                        !!}
                                        {{--                                    @error('name')--}}
                                        {{--                                        <div class="invalid-feedback">{{ $message }}</div>--}}
                                        {{--                                    @enderror--}}
                                    </div>
                                    <div class="form-group col-12 col-xl-8">
                                        <label class="col-form-label">Mô tả ngắn <span class="text-danger">*</span></label>
                                        {!!
                                            Form::textarea(
                                                'summary',
                                                $cruise->summary ?? null,
                                                [
                                                    'class' => 'form-control' . ($errors->has('summary') ? ' is-invalid' : ''),
                                                    'placeholder' => 'Nhập mô tả ngắn...',
                                                    'autocomplete' => 'off',
                                                    'rows' => 2,
                                                    'disabled' => $readOnly
                                                ]
                                            )
                                        !!}
                                        {{--                                    @error('summary')--}}
                                        {{--                                        <div class="invalid-feedback">{{ $message }}</div>--}}
                                        {{--                                    @enderror--}}
                                    </div>
                                    <div class="form-group col-12 col-xl-4">
                                        <label class="col-form-label">Hạng (số sao)</label>
                                        {!!
                                           Form::select(
                                                'star_rating',
                                                array_combine(
                                                    range(1,5),
                                                    array_map(fn($n) => $n . ' sao',range(1,5))
                                                ),
                                                $cruise->star_rating ?? null,
                                                [
                                                    'class' => 'form-control',
                                                    'placeholder' => 'Chọn hạng',
                                                    'autocomplete' => 'off',
                                                    'disabled' => $readOnly
                                                ]
                                           )
                                        !!}
                                    </div>
                                    <div class="form-group col-12">
                                        <label class="col-form-label">Mô tả thiết kế</label>
                                        {!!
                                            Form::textarea(
                                                'description_design',
                                                $cruise->description_design ?? null,
                                                [
                                                    'class' => 'form-control' . ($errors->has('description_design') ? ' is-invalid' : ''),
                                                    'placeholder' => 'Nhập mô tả thiết kế...',
                                                    'autocomplete' => 'off',
                                                    'rows' => 2,
                                                    'disabled' => $readOnly
                                                ]
                                            )
                                        !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                           <div class="card-header">
                               <h1 class="h5 m-0">Công nghệ xanh</h1>
                           </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        @if($readOnly)
                                            <img src="{{ \Modules\BackEnd\Helpers\Utilities::getFileLink($cruise->green_technology->image_link ?? null) }}" class="img-fluid" alt="Green Technology Icon"/>
                                        @else
                                            {{
                                                Form::hidden(
                                                    'green_technology[image_link]',
                                                     $cruise->green_technology->image_link ?? null,
                                                     [
                                                        'class' => 'image-select',
                                                        'data-link-full' => Utilities::getFileLink(old('green_technology[image_link]',$cruise->green_technology->image_link ?? null)),
                                                        'data-file-manager-url' => route('backend.file.index', ['layout' => 'popup', 'type' => config('backend.fileType.image')])
                                                     ]
                                                )
                                            }}
                                        @endif
                                    </div>
                                    <div class="col-12 col-md-8">
                                        <div class="form-group">
                                            {{
                                                Form::text(
                                                    'green_technology[name]',
                                                    $cruise->green_technology->name ?? null,
                                                    [
                                                        'class' => "form-control",
                                                        'placeholder' => 'Nhập tên...',
                                                        'autocomplete' => 'off',
                                                        'disabled' => $readOnly
                                                    ]
                                                )
                                            }}
                                        </div>
                                        <div class="form-group">
                                            {{
                                                Form::textarea(
                                                    'green_technology[description]',
                                                    $cruise->green_technology->description ?? null,
                                                    [
                                                        'class' => 'form-control',
                                                        'placeholder' => 'Nhập mô tả...',
                                                        'autocomplete' => 'off',
                                                        'rows' => 2,
                                                        'disabled' => $readOnly
                                                    ]
                                                )
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h1 class="h5 m-0">Danh sách tiện ích</h1>
                            </div>
                            <div class="card-body">
                                {{
                                    Form::select(
                                        'listAmenityId[]',
                                        $listAmenityOption,
                                        $cruise?->cruiseAmenities->pluck('id') ?? [],
                                        [
                                            'class' => 'form-control select2-multiple',
                                            'multiple' => true,
                                            'disabled' => $readOnly,
                                        ],
                                        $listAmenity->mapWithKeys(fn($amenity) => [$amenity->id => ['data-image' => Utilities::getFileLink($amenity->icon) ?? '']])->toArray()
                                    )
                                }}
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h1 class="h5 m-0">Danh sách dịch vụ</h1>
                            </div>
                            <div class="card-body">
                                {{
                                    Form::select(
                                        'listServiceId[]',
                                        $listServiceOption,
                                        $cruise?->cruiseServices->pluck('id') ?? [],
                                        [
                                            'class' => 'form-control select2-multiple',
                                            'multiple' => true,
                                            'disabled' => $readOnly,
                                        ],
                                        $listService->mapWithKeys(fn($service) => [$service->id => ['data-image' => Utilities::getFileLink($service->image_link) ?? '']])->toArray()
                                    )
                                }}
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-5 col-xl-4">
                        <div class="card">
                            <div class="card-header text-center">
                                <h1 class="h5 m-0">Ảnh bìa</h1>
                            </div>
                            <div class="card-body">
                                @if($readOnly)
                                    <img src="{{ \Modules\BackEnd\Helpers\Utilities::getFileLink($cruise->cover_link) }}" class="img-fluid" alt="Cruise Cover Image"/>
                                @else
                                    {{
                                        Form::hidden(
                                            'cover_link',
                                             $cruise->cover_link ?? null,
                                             [
                                                'class' => 'image-select',
                                                'data-link-full' => Utilities::getFileLink(old('cover_link',$cruise->cover_link ?? null)),
                                                'data-file-manager-url' => route('backend.file.index', ['layout' => 'popup', 'type' => config('backend.fileType.image')])
                                             ]
                                        )
                                    }}
                                @endif
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header text-center">
                                <h1 class="h5 m-0">Ảnh đại diện</h1>
                            </div>
                            <div class="card-body">
                                @if($readOnly)
                                   <img src="{{ \Modules\BackEnd\Helpers\Utilities::getFileLink($cruise->image_link) }}" class="img-fluid" alt="Cruise Profile Image"/>
                                @else
                                    {{
                                        Form::hidden(
                                            'image_link',
                                             $cruise->image_link ?? null,
                                             [
                                                'class' => 'image-select',
                                                'data-link-full' => Utilities::getFileLink(old('image_link',$cruise->image_link ?? null)),
                                                'data-file-manager-url' => route('backend.file.index', ['layout' => 'popup', 'type' => config('backend.fileType.image')])
                                             ]
                                        )
                                    }}
                                  @endif
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h1 class="h5 m-0">Thư viện ảnh</h1>
                            </div>
                            <div class="card-body">
                                @php
                                    $cruiseGalleryKey = 'image_gallery';
                                    if (old($cruiseGalleryKey) !== null) {
                                        $listCruiseImage = json_decode(old($cruiseGalleryKey));
                                        $listCruiseImage = is_array($listCruiseImage) ? $listCruiseImage : ($listCruiseImage ? [$listCruiseImage] : []);
                                    } else {
                                        $listCruiseImage = $cruise->galleryImages ?? [];
                                    }
                                    $imageGalleryValue = old($cruiseGalleryKey) !== null ? old($cruiseGalleryKey) : json_encode($listCruiseImage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                @endphp
                                <div class="gallery" key="{{ $cruiseGalleryKey }}">
                                    <div id="list-image-{{ $cruiseGalleryKey }}" class="list-image row">
                                        @foreach ($listCruiseImage as $img)
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
                                                        <a href="{{ Utilities::getFileLink($imgLink) }}" data-fancybox="gallery-{{ $cruiseGalleryKey }}">
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
                                        {{ Form::hidden($cruiseGalleryKey, $imageGalleryValue) }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h1 class="h5 m-0">Thông số</h1>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6 form-group">
                                        <label class="col-form-label">Sức chứa <span class="text-danger">*</span></label>
                                        {!!
                                           Form::number(
                                               'capacity',
                                               $cruise->capacity ?? null,
                                               [
                                                   'class' => 'form-control' . ($errors->has('capacity') ? ' is-invalid' : ''),
                                                   'placeholder' => 'Nhập sức chứa...',
                                                   'autocomplete' => 'off',
                                                   'disabled' => $readOnly
                                               ]
                                           )
                                        !!}
                                        {{--                                    @error('capacity')--}}
                                        {{--                                        <div class="invalid-feedback">{{ $message }}</div>--}}
                                        {{--                                    @enderror--}}
                                    </div>
                                    <div class="col-6 form-group">
                                        <label class="col-form-label">Số tầng <span class="text-danger">*</span></label>
                                        {!!
                                           Form::number(
                                               'total_floor',
                                               $cruise->total_floor ?? null,
                                               [
                                                   'class' => 'form-control' . ($errors->has('total_floor') ? ' is-invalid' : ''),
                                                   'placeholder' => 'Nhập số tầng...',
                                                   'autocomplete' => 'off',
                                                   'disabled' => $readOnly
                                               ]
                                           )
                                        !!}
                                        {{--                                    @error('total_floor')--}}
                                        {{--                                        <div class="invalid-feedback">{{ $message }}</div>--}}
                                        {{--                                    @enderror--}}
                                    </div>
                                    <div class="col-6 form-group">
                                        <label class="col-form-label">Chiều dài <span class="text-danger">*</span></label>
                                        {!!
                                           Form::number(
                                               'dimension_length',
                                               $cruise->dimension_length ?? null,
                                               [
                                                   'class' => 'form-control' . ($errors->has('dimension_length') ? ' is-invalid' : ''),
                                                   'placeholder' => 'Nhập chiều dài...',
                                                   'autocomplete' => 'off',
                                                   'disabled' => $readOnly
                                               ]
                                           )
                                        !!}
                                        {{--                                    @error('dimension_length')--}}
                                        {{--                                        <div class="invalid-feedback">{{ $message }}</div>--}}
                                        {{--                                    @enderror--}}
                                    </div>
                                    <div class="col-6 form-group">
                                        <label class="col-form-label">Năm đóng <span class="text-danger">*</span></label>
                                        {!!
                                           Form::number(
                                               'year_built',
                                               $cruise->year_built ?? null,
                                               [
                                                   'class' => 'form-control' . ($errors->has('year_built') ? ' is-invalid' : ''),
                                                   'placeholder' => 'Nhập năm đóng...',
                                                   'autocomplete' => 'off',
                                                   'disabled' => $readOnly
                                               ]
                                           )
                                        !!}
                                        {{--                                    @error('year_built')--}}
                                        {{--                                        <div class="invalid-feedback">{{ $message }}</div>--}}
                                        {{--                                    @enderror--}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @if(!$readOnly) {!! Form::close() !!} @endif
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
                @can('cruise-update')
                    <a href="{{ route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.cruise.edit'),$routeParams) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-pencil-alt"></i> Sửa
                    </a>
                @endcan
                @can('cruise-delete')
                    <a
                        href="javascript:void(0)"
                        class="btn btn-danger btn-sm btn-delete-one"
                        data-id="{{$cruise?->id ?? null}}"
                        data-ajax-url="{{ route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.cruise.destroy'),$routeParams)}}"
                        data-ajax-url-go-back="{{ route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.cruise.index'),array_merge($queryStr,['languageCode' => $languageCode])) }}"
                    >
                        <i class="fas fa-trash-alt"></i> Xóa
                    </a>
                @endcan
            @endif

            <a href="{{ route(Utilities::getRouteName('backend.cruise.index'),['languageCode' => $languageCode]) }}" class="btn btn-light btn-sm">
                <i class="fas fa-undo"></i> Quay lại
            </a>
        </div>
    </footer>
@endsection

@include('backend::config.shared.modal-gallery-image.modal-select')

@section('styles')
<link href="{{ asset('/assets/backend/css/modules/config/index.css') }}" rel="stylesheet">
@endsection

@section('scripts')
<script src="{{ asset('/assets/backend/plugins/fancybox/jquery.fancybox.min.js') }}"></script>
<script src="{{ asset('/assets/backend/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('/assets/backend/plugins/touchpunch/jquery.ui.touch-punch.min.js') }}"></script>
<script src="{{ asset('/assets/backend/plugins/mustache/mustache.js') }}"></script>
<script src="{{ asset('/assets/backend/plugins/mustache/jquery.mustache.js') }}"></script>
@if(!$readOnly)
<script src="{{ asset('/assets/backend/js/modules/shared/gallery.js') }}"></script>
@endif
<script src="{{ asset('assets/backend/js/shared/select2-multiple.js?v=1.0.0') }}" type="text/javascript"></script>
@endsection
