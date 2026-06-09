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
                                    <label>{{ __('backend::cabin.label_name') }} <span class="text-danger">*</span></label>
                                    {{ Form::text('name', old('name', $obj->name ?? null), ['class' => 'form-control', 'placeholder' => __('backend::cabin.placeholder_name'), 'maxlength' => 100, 'autocomplete' => 'off']) }}
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
                        <div class="row" id="view-cabin-class-row">
                            <div class="col-md-6" id="view-input-wrapper">
                                <div class="form-group">
                                    <label>{{ __('backend::cabin.label_view') }} <span class="text-danger">*</span></label>
                                    {{ Form::text('view', old('view', $obj->view ?? null), ['class' => 'form-control', 'placeholder' => __('backend::cabin.placeholder_view'), 'maxlength' => 50, 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                            @php
                                $selectedGroup = $listCabinType->firstWhere('id', old('group_id', $obj->group_id ?? null));
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
                                    {{ Form::select('cabin_class', ['' => __('backend::cabin.placeholder_cabin_class')] + $listCabinClass, old('cabin_class', $obj->cabin_class ?? null), ['class' => 'form-control', 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('backend::cabin.label_summary') }} <span class="text-danger">*</span></label>
                            {{ Form::textarea('summary', old('summary', $obj->summary ?? null), ['class' => 'form-control', 'placeholder' => __('backend::cabin.placeholder_summary'), 'rows' => 2, 'maxlength' => 200, 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group mb-0">
                            <label>{{ __('backend::cabin.label_content') }}</label>
                            {{ Form::textarea('content', old('content', $obj->content ?? null), ['class' => 'form-control tinymce', 'placeholder' => __('backend::cabin.placeholder_content'), 'rows' => 2, 'autocomplete' => 'off']) }}
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
                        <h6 class="m-0"><i class="fas fa-cogs"></i> {{ __('backend::cabin.section_operations') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>{{ __('backend::cabin.label_belongs_cruise') }} <span class="text-danger">*</span></label>
                            {{ Form::select('cruise_id', $listCruise, old('cruise_id', $obj->cruise_id ?? null), ['class' => 'form-control', 'placeholder' => __('backend::cabin.placeholder_select_cruise'), 'autocomplete' => 'off']) }}
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6 col-cabin-field">
                                <div class="form-group">
                                    <label>{{ __('backend::cabin.label_capacity_max') }} <span class="text-danger">*</span></label>
                                    {{ Form::number('capacity', old('capacity', $obj->capacity ?? null), ['class' => 'form-control', 'id' => 'capacity-input', 'placeholder' => '0', 'min' => 1, 'max' => 10, 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-cabin-field">
                                <div class="form-group">
                                    <label>{{ __('backend::cabin.label_area_m2') }} <span class="text-danger">*</span></label>
                                    {{ Form::number('area', old('area', $obj->area ?? null), ['class' => 'form-control', 'placeholder' => '0', 'step' => '0.01', 'min' => 1, 'max' => 10000, 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
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

                        <div class="form-group mb-0">
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
                        <h6 class="m-0"><i class="fas fa-images"></i> {{ __('backend::cabin.section_gallery') }}</h6>
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
    @endphp
    @include('backend::shared.js-helpers')
    <script src="{{ asset('/assets/backend/plugins/fancybox/jquery.fancybox.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/touchpunch/jquery.ui.touch-punch.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/mustache/mustache.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/mustache/jquery.mustache.js') }}"></script>
    <script src="{{ asset('/assets/backend/js/modules/shared/gallery.js') }}"></script>
    <script type="text/javascript">
        (function () {
            var oldRoomTitles = @json($oldRoomTitles);
            var oldRoomDescriptions = @json($oldRoomDescriptions);
            var oldAmenityIds = @json($oldAmenityIds);
            var oldAmenityName = @json($oldAmenityName);
            var oldAmenityDescription = @json($oldAmenityDescription);
            var oldAmenityIcon = @json($oldAmenityIcon);
            var oldAudienceName = @json($oldAudienceName);
            var oldAudienceIcon = @json($oldAudienceIcon);
            var oldAudienceIds = @json($oldAudienceIds);
            var listAmenityMap = @json($amenityMap);
            var priceData = @json($priceData);
            var initialCapacity = {{ (int) old('capacity', $obj->capacity ?? 1) }};
            var currentPriceData = {};
            if (priceData && priceData.length > 0) {
                for (var i = 0; i < priceData.length; i++) {
                    var p = priceData[i];
                    if (!currentPriceData[p.duration]) {
                        currentPriceData[p.duration] = {};
                    }
                    currentPriceData[p.duration][p.guest] = p.price;
                }
            }

            var langRoomName = @json(__('backend::cabin.placeholder_room_name'));
            var langRoomDesc = @json(__('backend::cabin.placeholder_room_desc'));
            var langDelete = @json(__('backend::cabin.btn_delete'));
            var langOverCapacityTotal = @json(__('backend::cabin.validation_over_capacity_total', ['capacity' => '__CAP__']));

            function renderRoom(name, description) {
                var escapedName = name ? $('<div>').text(name).html() : '';
                var escapedDesc = description ? $('<div>').text(description).html() : '';
                var titleClass = name ? '' : ' placeholder';
                var descClass = description ? '' : ' placeholder';
                var html = '<div class="room-pill d-flex align-items-start justify-content-between">' +
                    '<div class="flex-grow-1 mr-2">' +
                        '<input type="hidden" name="room_title[]" value="' + escapedName + '">' +
                        '<input type="hidden" name="room_description[]" value="' + escapedDesc + '">' +
                        '<strong class="room-title-display' + titleClass + '" data-placeholder="' + langRoomName + '" title="' + (name || '') + '" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">' + (escapedName || langRoomName) + '</strong>' +
                        '<span class="room-desc-display' + descClass + '" data-placeholder="' + langRoomDesc + '" title="' + (description || '') + '" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">' + (escapedDesc || langRoomDesc) + '</span>' +
                    '</div>' +
                    '<button type="button" class="btn btn-link text-danger p-0 btn-remove-room-pill"><i class="fas fa-times"></i></button>' +
                '</div>';
                $('#selected-rooms').append(html);
            }

            $(document).on('click', '.btn-open-room-modal', function () {
                renderRoom('', '');
            });

            function toggleCabinClassVisibility() {
                var $groupSelect = $('#cabin-form').find('select[name="group_id"]');
                var $selected = $groupSelect.find('option:selected');
                var slug = ($selected.data('slug') || '').toString().toLowerCase().replace(/\s+/g, '-');
                var isAccommodation = (slug === 'phong-o' || slug === 'phong_o' || slug === 'accommodation');
                if (isAccommodation) {
                    $('#cabin-class-wrapper').show();
                    $('#view-input-wrapper').removeClass('col-md-12').addClass('col-md-6');
                } else {
                    $('#cabin-class-wrapper').hide();
                    $('#view-input-wrapper').removeClass('col-md-6').addClass('col-md-12');
                    $('#cabin-form').find('select[name="cabin_class"]').val('');
                }
            }
            $('#cabin-form').on('change', 'select[name="group_id"]', toggleCabinClassVisibility);
            toggleCabinClassVisibility();

            $(document).on('click', '.btn-remove-room-pill', function (e) {
                e.stopPropagation();
                $(this).closest('.room-pill').remove();
            });

            function startRoomInlineEdit($display, isDesc) {
                var placeholder = $display.data('placeholder');
                var $pill = $display.closest('.room-pill');
                var $hidden = isDesc ? $pill.find('input[name="room_description[]"]') : $pill.find('input[name="room_title[]"]');
                var currentVal = $hidden.val() || '';
                var maxLen = isDesc ? 200 : 50;
                var $input = $('<input type="text" class="form-control form-control-sm ' + (isDesc ? 'room-desc-edit' : 'room-title-edit') + '" maxlength="' + maxLen + '">').val(currentVal);
                $display.after($input).hide();
                $input.focus();
                function commit() {
                    var val = $input.val().trim();
                    var displayText = val || placeholder;
                    var escaped = $('<div>').text(val).html();
                    $hidden.val(escaped);
                    $display.attr('title', val);
                    $display.text(displayText).toggleClass('placeholder', !val).show();
                    $input.remove();
                }
                $input.on('blur', commit);
                $input.on('keydown', function (e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        $input.blur();
                    }
                });
            }

            $(document).on('click', '#selected-rooms .room-title-display', function () {
                startRoomInlineEdit($(this), false);
            });
            $(document).on('click', '#selected-rooms .room-desc-display', function () {
                startRoomInlineEdit($(this), true);
            });

            function renderExistingAmenity(id, name, icon) {
                if ($('#selected-amenities .amenity-pill[data-id="' + id + '"]').length) {
                    return;
                }
                var escapedName = $('<div>').text(name).html();
                var escapedIcon = icon ? $('<div>').text(icon).html() : '';
                var iconHtml = escapedIcon
                    ? '<img src="' + escapedIcon + '" alt="" class="cabin-pill-icon mr-2" />'
                    : '';
                var html = '<div class="amenity-pill" data-id="' + id + '">' +
                    '<input type="hidden" name="amenity_ids[]" value="' + id + '">' +
                    '<button type="button" class="btn btn-link text-dark p-0 btn-remove-amenity-pill amenity-pill-remove" title="{{ __('backend::cabin.btn_delete') }}"><i class="fas fa-times"></i></button>' +
                    '<div class="d-flex align-items-center">' + iconHtml + '<strong class="text-uppercase small mb-0 amenity-name" title="' + (name || '') + '">' + escapedName + '</strong></div>' +
                    '</div>';
                $('#selected-amenities').append(html);
            }

            function renderNewAmenity(name, description, icon) {
                var escapedName = $('<div>').text(name).html();
                var escapedDesc = description ? $('<div>').text(description).html() : '';
                var escapedIcon = icon ? $('<div>').text(icon).html() : '';
                var iconHtml = escapedIcon
                    ? '<img src="' + escapedIcon + '" alt="" class="cabin-pill-icon mr-2" />'
                    : '';
                var html = '<div class="amenity-pill" data-new="1">' +
                    '<input type="hidden" name="amenity_name[]" value="' + escapedName + '">' +
                    '<input type="hidden" name="amenity_description[]" value="' + escapedDesc + '">' +
                    '<input type="hidden" name="amenity_icon[]" value="' + escapedIcon + '">' +
                    '<button type="button" class="btn btn-link text-dark p-0 btn-remove-amenity-pill amenity-pill-remove" title="{{ __('backend::cabin.btn_delete') }}"><i class="fas fa-times"></i></button>' +
                    '<div class="d-flex align-items-center">' + iconHtml + '<strong class="text-uppercase small mb-0 amenity-name" title="' + (name || '') + '">' + escapedName + '</strong></div>' +
                    '</div>';
                $('#selected-amenities').append(html);
            }

            function updateAmenitySelectionCount() {
                var n = $('#amenity-modal .amenity-item.selected').length;
                $('#amenity-modal .amenity-selection-count').text(n);
            }

            $(document).on('click', '.btn-open-amenity-modal', function () {
                $('#amenity-search').val('');
                $('#amenity-modal .amenity-card-wrapper').show();
                $('#amenity-modal .amenity-item').removeClass('selected in-list');
                $('#amenity-modal .amenity-item').each(function () {
                    var id = $(this).data('id');
                    if (id && $('#selected-amenities .amenity-pill[data-id="' + id + '"]').length > 0) {
                        $(this).addClass('selected in-list');
                    }
                });
                updateAmenitySelectionCount();
                $('#amenity-modal').modal('show');
            });

            $(document).on('keyup', '#amenity-search', function () {
                var q = $(this).val().toLowerCase();
                $('#amenity-modal-list').find('.amenity-card-wrapper').each(function () {
                    var name = $(this).find('.amenity-item').data('name');
                    if (name && typeof name === 'string') {
                        $(this).toggle(name.toLowerCase().indexOf(q) !== -1);
                    }
                });
            });

            $(document).on('click', '#amenity-modal .amenity-item', function () {
                var $card = $(this);
                if ($card.hasClass('in-list')) {
                    var id = $card.data('id');
                    $('#selected-amenities .amenity-pill[data-id="' + id + '"]').closest('.amenity-pill').remove();
                    $card.removeClass('selected in-list');
                } else {
                    $card.toggleClass('selected');
                }
                updateAmenitySelectionCount();
            });

            $(document).on('click', '.btn-confirm-amenity-selection', function () {
                $('#amenity-modal .amenity-item.selected').each(function () {
                    var id = $(this).data('id');
                    var name = $(this).data('name');
                    var icon = $(this).data('icon');
                    if (id && $('#selected-amenities .amenity-pill[data-id="' + id + '"]').length === 0) {
                        renderExistingAmenity(id, name, icon);
                    }
                });
                $('#amenity-modal .amenity-item').removeClass('selected in-list');
                updateAmenitySelectionCount();
            });

            $(document).on('click', '.btn-remove-amenity-pill', function () {
                $(this).closest('.amenity-pill').remove();
            });

            function renderAudience(name, icon) {
                var escapedName = name ? $('<div>').text(name).html() : '';
                var escapedIcon = icon ? $('<div>').text(icon).html() : '';
                var nameClass = name ? '' : ' placeholder';
                var nameText = name || 'Tên đối tượng';
                var iconHtml = escapedIcon ? 
                    '<i class="' + escapedIcon + ' text-primary mr-2 d-flex align-items-center justify-content-center" style="font-size:1.25rem;width:1.5rem;min-width:1.5rem;flex-shrink:0;line-height:1"></i>' : 
                    '<i class="fas fa-tag text-primary mr-2 d-flex align-items-center justify-content-center" style="font-size:1.25rem;width:1.5rem;min-width:1.5rem;flex-shrink:0;line-height:1"></i>';
                var html = '<div class="audience-pill">' +
                    '<input type="hidden" name="audience_name[]" value="' + escapedName + '">' +
                    '<input type="hidden" name="audience_icon[]" value="' + escapedIcon + '">' +
                    '<button type="button" class="btn btn-link text-dark p-0 btn-remove-audience-pill audience-pill-remove" title="{{ __('backend::cabin.btn_delete') }}"><i class="fas fa-times"></i></button>' +
                    '<div class="d-flex align-items-center flex-grow-1">' +
                        iconHtml +
                        '<strong class="text-uppercase small mb-0 audience-name-display' + nameClass + '" data-placeholder="Tên đối tượng" style="cursor: default; flex-grow: 1;">' + (escapedName || nameText) + '</strong>' +
                    '</div>' +
                '</div>';
                $('#selected-audiences').append(html);
            }

            function updateAudienceSelectionCount() {
                var n = $('#audience-modal .audience-item.selected').length;
                $('#audience-modal .audience-selection-count').text(n);
            }

            $(document).on('click', '.btn-add-audience', function () {
                $('#audience-search').val('');
                $('#audience-modal .audience-card-wrapper').show();
                $('#audience-modal .audience-item').removeClass('selected in-list');
                $('#audience-modal .audience-item').each(function () {
                    var $item = $(this);
                    var name = $item.data('name');
                    if (name) {
                        var found = false;
                        $('#selected-audiences .audience-pill .audience-name-display').each(function () {
                            var currentName = $(this).text().trim();
                            if (currentName === name) {
                                found = true;
                                return false;
                            }
                        });
                        if (found) {
                            $item.addClass('selected in-list');
                        }
                    }
                });
                updateAudienceSelectionCount();
                $('#audience-modal').modal('show');
            });

            $(document).on('keyup', '#audience-search', function () {
                var q = $(this).val().toLowerCase();
                $('#audience-modal-list').find('.audience-card-wrapper').each(function () {
                    var name = $(this).find('.audience-item').data('name');
                    if (name && typeof name === 'string') {
                        $(this).toggle(name.toLowerCase().indexOf(q) !== -1);
                    }
                });
            });

            $(document).on('click', '#audience-modal .audience-item', function () {
                var $card = $(this);
                if ($card.hasClass('in-list')) {
                    var name = $card.data('name');
                    if (name) {
                        $('#selected-audiences .audience-pill .audience-name-display').each(function () {
                            if ($(this).text().trim() === name) {
                                $(this).closest('.audience-pill').remove();
                                return false;
                            }
                        });
                    }
                    $card.removeClass('selected in-list');
                } else {
                    $card.toggleClass('selected');
                }
                updateAudienceSelectionCount();
            });

            $(document).on('click', '.btn-confirm-audience-selection', function () {
                $('#audience-modal .audience-item.selected').each(function () {
                    var id = $(this).data('id');
                    var name = $(this).data('name') || '';
                    var icon = $(this).data('icon') || '';
                    if (!id || !name) { return; }

                    var exists = false;
                    $('#selected-audiences .audience-pill').each(function () {
                        if (parseInt($(this).data('id')) === parseInt(id)) {
                            exists = true;
                            return false;
                        }
                    });
                    if (!exists) {
                        renderAudience(name, icon);
                        var $pill = $('#selected-audiences .audience-pill').last();
                        $pill.attr('data-id', id);
                        $pill.append('<input type="hidden" name="audience_group_ids[]" value="' + id + '">');
                    }
                });
                $('#audience-modal .audience-item').removeClass('selected in-list');
                updateAudienceSelectionCount();
            });

            $(document).on('click', '.btn-remove-audience-pill', function () {
                $(this).closest('.audience-pill').remove();
            });

            function getGuestLabel(guestCount) {
                var labels = {
                    1: '1 Khách (Single)',
                    2: '2 Khách (Double)',
                    3: '3 Khách (Triple)',
                    4: '4 Khách (Quad)',
                    5: '5 Khách',
                    6: '6 Khách',
                    7: '7 Khách',
                    8: '8 Khách'
                };
                return labels[guestCount] || guestCount + ' Khách';
            }

            function updatePriceTable() {
                var capacityInput = $('#capacity-input');
                if (capacityInput.length === 0) {
                    capacityInput = $('input[name="capacity"]');
                }
                var capacity = parseInt(capacityInput.val(), 10) || 0;
                if (capacity <= 0 && typeof initialCapacity !== 'undefined') {
                    capacity = initialCapacity;
                }
                if (capacity <= 0) {
                    capacity = 1;
                }
                var $header = $('#price-table-header');
                var $tbody = $('#price-table-body');
                
                if ($header.length === 0 || $tbody.length === 0) {
                    return;
                }
                
                $tbody.find('tr').each(function() {
                    var $row = $(this);
                    var duration = $row.data('duration');
                    if (!currentPriceData[duration]) {
                        currentPriceData[duration] = {};
                    }
                    $row.find('input[data-guest]').each(function() {
                        var guest = $(this).data('guest');
                        var val = $(this).val();
                        if (val) {
                            val = AppJs.normalizePriceForSubmit(val);
                            if (val) {
                                currentPriceData[duration][guest] = val;
                            }
                        }
                    });
                });
                
                $header.find('th:not(:first)').remove();
                
                if (capacity > 0) {
                    for (var i = 1; i <= capacity; i++) {
                        $header.append('<th>' + getGuestLabel(i) + '</th>');
                    }
                }
                
                $tbody.find('tr').each(function() {
                    var $row = $(this);
                    var duration = $row.data('duration');
                    
                    $row.find('td:first').css('width', '150px');
                    $row.find('td:not(:first)').remove();
                    
                    if (capacity > 0) {
                        for (var i = 1; i <= capacity; i++) {
                            var existingValue = '';
                            if (currentPriceData[duration] && currentPriceData[duration][i]) {
                                existingValue = currentPriceData[duration][i];
                            } else if (priceData && priceData.length > 0) {
                                for (var j = 0; j < priceData.length; j++) {
                                    if (priceData[j].duration == duration && priceData[j].guest == i) {
                                        existingValue = priceData[j].price;
                                        break;
                                    }
                                }
                            }
                            var displayValue = existingValue ? AppJs.formatPriceDisplay(existingValue.toString()) : '';
                            var inputName = 'price[' + duration + '][' + i + ']';
                            var $input = $('<input>')
                                .attr('type', 'text')
                                .attr('name', inputName)
                                .attr('data-duration', duration)
                                .attr('data-guest', i)
                                .addClass('form-control form-control-sm text-right price-input')
                                .attr('placeholder', '0')
                                .val(displayValue)
                                .css('width', '100%');
                            var $cell = $('<td>').append($input);
                            $row.append($cell);
                        }
                    }
                });
            }

            $(document).on('input change', '#capacity-input, input[name="capacity"]', function() {
                var $input = $(this);
                var value = parseInt($input.val()) || 0;
                if (value > 10) {
                    $input.val(10);
                }
                updatePriceTable();
                enforceOverCapacityMax();
            });

            // Over capacity: Adult + 6-12y + 2-5y + Infant must not exceed capacity (chặn tổng <= sức chứa tối đa)
            var overCapacityFields = ['over_capacity_adult', 'over_capacity_child_6_12', 'over_capacity_child_2_5', 'over_capacity_infant'];

            function getCapacityForOverCapacity() {
                var $cap = $('#capacity-input');
                if (!$cap.length) { $cap = $('#cabin-form').find('input[name="capacity"]'); }
                var cap = parseInt($cap.val(), 10) || 0;
                return cap <= 0 ? 1 : Math.min(10, cap);
            }

            function getOverCapacityValues() {
                var out = {};
                overCapacityFields.forEach(function(name) {
                    var v = parseInt($('#cabin-form').find('input[name="' + name + '"]').val(), 10);
                    out[name] = isNaN(v) || v < 0 ? 0 : v;
                });
                return out;
            }

            function enforceOverCapacityMax() {
                var capacity = getCapacityForOverCapacity();
                var vals = getOverCapacityValues();
                var total = vals.over_capacity_adult + vals.over_capacity_child_6_12 + vals.over_capacity_child_2_5 + vals.over_capacity_infant;
                if (total <= capacity) {
                    $('#cabin-form').find('.over-capacity-feedback').remove();
                    return;
                }
                var order = ['over_capacity_infant', 'over_capacity_child_2_5', 'over_capacity_child_6_12', 'over_capacity_adult'];
                var remaining = capacity;
                for (var i = order.length - 1; i >= 0; i--) {
                    var name = order[i];
                    var current = vals[name];
                    var allow = Math.min(current, remaining);
                    remaining -= allow;
                    var $field = $('#cabin-form').find('input[name="' + name + '"]');
                    if ($field.length && parseInt($field.val(), 10) !== allow) {
                        $field.val(allow);
                    }
                }
                showOverCapacityFeedback();
            }

            function capSingleOverCapacityField($input) {
                var name = $input.attr('name');
                if (overCapacityFields.indexOf(name) === -1) { return; }
                var capacity = getCapacityForOverCapacity();
                var vals = getOverCapacityValues();
                var otherSum = 0;
                overCapacityFields.forEach(function(n) { if (n !== name) { otherSum += vals[n]; } });
                var maxThis = Math.max(0, capacity - otherSum);
                var current = parseInt($input.val(), 10);
                if (isNaN(current) || current < 0) {
                    $input.val(0);
                    return;
                }
                if (current > maxThis) {
                    $input.val(maxThis);
                    showOverCapacityFeedback();
                } else {
                    $('#cabin-form').find('.over-capacity-feedback').remove();
                }
            }

            function showOverCapacityFeedback() {
                if ($('#cabin-form').find('.over-capacity-feedback').length) { return; }
                var capacity = getCapacityForOverCapacity();
                var msg = (langOverCapacityTotal || 'Tổng số khách không được vượt quá sức chứa tối đa (__CAP__).').replace('__CAP__', capacity);
                var $wrap = $('#cabin-form').find('input[name="over_capacity_infant"]').closest('.form-group').closest('.row');
                if ($wrap.length) {
                    $wrap.after('<div class="over-capacity-feedback text-danger small mt-1">' + $('<div>').text(msg).html() + '</div>');
                }
            }

            overCapacityFields.forEach(function(name) {
                var selector = 'input[name="' + name + '"]';
                $('#cabin-form').on('input change blur', selector, function() {
                    capSingleOverCapacityField($(this));
                });
                $('#cabin-form').on('paste', selector, function() {
                    var $input = $(this);
                    setTimeout(function() { capSingleOverCapacityField($input); }, 0);
                });
            });
            
            $(document).on('blur', '.price-input', function() {
                var $input = $(this);
                var duration = $input.data('duration');
                var guest = $input.data('guest');
                var val = $input.val();
                if (val) {
                    val = AppJs.normalizePriceForSubmit(val);
                    if (val) {
                        if (!currentPriceData[duration]) {
                            currentPriceData[duration] = {};
                        }
                        currentPriceData[duration][guest] = val;
                    }
                }
            });

            function repopulateFromOld() {
                var i;
                if (oldRoomTitles && oldRoomTitles.length > 0) {
                    $('#selected-rooms').empty();
                    for (i = 0; i < oldRoomTitles.length; i++) {
                        renderRoom(
                            typeof oldRoomTitles[i] === 'string' ? oldRoomTitles[i] : '',
                            (oldRoomDescriptions && oldRoomDescriptions[i] !== undefined) ? oldRoomDescriptions[i] : ''
                        );
                    }
                }
                if (oldAmenityIds && oldAmenityIds.length > 0 && listAmenityMap) {
                    $('#selected-amenities').empty();
                    for (i = 0; i < oldAmenityIds.length; i++) {
                        var id = oldAmenityIds[i];
                        var info = listAmenityMap[id];
                        if (info) {
                            renderExistingAmenity(id, info.name || '', info.icon || '');
                        }
                    }
                }
                if (oldAmenityName && oldAmenityName.length > 0) {
                    if (!oldAmenityIds || oldAmenityIds.length === 0) {
                        $('#selected-amenities').empty();
                    }
                    for (i = 0; i < oldAmenityName.length; i++) {
                        var n = oldAmenityName[i];
                        if (!n) { continue; }
                        renderNewAmenity(
                            n,
                            (oldAmenityDescription && oldAmenityDescription[i] !== undefined) ? oldAmenityDescription[i] : '',
                            (oldAmenityIcon && oldAmenityIcon[i] !== undefined) ? oldAmenityIcon[i] : ''
                        );
                    }
                }
                if (oldAudienceName && oldAudienceName.length > 0) {
                    $('#selected-audiences').empty();
                    for (i = 0; i < oldAudienceName.length; i++) {
                        var an = oldAudienceName[i];
                        if (!an) { continue; }
                        renderAudience(an, (oldAudienceIcon && oldAudienceIcon[i] !== undefined) ? oldAudienceIcon[i] : '');
                        if (oldAudienceIds && oldAudienceIds[i] !== undefined && oldAudienceIds[i]) {
                            var $pill = $('#selected-audiences .audience-pill').last();
                            $pill.attr('data-id', oldAudienceIds[i]);
                            $pill.append('<input type="hidden" name="audience_group_ids[]" value="' + oldAudienceIds[i] + '">');
                        }
                    }
                }
            }

            function syncPriceDataFromTable() {
                $('#price-table-body').find('tr').each(function() {
                    var $row = $(this);
                    var duration = $row.data('duration');
                    if (!currentPriceData[duration]) {
                        currentPriceData[duration] = {};
                    }
                    $row.find('input[data-guest]').each(function() {
                        var guest = $(this).data('guest');
                        var val = $(this).val();
                        if (val) {
                            val = (typeof AppJs !== 'undefined' && AppJs.normalizePriceForSubmit) ? AppJs.normalizePriceForSubmit(val) : val;
                            if (val) {
                                currentPriceData[duration][guest] = val;
                            }
                        }
                    });
                });
            }

            $(document).ready(function() {
                repopulateFromOld();
                syncPriceDataFromTable();
                enforceOverCapacityMax();
                AppJs.bindPriceInputs('#price-table', '#cabin-form');
            });
        })();
    </script>
@endsection
