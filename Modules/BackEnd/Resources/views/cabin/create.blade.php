@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    
    @php
        $breadcrumbItems = [
            ['label' => __('backend::cabin.breadcrumb_manage'), 'url' => route('backend.cabin.index')],
            ['label' => __('backend::cabin.breadcrumb_add')]
        ];
    @endphp
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        @include('backend::shared.breadcrumb', ['breadcrumbItems' => $breadcrumbItems])
        @include('backend::shared.action-buttons', [
            'cancelUrl' => route('backend.cabin.index'),
            'submitFormId' => 'cabin-form',
            'cancelLabel' => __('backend::cabin.btn_cancel'),
            'submitLabel' => __('backend::cabin.btn_save')
        ])
    </div>
    
    @php
        $languageCode = request()->route('languageCode');
        $storeRouteName = \Modules\BackEnd\Helpers\Utilities::getRouteName('backend.cabin.store');
        $storeRouteParams = ['lastUrl' => Request::get('lastUrl')];
        if ($languageCode) {
            $storeRouteParams['languageCode'] = $languageCode;
        }
    @endphp
    {{ Form::open(['route' => [$storeRouteName, $storeRouteParams], 'id' => 'cabin-form']) }}
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-info-circle"></i> {{ __('backend::cabin.section_overview') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('backend::cabin.label_name') }} <span class="text-danger">*</span></label>
                                    {{ Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => __('backend::cabin.placeholder_name'), 'maxlength' => 100, 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('backend::cabin.label_cabin_type') }} <span class="text-danger">*</span></label>
                                    <select name="group_id" class="form-control" id="group-id-select" autocomplete="off">
                                        <option value="">{{ __('backend::cabin.placeholder_cabin_type') }}</option>
                                        @foreach($listCabinType as $cabinType)
                                            <option value="{{ $cabinType->id }}" data-slug="{{ $cabinType->slug ?? '' }}" {{ old('group_id') == $cabinType->id ? 'selected' : '' }}>{{ $cabinType->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row" id="view-cabin-class-row">
                            <div class="col-md-6" id="view-input-wrapper">
                                <div class="form-group">
                                    <label>{{ __('backend::cabin.label_view') }} <span class="text-danger">*</span></label>
                                    {{ Form::text('view', old('view'), ['class' => 'form-control', 'placeholder' => __('backend::cabin.placeholder_view'), 'maxlength' => 50, 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                            @php
                                $selectedGroup = $listCabinType->firstWhere('id', old('group_id'));
                                $selectedGroupSlug = $selectedGroup ? ($selectedGroup->slug ?? '') : '';
                                $showCabinClass = in_array(strtolower($selectedGroupSlug), ['phong-o', 'phong_o', 'accommodation']);
                            @endphp
                            <div class="col-md-6" id="cabin-class-wrapper" style="{{ $showCabinClass ? '' : 'display:none;' }}">
                                <div class="form-group">
                                    <label>{{ __('backend::cabin.label_cabin_class') }}</label>
                                    @php
                                        $listCabinClass = [
                                            'Suite' => __('backend::cabin.cabin_class_suite'),
                                            'Balcony' => __('backend::cabin.cabin_class_balcony'),
                                            'Ocean View' => __('backend::cabin.cabin_class_ocean_view'),
                                        ];
                                    @endphp
                                    {{ Form::select('cabin_class', ['' => __('backend::cabin.placeholder_cabin_class')] + $listCabinClass, old('cabin_class'), ['class' => 'form-control', 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('backend::cabin.label_summary') }} <span class="text-danger">*</span></label>
                            {{ Form::textarea('summary', old('summary'), ['class' => 'form-control', 'placeholder' => __('backend::cabin.placeholder_summary'), 'rows' => 2, 'maxlength' => 200, 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group mb-0">
                            <label>{{ __('backend::cabin.label_content') }}</label>
                            {{ Form::textarea('content', old('content'), ['class' => 'form-control tinymce', 'placeholder' => __('backend::cabin.placeholder_content'), 'rows' => 2, 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-table"></i> {{ __('backend::cabin.section_price') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="price-table">
                                <thead class="thead-light">
                                    <tr id="price-table-header">
                                        <th class="th-duration">{{ __('backend::cabin.col_duration') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="price-table-body">
                                    @foreach(\Modules\BackEnd\Helpers\CruiseUtils::getListDuration() as $duration => $label)
                                    <tr data-duration="{{ $duration }}">
                                        <td class="td-duration">{{ $label }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted small mb-0 mt-2">{{ __('backend::cabin.price_required_note') }}</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="m-0"><i class="fas fa-door-open"></i> {{ __('backend::cabin.section_rooms') }}</h6>
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-open-room-modal">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="selected-rooms"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 text-uppercase"><i class="fas fa-star"></i> {{ __('backend::cabin.section_amenities') }}</h6>
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-open-amenity-modal">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="selected-amenities"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-cogs"></i> {{ __('backend::cabin.section_operations') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>{{ __('backend::cabin.label_belongs_cruise') }} <span class="text-danger">*</span></label>
                            {{ Form::select('cruise_id', $listCruise, old('cruise_id'), ['class' => 'form-control', 'placeholder' => __('backend::cabin.placeholder_select_cruise'), 'autocomplete' => 'off']) }}
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6 col-cabin-field">
                                <div class="form-group">
                                    <label>{{ __('backend::cabin.label_capacity_max') }} <span class="text-danger">*</span></label>
                                    {{ Form::number('capacity', old('capacity'), ['class' => 'form-control', 'id' => 'capacity-input', 'placeholder' => '0', 'min' => 1, 'max' => 10, 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-cabin-field">
                                <div class="form-group">
                                    <label>{{ __('backend::cabin.label_area_m2') }} <span class="text-danger">*</span></label>
                                    {{ Form::number('area', old('area'), ['class' => 'form-control', 'placeholder' => '0', 'step' => '0.01', 'min' => 1, 'max' => 10000, 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>{{ __('backend::cabin.label_over_capacity_title') }}</label>
                            <div class="row">
                                <div class="col-6 col-md-3 col-cabin-field">
                                    <div class="form-group mb-2 mb-md-0">
                                        <label class="small d-block text-uppercase mb-1">{{ __('backend::cabin.label_over_capacity_adult') }}</label>
                                        {{ Form::number('over_capacity_adult', old('over_capacity_adult'), ['class' => 'form-control', 'placeholder' => '0', 'min' => 0, 'max' => 50, 'autocomplete' => 'off']) }}
                                    </div>
                                </div>
                                <div class="col-6 col-md-3 col-cabin-field">
                                    <div class="form-group mb-2 mb-md-0">
                                        <label class="small d-block text-uppercase mb-1">{{ __('backend::cabin.label_over_capacity_child_6_12') }}</label>
                                        {{ Form::number('over_capacity_child_6_12', old('over_capacity_child_6_12'), ['class' => 'form-control', 'placeholder' => '0', 'min' => 0, 'max' => 50, 'autocomplete' => 'off']) }}
                                    </div>
                                </div>
                                <div class="col-6 col-md-3 col-cabin-field">
                                    <div class="form-group mb-2 mb-md-0">
                                        <label class="small d-block text-uppercase mb-1">{{ __('backend::cabin.label_over_capacity_child_2_5') }}</label>
                                        {{ Form::number('over_capacity_child_2_5', old('over_capacity_child_2_5'), ['class' => 'form-control', 'placeholder' => '0', 'min' => 0, 'max' => 50, 'autocomplete' => 'off']) }}
                                    </div>
                                </div>
                                <div class="col-6 col-md-3 col-cabin-field">
                                    <div class="form-group mb-0">
                                        <label class="small d-block text-uppercase mb-1">{{ __('backend::cabin.label_over_capacity_infant') }}</label>
                                        {{ Form::number('over_capacity_infant', old('over_capacity_infant'), ['class' => 'form-control', 'placeholder' => '0', 'min' => 0, 'max' => 50, 'autocomplete' => 'off']) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label>{{ __('backend::cabin.label_discount') }}</label>
                            {{ Form::number('discount_percent', old('discount_percent', 0), ['class' => 'form-control', 'placeholder' => '0', 'min' => 0, 'max' => 100, 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-image"></i> {{ __('backend::cabin.section_avatar') }}</h6>
                    </div>
                    <div class="card-body">
                        {{ Form::hidden('image_link', old('image_link'), ['class' => 'image-select', 'data-link-full' => Utilities::getFileLink(old('image_link')), 'data-file-manager-url' => route('backend.file.index', ['layout' => 'popup', 'type' => config('backend.fileType.image')])]) }}
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-images"></i> {{ __('backend::cabin.section_gallery') }}</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $cabinGalleryKey = 'image_gallery';
                            $listCabinImage = old($cabinGalleryKey, []);
                            if (is_string($listCabinImage)) {
                                $listCabinImage = $listCabinImage ? (json_decode($listCabinImage) ?: []) : [];
                            }
                            if (!is_array($listCabinImage)) {
                                $listCabinImage = $listCabinImage ? [$listCabinImage] : [];
                            }
                        @endphp
                        <div class="gallery" key="{{ $cabinGalleryKey }}">
                            <div id="list-image-{{ $cabinGalleryKey }}" class="list-image row">
                                @foreach ($listCabinImage as $img)
                                    @php
                                        $thumbnail = is_object($img) && property_exists($img, 'thumbnail') ? $img->thumbnail : null;
                                        $thumbnailFull = Utilities::getFileLink(!$thumbnail ? $img->link : $thumbnail);
                                        $imgLink = is_object($img) ? $img->link : $img;
                                        $imgTitle = is_object($img) && property_exists($img, 'title') ? $img->title : '';
                                    @endphp
                                    <div class="item col-4 col-lg-3" data-obj="{{ json_encode($img, JSON_UNESCAPED_UNICODE) }}">
                                        <div class="box-dragdrop position-relative">
                                            <div class="image-wrapper position-relative">
                                                <a href="{{ Utilities::getFileLink($imgLink) }}" data-fancybox="gallery-{{ $cabinGalleryKey }}">
                                                    <img src="{{ $thumbnailFull }}" alt="{{ $imgTitle }}" class="position-absolute w-100 h-100" />
                                                </a>
<div class="action position-absolute">
                                                        <a href="#" class="btn-delete btn btn-danger btn-sm" title="{{ __('backend::cabin.btn_delete') }}">
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
                            {{ Form::hidden($cabinGalleryKey, is_string(old($cabinGalleryKey)) ? old($cabinGalleryKey) : json_encode($listCabinImage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="m-0"><i class="fas fa-users"></i> {{ __('backend::cabin.section_audience') }}</h6>
                            <button type="button" class="btn btn-outline-primary btn-sm btn-add-audience">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="selected-audiences"></div>
                    </div>
                </div>

            </div>
        </div>
    {{ Form::close() }}
@endsection

@include('backend::shared.modal-amenity', ['listAmenity' => $listAmenity, 'modalTitle' => __('backend::cabin.modal_amenity')])
@include('backend::shared.modal-audience', ['listAudience' => $listAudience, 'modalTitle' => __('backend::cabin.modal_audience')])
@include('backend::config.shared.modal-gallery-image.modal-select')

@section('styles')
    @include('backend::shared.modal-styles')
    <link href="{{ asset('/assets/backend/css/modules/cabin/index.css') }}" rel="stylesheet">
    <link href="{{ asset('/assets/backend/css/modules/config/index.css') }}" rel="stylesheet">
@endsection

@section('scripts')
    @php
        $oldRoomTitles = old('room_title', []);
        $oldRoomDescriptions = old('room_description', []);
        $oldAmenityIds = old('amenity_ids', []);
        $oldAmenityName = old('amenity_name', []);
        $oldAmenityDescription = old('amenity_description', []);
        $oldAmenityIcon = old('amenity_icon', []);
        $oldAudienceName = old('audience_name', []);
        $oldAudienceIcon = old('audience_icon', []);
        $oldAudienceIds = old('audience_group_ids', []);
        $oldPrice = old('price', []);
        $amenityMap = [];
        foreach (isset($listAmenity) ? $listAmenity : [] as $a) {
            $amenityMap[$a->id] = [
                'name' => $a->name ?? '',
                'icon' => $a->icon ? Utilities::getFileLink($a->icon) : '',
            ];
        }
        $audienceMap = [];
        foreach (isset($listAudience) ? $listAudience : [] as $g) {
            $icon = null;
            if ($g->description) {
                $descData = json_decode($g->description, true);
                if (isset($descData['icon'])) {
                    $icon = $descData['icon'];
                }
            }
            $audienceMap[$g->id] = ['name' => $g->name ?? '', 'icon' => $icon ?? ''];
        }
        $langGuestLabels = [
            1 => __('backend::cabin.guest_single'),
            2 => __('backend::cabin.guest_double'),
            3 => __('backend::cabin.guest_triple'),
            4 => __('backend::cabin.guest_quad'),
            5 => __('backend::cabin.guest_count', ['n' => 5]),
            6 => __('backend::cabin.guest_count', ['n' => 6]),
            7 => __('backend::cabin.guest_count', ['n' => 7]),
            8 => __('backend::cabin.guest_count', ['n' => 8]),
            9 => __('backend::cabin.guest_count', ['n' => 9]),
            10 => __('backend::cabin.guest_count', ['n' => 10]),
        ];
    @endphp
    @include('backend::shared.js-helpers')
    <script type="text/javascript">
        window.CabinFormConfig = {
            oldRoomTitles: @json($oldRoomTitles),
            oldRoomDescriptions: @json($oldRoomDescriptions),
            oldAmenityIds: @json($oldAmenityIds),
            oldAmenityName: @json($oldAmenityName),
            oldAmenityDescription: @json($oldAmenityDescription),
            oldAmenityIcon: @json($oldAmenityIcon),
            oldAudienceName: @json($oldAudienceName),
            oldAudienceIcon: @json($oldAudienceIcon),
            oldAudienceIds: @json($oldAudienceIds),
            oldPrice: @json($oldPrice),
            listAmenityMap: @json($amenityMap),
            listAudienceMap: @json($audienceMap),
            isEdit: false,
            langRoomName: @json(__('backend::cabin.placeholder_room_name')),
            langRoomDesc: @json(__('backend::cabin.placeholder_room_desc')),
            langAudienceName: @json(__('backend::cabin.placeholder_audience_name')),
            langDelete: @json(__('backend::cabin.btn_delete')),
            langGuestLabels: @json($langGuestLabels),
            langOverCapacityTotal: @json(__('backend::cabin.validation_over_capacity_total', ['capacity' => '__CAP__']))
        };
    </script>
    <script src="{{ asset('/assets/backend/plugins/fancybox/jquery.fancybox.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/touchpunch/jquery.ui.touch-punch.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/mustache/mustache.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/mustache/jquery.mustache.js') }}"></script>
    <script src="{{ asset('/assets/backend/js/modules/shared/gallery.js') }}"></script>
    <script src="{{ asset('/assets/backend/js/modules/cabin/index.js') }}"></script>
@endsection
