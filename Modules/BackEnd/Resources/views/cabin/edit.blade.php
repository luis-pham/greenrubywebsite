@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    
    @php
        $breadcrumbItems = [
            ['label' => __('backend::cabin.breadcrumb_manage'), 'url' => route('backend.cabin.index')],
            ['label' => __('backend::cabin.breadcrumb_edit')]
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
        $updateRouteName = \Modules\BackEnd\Helpers\Utilities::getRouteName('backend.cabin.update');
        $updateRouteParams = ['id' => $obj->id, 'lastUrl' => Request::get('lastUrl')];
        if ($languageCode) {
            $updateRouteParams['languageCode'] = $languageCode;
        }
    @endphp
    {{ Form::model($obj, ['route' => [$updateRouteName, $updateRouteParams], 'id' => 'cabin-form']) }}
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
                                    <label class="facility-dynamic-label" data-label-key="label_name">{{ __('backend::cabin.label_name') }} <span class="text-danger facility-required-star">*</span></label>
                                    {{ Form::text('name', old('name', $obj->name ?? null), ['class' => 'form-control facility-dynamic-placeholder', 'data-placeholder-key' => 'placeholder_name', 'placeholder' => __('backend::cabin.placeholder_name'), 'maxlength' => 100, 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('backend::cabin.label_cabin_type') }} <span class="text-danger">*</span></label>
                                    <select name="group_id" class="form-control" id="group-id-select" autocomplete="off">
                                        <option value="">{{ __('backend::cabin.placeholder_cabin_type') }}</option>
                                        @foreach($listCabinType as $cabinType)
                                            <option value="{{ $cabinType->id }}" data-slug="{{ $cabinType->slug ?? '' }}" {{ (old('group_id', $obj->group_id ?? null) == $cabinType->id) ? 'selected' : '' }}>{{ $cabinType->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row facility-profile-section" id="facility-section-view" data-section="view">
                            <div class="col-md-6" id="view-input-wrapper">
                                <div class="form-group">
                                    <label class="facility-dynamic-label" data-label-key="label_view">{{ __('backend::cabin.label_view') }} <span class="text-danger facility-required-star">*</span></label>
                                    {{ Form::text('view', old('view', $obj->view ?? null), ['class' => 'form-control facility-dynamic-placeholder', 'data-placeholder-key' => 'placeholder_view', 'placeholder' => __('backend::cabin.placeholder_view'), 'maxlength' => 50, 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                            @php
                                $selectedGroup = $listCabinType->firstWhere('id', old('group_id', $obj->group_id ?? null));
                                $selectedGroupSlug = $selectedGroup ? ($selectedGroup->slug ?? '') : '';
                                $showCabinClass = in_array(strtolower($selectedGroupSlug), ['phong-o', 'phong_o', 'accommodation']);
                            @endphp
                            <div class="col-md-6 facility-profile-section" id="facility-section-cabin-class" data-section="cabin_class" style="{{ $showCabinClass ? '' : 'display:none;' }}">
                                <div class="form-group">
                                    <label>{{ __('backend::cabin.label_cabin_class') }}</label>
                                    @php
                                        $listCabinClass = [
                                            'Suite' => __('backend::cabin.cabin_class_suite'),
                                            'Balcony' => __('backend::cabin.cabin_class_balcony'),
                                            'Ocean View' => __('backend::cabin.cabin_class_ocean_view'),
                                        ];
                                    @endphp
                                    {{ Form::select('cabin_class', ['' => __('backend::cabin.placeholder_cabin_class')] + $listCabinClass, old('cabin_class', $obj->cabin_class ?? null), ['class' => 'form-control', 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="facility-dynamic-label" data-label-key="label_summary">{{ __('backend::cabin.label_summary') }} <span class="text-danger facility-required-star">*</span></label>
                            {{ Form::textarea('summary', old('summary', $obj->summary ?? null), ['class' => 'form-control facility-dynamic-placeholder', 'data-placeholder-key' => 'placeholder_summary', 'placeholder' => __('backend::cabin.placeholder_summary'), 'rows' => 2, 'maxlength' => 200, 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group mb-0">
                            <label class="facility-dynamic-label" data-label-key="label_content">{{ __('backend::cabin.label_content') }}</label>
                            {{ Form::textarea('content', old('content', $obj->content ?? null), ['class' => 'form-control tinymce facility-dynamic-placeholder', 'data-placeholder-key' => 'placeholder_content', 'placeholder' => __('backend::cabin.placeholder_content'), 'rows' => 2, 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                </div>

                @php
                    $capacityForTable = max(1, min(10, (int) old('capacity', $obj->capacity ?? 1)));
                    $priceGrid = [];
                    $oldPriceForTable = old('price', []);
                    if (!empty($oldPriceForTable) && is_array($oldPriceForTable)) {
                        foreach ($oldPriceForTable as $dur => $guests) {
                            if (!is_array($guests)) { continue; }
                            foreach ($guests as $g => $p) {
                                $priceGrid[$dur][$g] = $p;
                            }
                        }
                    } else {
                        foreach (isset($prices) ? $prices : [] as $row) {
                            $priceGrid[$row->duration][$row->guest] = $row->price;
                        }
                    }
                    $guestLabelsForTable = [
                        1 => __('backend::cabin.guest_single'),
                        2 => __('backend::cabin.guest_double'),
                        3 => __('backend::cabin.guest_triple'),
                        4 => __('backend::cabin.guest_quad'),
                        5 => __('backend::cabin.guest_count', ['n' => 5]),
                        6 => __('backend::cabin.guest_count', ['n' => 6]),
                        7 => __('backend::cabin.guest_count', ['n' => 7]),
                        8 => __('backend::cabin.guest_count', ['n' => 8]),
                    ];
                @endphp
                <div class="card mb-3 facility-profile-section" id="facility-section-price" data-section="price">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-table"></i> {{ __('backend::cabin.section_price') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="price-table">
                                <thead class="thead-light">
                                    <tr id="price-table-header">
                                        <th class="th-duration">{{ __('backend::cabin.col_duration') }}</th>
                                        @for ($g = 1; $g <= $capacityForTable; $g++)
                                            <th>{{ $guestLabelsForTable[$g] ?? __('backend::cabin.guest_count', ['n' => $g]) }}</th>
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody id="price-table-body">
                                    @foreach(\Modules\BackEnd\Helpers\CruiseUtils::getListDuration() as $duration => $label)
                                    <tr data-duration="{{ $duration }}">
                                        <td class="td-duration">{{ $label }}</td>
                                        @for ($g = 1; $g <= $capacityForTable; $g++)
                                            @php
                                                $val = $priceGrid[$duration][$g] ?? '';
                                                if ($val !== '') {
                                                    $val = number_format((float) str_replace(',', '.', $val), 0, ',', '.');
                                                }
                                            @endphp
                                            <td>
                                                <input type="text"
                                                    name="price[{{ $duration }}][{{ $g }}]"
                                                    data-duration="{{ $duration }}"
                                                    data-guest="{{ $g }}"
                                                    class="form-control form-control-sm text-right price-input"
                                                    placeholder="0"
                                                    value="{{ $val }}"
                                                    class="price-input-full">
                                            </td>
                                        @endfor
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted small mb-0 mt-2">{{ __('backend::cabin.price_required_note') }}</p>
                    </div>
                </div>

                <div class="row" id="facility-row-rooms-amenities">
                    <div class="col-md-6 facility-profile-section" id="facility-section-rooms" data-section="rooms">
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
                                <div id="selected-rooms">
                                    @if (!old('room_title') && isset($rooms) && count($rooms) > 0)
                                        @foreach ($rooms as $room)
                                            <div class="room-pill d-flex align-items-start justify-content-between">
                                                <div class="flex-grow-1 mr-2">
                                                    <input type="hidden" name="room_title[]" value="{{ htmlspecialchars($room->title, ENT_QUOTES, 'UTF-8') }}">
                                                    <input type="hidden" name="room_description[]" value="{{ htmlspecialchars($room->description ?? '', ENT_QUOTES, 'UTF-8') }}">
                                                    <strong class="room-title-display{{ $room->title ? '' : ' placeholder' }}" data-placeholder="{{ __('backend::cabin.placeholder_room_name') }}">{{ $room->title ?: __('backend::cabin.placeholder_room_name') }}</strong>
                                                    <span class="room-desc-display{{ $room->description ? '' : ' placeholder' }}" data-placeholder="{{ __('backend::cabin.placeholder_room_desc') }}">{{ $room->description ?: __('backend::cabin.placeholder_room_desc') }}</span>
                                                </div>
                                                <button type="button" class="btn btn-link text-danger p-0 btn-remove-room-pill"><i class="fas fa-times"></i></button>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
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
                                <div id="selected-amenities">
                                    @if (!old('amenity_ids') && !old('amenity_name'))
                                        @foreach (isset($listAmenity) ? $listAmenity : [] as $amenity)
                                            @if (in_array($amenity->id, $selectedAmenityIds ?? []))
                                                @php
                                                    $iconUrl = $amenity->icon ? Utilities::getFileLink($amenity->icon) : null;
                                                @endphp
                                                <div class="amenity-pill" data-id="{{ $amenity->id }}">
                                                    <input type="hidden" name="amenity_ids[]" value="{{ $amenity->id }}">
                                                    <button type="button" class="btn btn-link text-dark p-0 btn-remove-amenity-pill amenity-pill-remove" title="{{ __('backend::cabin.btn_delete') }}"><i class="fas fa-times"></i></button>
                                                    <div class="d-flex align-items-center">
                                                        @if ($iconUrl)
                                                            <img src="{{ $iconUrl }}" alt="{{ html_entity_decode($amenity->name, ENT_QUOTES, 'UTF-8') }}" class="cabin-pill-icon mr-2" />
                                                        @endif
                                                        <strong class="text-uppercase small mb-0 amenity-name" title="{{ html_entity_decode($amenity->name, ENT_QUOTES, 'UTF-8') }}">{{ html_entity_decode($amenity->name, ENT_QUOTES, 'UTF-8') }}</strong>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0 facility-dynamic-section-title" data-section-key="section_operations"><i class="fas fa-cogs"></i> {{ __('backend::cabin.section_operations') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>{{ __('backend::cabin.label_belongs_cruise') }} <span class="text-danger">*</span></label>
                            {{ Form::select('cruise_id', $listCruise, old('cruise_id', $obj->cruise_id ?? null), ['class' => 'form-control', 'placeholder' => __('backend::cabin.placeholder_select_cruise'), 'autocomplete' => 'off']) }}
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6 facility-profile-section" id="facility-section-capacity" data-section="capacity">
                                <div class="form-group">
                                    <label class="facility-dynamic-label" data-label-key="label_capacity_max">{{ __('backend::cabin.label_capacity_max') }} <span class="text-danger facility-required-star">*</span></label>
                                    {{ Form::number('capacity', old('capacity', $obj->capacity ?? null), ['class' => 'form-control', 'id' => 'capacity-input', 'placeholder' => '0', 'min' => 1, 'max' => 10, 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                            <div class="col-12 col-md-6 facility-profile-section" id="facility-section-area" data-section="area">
                                <div class="form-group">
                                    <label class="facility-dynamic-label" data-label-key="label_area_m2">{{ __('backend::cabin.label_area_m2') }} <span class="text-danger facility-required-star">*</span></label>
                                    {{ Form::number('area', old('area', $obj->area ?? null), ['class' => 'form-control', 'placeholder' => '0', 'step' => '0.01', 'min' => 1, 'max' => 10000, 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group facility-profile-section" id="facility-section-over-capacity" data-section="over_capacity">
                            <label>{{ __('backend::cabin.label_over_capacity_title') }}</label>
                            <div class="row">
                                <div class="col-6 col-md-3 col-cabin-field">
                                    <div class="form-group mb-2 mb-md-0">
                                        <label class="small d-block text-uppercase mb-1">{{ __('backend::cabin.label_over_capacity_adult') }}</label>
                                        {{ Form::number('over_capacity_adult', old('over_capacity_adult', $obj->over_capacity_adult ?? null), ['class' => 'form-control', 'placeholder' => '0', 'min' => 0, 'max' => 50, 'autocomplete' => 'off']) }}
                                    </div>
                                </div>
                                <div class="col-6 col-md-3 col-cabin-field">
                                    <div class="form-group mb-2 mb-md-0">
                                        <label class="small d-block text-uppercase mb-1">{{ __('backend::cabin.label_over_capacity_child_6_12') }}</label>
                                        {{ Form::number('over_capacity_child_6_12', old('over_capacity_child_6_12', $obj->over_capacity_child_6_12 ?? null), ['class' => 'form-control', 'placeholder' => '0', 'min' => 0, 'max' => 50, 'autocomplete' => 'off']) }}
                                    </div>
                                </div>
                                <div class="col-6 col-md-3 col-cabin-field">
                                    <div class="form-group mb-2 mb-md-0">
                                        <label class="small d-block text-uppercase mb-1">{{ __('backend::cabin.label_over_capacity_child_2_5') }}</label>
                                        {{ Form::number('over_capacity_child_2_5', old('over_capacity_child_2_5', $obj->over_capacity_child_2_5 ?? null), ['class' => 'form-control', 'placeholder' => '0', 'min' => 0, 'max' => 50, 'autocomplete' => 'off']) }}
                                    </div>
                                </div>
                                <div class="col-6 col-md-3 col-cabin-field">
                                    <div class="form-group mb-0">
                                        <label class="small d-block text-uppercase mb-1">{{ __('backend::cabin.label_over_capacity_infant') }}</label>
                                        {{ Form::number('over_capacity_infant', old('over_capacity_infant', $obj->over_capacity_infant ?? null), ['class' => 'form-control', 'placeholder' => '0', 'min' => 0, 'max' => 50, 'autocomplete' => 'off']) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0 facility-profile-section" id="facility-section-discount" data-section="discount">
                            <label>{{ __('backend::cabin.label_discount') }}</label>
                            {{ Form::number('discount_percent', old('discount_percent', $obj->discount_percent ?? null), ['class' => 'form-control', 'placeholder' => '0', 'min' => 0, 'max' => 100, 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-image"></i> {{ __('backend::cabin.section_avatar') }}</h6>
                    </div>
                    <div class="card-body">
                        {{ Form::hidden('image_link', old('image_link', $obj->image_link), ['class' => 'image-select', 'data-link-full' => Utilities::getFileLink(old('image_link', $obj->image_link)), 'data-file-manager-url' => route('backend.file.index', ['layout' => 'popup', 'type' => config('backend.fileType.image')])]) }}
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0 facility-dynamic-section-title" data-section-key="section_gallery"><i class="fas fa-images"></i> {{ __('backend::cabin.section_gallery') }}</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $cabinGalleryKey = 'image_gallery';
                            if (old($cabinGalleryKey) !== null) {
                                $listCabinImage = json_decode(old($cabinGalleryKey));
                                $listCabinImage = is_array($listCabinImage) ? $listCabinImage : ($listCabinImage ? [$listCabinImage] : []);
                            } else {
                                $listCabinImage = isset($cabinGallery) ? $cabinGallery : [];
                            }
                            $imageGalleryValue = old($cabinGalleryKey) !== null ? old($cabinGalleryKey) : json_encode($listCabinImage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        @endphp
                        <div class="gallery" key="{{ $cabinGalleryKey }}">
                            <div id="list-image-{{ $cabinGalleryKey }}" class="list-image row">
                                @foreach ($listCabinImage as $img)
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
                            {{ Form::hidden($cabinGalleryKey, $imageGalleryValue) }}
                        </div>
                    </div>
                </div>

                <div class="card mb-3 facility-profile-section" id="facility-section-audience" data-section="audience">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="m-0"><i class="fas fa-users"></i> {{ __('backend::cabin.section_audience') }}</h6>
                            <button type="button" class="btn btn-outline-primary btn-sm btn-add-audience">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="selected-audiences">
                            @if (!old('audience_name') && isset($audiences) && count($audiences) > 0)
                                @foreach ($audiences as $audience)
                                    <div class="audience-pill" data-id="{{ $audience->id }}">
                                        <input type="hidden" name="audience_name[]" value="{{ htmlspecialchars($audience->name, ENT_QUOTES, 'UTF-8') }}">
                                        <input type="hidden" name="audience_icon[]" value="{{ $audience->icon ?? '' }}">
                                        <input type="hidden" name="audience_group_ids[]" value="{{ $audience->id }}">
                                        <button type="button" class="btn btn-link text-dark p-0 btn-remove-audience-pill audience-pill-remove" title="{{ __('backend::cabin.btn_delete') }}"><i class="fas fa-times"></i></button>
                                        <div class="d-flex align-items-center flex-grow-1">
                                            @if ($audience->icon)
                                                <i class="{{ $audience->icon }} text-primary mr-2 d-flex align-items-center justify-content-center cabin-pill-icon"></i>
                                            @else
                                                <i class="fas fa-tag text-primary mr-2 d-flex align-items-center justify-content-center cabin-pill-icon"></i>
                                            @endif
                                            <strong class="text-uppercase small mb-0 audience-name-display" data-placeholder="Tên đối tượng">{{ html_entity_decode($audience->name, ENT_QUOTES, 'UTF-8') }}</strong>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
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
        $priceData = isset($prices) ? $prices : [];
        if (!empty($oldPrice) && is_array($oldPrice)) {
            $priceData = [];
            foreach ($oldPrice as $dur => $guests) {
                if (!is_array($guests)) { continue; }
                foreach ($guests as $g => $p) {
                    $priceData[] = ['duration' => (int) $dur, 'guest' => (int) $g, 'price' => $p];
                }
            }
        }
        $groupProfileMap = [];
        foreach ($listCabinType as $cabinType) {
            $groupProfileMap[(string) $cabinType->id] = \Modules\BackEnd\Helpers\FacilityProfileUtils::getProfileBySlug($cabinType->slug ?? '');
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
            priceData: @json($priceData),
            initialCapacity: {{ (int) old('capacity', $obj->capacity ?? 1) }},
            isEdit: true,
            langRoomName: @json(__('backend::cabin.placeholder_room_name')),
            langRoomDesc: @json(__('backend::cabin.placeholder_room_desc')),
            langAudienceName: @json(__('backend::cabin.placeholder_audience_name')),
            langDelete: @json(__('backend::cabin.btn_delete')),
            langGuestLabels: @json($langGuestLabels),
            langOverCapacityTotal: @json(__('backend::cabin.validation_over_capacity_total', ['capacity' => '__CAP__'])),
            facilityProfile: @json($facilityProfileConfig ?? \Modules\BackEnd\Helpers\FacilityProfileUtils::getJsConfig()),
            groupProfileMap: @json($groupProfileMap)
        };
    </script>
    <script src="{{ asset('/assets/backend/plugins/fancybox/jquery.fancybox.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/touchpunch/jquery.ui.touch-punch.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/mustache/mustache.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/mustache/jquery.mustache.js') }}"></script>
    <script src="{{ asset('/assets/backend/js/modules/shared/gallery.js') }}"></script>
    <script src="{{ asset('/assets/backend/js/modules/cabin/index.js') }}?v=0494035"></script>
@endsection
