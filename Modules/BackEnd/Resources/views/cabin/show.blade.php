@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    
    @php
        $breadcrumbItems = [
            ['label' => __('backend::cabin.breadcrumb_manage'), 'url' => route('backend.cabin.index')],
            ['label' => __('backend::cabin.page_show')]
        ];
    @endphp
    
    <div class="page-header-row d-flex justify-content-between align-items-center mb-4">
        @include('backend::shared.breadcrumb', ['breadcrumbItems' => $breadcrumbItems])
    </div>
    
    <div id="cabin-show-card">
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
                                <div class="form-control-plaintext">{{ $obj->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('backend::cabin.label_cabin_type') }} <span class="text-danger">*</span></label>
                                <div class="form-control-plaintext">{{ $listCabinType[$obj->group_id] ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{ __('backend::cabin.label_view') }} <span class="text-danger">*</span></label>
                        <div class="form-control-plaintext">{{ $obj->view }}</div>
                    </div>
                    <div class="form-group">
                        <label>{{ __('backend::cabin.label_summary') }} <span class="text-danger">*</span></label>
                        <div class="form-control-plaintext">{{ $obj->summary }}</div>
                    </div>
                    <div class="form-group mb-0">
                        <label>{{ __('backend::cabin.label_content') }}</label>
                        <div class="form-control-plaintext">{!! $obj->content !!}</div>
                    </div>
                </div>
            </div>

            @php
                $capacityForTable = max(1, min(10, (int) ($obj->capacity ?? 1)));
                $priceGrid = [];
                foreach (isset($prices) ? $prices : [] as $row) {
                    $priceGrid[$row->duration][$row->guest] = $row->price;
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
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th class="th-duration">{{ __('backend::cabin.col_duration') }}</th>
                                    @for ($g = 1; $g <= $capacityForTable; $g++)
                                        <th>{{ $guestLabelsForTable[$g] ?? __('backend::cabin.guest_count', ['n' => $g]) }}</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(\Modules\BackEnd\Helpers\CruiseUtils::getListDuration() as $duration => $label)
                                <tr>
                                    <td class="td-duration">{{ $label }}</td>
                                    @for ($g = 1; $g <= $capacityForTable; $g++)
                                        @php
                                            $val = $priceGrid[$duration][$g] ?? '';
                                            if ($val !== '') {
                                                $val = number_format((float) str_replace(',', '.', $val), 0, ',', '.') . ' ₫';
                                            } else {
                                                $val = '-';
                                            }
                                        @endphp
                                        <td class="text-right">{{ $val }}</td>
                                    @endfor
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="m-0"><i class="fas fa-door-open"></i> {{ __('backend::cabin.section_rooms') }}</h6>
                        </div>
                        <div class="card-body">
                            <div id="selected-rooms">
                                @if(isset($rooms) && count($rooms) > 0)
                                    @foreach ($rooms as $room)
                                        <div class="room-pill d-flex align-items-start justify-content-between">
                                            <div class="flex-grow-1 mr-2">
                                                <strong class="room-title-display{{ $room->title ? '' : ' placeholder' }}" data-placeholder="{{ __('backend::cabin.placeholder_room_name') }}">{{ $room->title ?: __('backend::cabin.placeholder_room_name') }}</strong>
                                                <span class="room-desc-display{{ $room->description ? '' : ' placeholder' }}" data-placeholder="{{ __('backend::cabin.placeholder_room_desc') }}">{{ $room->description ?: __('backend::cabin.placeholder_room_desc') }}</span>
                                            </div>
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
                            <h6 class="m-0 text-uppercase"><i class="fas fa-star"></i> {{ __('backend::cabin.section_amenities') }}</h6>
                        </div>
                        <div class="card-body">
                            <div id="selected-amenities">
                                @if(isset($listAmenity) && isset($selectedAmenityIds) && count($selectedAmenityIds) > 0)
                                    @foreach ($listAmenity as $amenity)
                                        @if (in_array($amenity->id, $selectedAmenityIds))
                                            @php
                                                $iconUrl = $amenity->icon ? Utilities::getFileLink($amenity->icon) : null;
                                            @endphp
                                            <div class="amenity-pill" data-id="{{ $amenity->id }}">
                                                <div class="d-flex align-items-center">
                                                    @if ($iconUrl)
                                                        <img src="{{ $iconUrl }}" alt="{{ html_entity_decode($amenity->name, ENT_QUOTES, 'UTF-8') }}" class="cabin-pill-icon mr-2" onerror="this.style.display='none'" />
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
                        <div class="form-control-plaintext">{{ $obj->cruise_name ?? '-' }}</div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6 col-cabin-field">
                            <div class="form-group">
                                <label>{{ __('backend::cabin.label_capacity_max') }} <span class="text-danger">*</span></label>
                                <div class="form-control-plaintext">{{ $obj->capacity ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-cabin-field">
                            <div class="form-group">
                                <label>{{ __('backend::cabin.label_area_m2') }} <span class="text-danger">*</span></label>
                                <div class="form-control-plaintext">{{ $obj->area ? $obj->area . ' m²' : '-' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ __('backend::cabin.label_over_capacity_title') }}</label>
                        <div class="row">
                            <div class="col-6 col-md-3 col-cabin-field">
                                <div class="form-group mb-2 mb-md-0">
                                    <label class="small d-block text-uppercase mb-1">{{ __('backend::cabin.label_over_capacity_adult') }}</label>
                                    <div class="form-control-plaintext">{{ $obj->over_capacity_adult ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-cabin-field">
                                <div class="form-group mb-2 mb-md-0">
                                    <label class="small d-block text-uppercase mb-1">{{ __('backend::cabin.label_over_capacity_child_6_12') }}</label>
                                    <div class="form-control-plaintext">{{ $obj->over_capacity_child_6_12 ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-cabin-field">
                                <div class="form-group mb-2 mb-md-0">
                                    <label class="small d-block text-uppercase mb-1">{{ __('backend::cabin.label_over_capacity_child_2_5') }}</label>
                                    <div class="form-control-plaintext">{{ $obj->over_capacity_child_2_5 ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-cabin-field">
                                <div class="form-group mb-0">
                                    <label class="small d-block text-uppercase mb-1">{{ __('backend::cabin.label_over_capacity_infant') }}</label>
                                    <div class="form-control-plaintext">{{ $obj->over_capacity_infant ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label>{{ __('backend::cabin.label_discount') }}</label>
                        <div class="form-control-plaintext">{{ $obj->discount_percent ? $obj->discount_percent . '%' : '0%' }}</div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="m-0"><i class="fas fa-image"></i> {{ __('backend::cabin.section_avatar') }}</h6>
                </div>
                <div class="card-body">
                    @if($obj->image_link)
                        <img src="{{ Utilities::getFileLink($obj->image_link) }}" alt="{{ $obj->name }}" class="img-fluid" />
                    @else
                        <div class="text-muted">-</div>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="m-0"><i class="fas fa-images"></i> {{ __('backend::cabin.section_gallery') }}</h6>
                </div>
                <div class="card-body">
                    @if(isset($cabinGallery) && count($cabinGallery) > 0)
                        <div class="row">
                            @foreach ($cabinGallery as $img)
                                @php
                                    $img = is_array($img) ? (object) $img : $img;
                                    $thumbnail = property_exists($img, 'thumbnail') ? $img->thumbnail : null;
                                    $imgLink = property_exists($img, 'link') ? $img->link : '';
                                    $thumbnailFull = Utilities::getFileLink(!$thumbnail ? $imgLink : $thumbnail);
                                    $imgTitle = property_exists($img, 'title') ? $img->title : (property_exists($img, 'name') ? $img->name : '');
                                @endphp
                                <div class="col-4 col-lg-3 mb-3">
                                    <div class="box-dragdrop position-relative">
                                        <div class="image-wrapper position-relative">
                                            <a href="{{ Utilities::getFileLink($imgLink) }}" data-fancybox="gallery-cabin-show">
                                                <img src="{{ $thumbnailFull }}" alt="{{ $imgTitle }}" class="position-absolute w-100 h-100" />
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted">-</div>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="m-0"><i class="fas fa-users"></i> {{ __('backend::cabin.section_audience') }}</h6>
                </div>
                <div class="card-body">
                    <div id="selected-audiences">
                        @if(isset($audiences) && count($audiences) > 0)
                            @foreach ($audiences as $audience)
                                <div class="audience-pill" data-id="{{ $audience->id }}">
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
    </div>
@endsection

@section('styles')
    <link href="{{ asset('/assets/backend/css/modules/cabin/index.css') }}" rel="stylesheet">
    <link href="{{ asset('/assets/backend/css/modules/config/index.css') }}" rel="stylesheet">
@endsection

@section('scripts')
    <script src="{{ asset('/assets/backend/plugins/fancybox/jquery.fancybox.min.js') }}"></script>
@endsection
