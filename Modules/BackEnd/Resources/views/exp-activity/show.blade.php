@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    
    @php
        $languageCode = request()->route('languageCode');
        $routeParams = $languageCode ? ['languageCode' => $languageCode] : [];
        $breadcrumbItems = [
            ['label' => 'Quản lý hoạt động', 'url' => route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.exp-activity.index'), $routeParams)],
            ['label' => 'Chi tiết hoạt động']
        ];
    @endphp
    
    <div class="page-header-row d-flex justify-content-between align-items-center mb-4">
        @include('backend::shared.breadcrumb', ['breadcrumbItems' => $breadcrumbItems])
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="m-0"><i class="fas fa-info-circle"></i> THÔNG TIN HOẠT ĐỘNG</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Tên hoạt động <span class="text-danger">*</span></label>
                        <div class="form-control-plaintext font-weight-bold">{{ $obj->name }}</div>
                    </div>
                    <div class="form-group">
                        <label>Nhóm hoạt động</label>
                        <div class="form-control-plaintext">{{ $obj->group_name ?? '-' }}</div>
                    </div>
                    <div class="form-group">
                        <label>Mô tả ngắn</label>
                        <div class="form-control-plaintext">{{ $obj->summary ?? '-' }}</div>
                    </div>
                    @if($obj->content)
                        <div class="form-group mb-0">
                            <label>Nội dung chi tiết</label>
                            <div class="form-control-plaintext">{!! $obj->content !!}</div>
                        </div>
                    @endif
                </div>
            </div>

            @if($obj->note)
                @php
                    $notes = json_decode($obj->note, true);
                    if (!is_array($notes)) {
                        $notes = $obj->note ? [$obj->note] : [];
                    }
                @endphp
                @if(count($notes) > 0)
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="m-0"><i class="fas fa-sticky-note"></i> LƯU Ý THAM GIA</h6>
                        </div>
                        <div class="card-body">
                            <ul class="mb-0">
                                @foreach($notes as $noteText)
                                    <li>{{ $noteText }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            @endif

            @php
                $suitableAudiencesList = isset($suitableAudiences) ? $suitableAudiences : [];
            @endphp
            @if(count($suitableAudiencesList) > 0)
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-users"></i> ĐỐI TƯỢNG PHÙ HỢP</h6>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            @foreach($suitableAudiencesList as $audienceId => $audienceName)
                                <li>{{ $audienceName }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @php
                $galleryImagesList = isset($galleryImages) ? $galleryImages : [];
            @endphp
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-images"></i> THƯ VIỆN ẢNH</h6>
                    </div>
                    <div class="card-body">
                    @if(count($galleryImagesList) > 0)
                        <div class="row">
                            @foreach($galleryImagesList as $img)
                                @php
                                    $thumbnail = property_exists($img, 'thumbnail') ? $img->thumbnail : null;
                                    $thumbnailFull = Utilities::getFileLink(!$thumbnail ? $img->link : $thumbnail);
                                    $fullLink = Utilities::getFileLink($img->link);
                                @endphp
                                <div class="col-6 col-md-4 mb-3">
                                    <div class="box-dragdrop position-relative">
                                        <div class="image-wrapper position-relative">
                                            <a href="{{ $fullLink }}" data-fancybox="exp-activity-gallery">
                                                <img src="{{ $thumbnailFull }}" alt="{{ $img->title ?? $obj->name }}" class="position-absolute w-100 h-100 rounded" />
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    </div>
                </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="m-0"><i class="fas fa-clock"></i> THỜI GIAN</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Thời lượng</label>
                        <div class="form-control-plaintext">{{ $obj->duration ? $obj->duration . ' phút' : '-' }}</div>
                    </div>
                    <div class="form-group mb-0">
                        <label>Thời gian diễn ra</label>
                        <div class="form-control-plaintext">
                            @if($obj->start_time && $obj->end_time)
                                {{ Utilities::formatDisplayTime($obj->start_time) }} - {{ Utilities::formatDisplayTime($obj->end_time) }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>
            </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-image"></i> ẢNH ĐẠI DIỆN</h6>
                    </div>
                    <div class="card-body">
                    @if($obj->image_link)
                        <img src="{{ Utilities::getFileLink($obj->image_link) }}" alt="{{ $obj->name }}" class="img-fluid rounded" />
                    @endif
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-image"></i> ẢNH BÌA</h6>
                    </div>
                    <div class="card-body">
                    @if($obj->cover_link)
                        <img src="{{ Utilities::getFileLink($obj->cover_link) }}" alt="{{ $obj->name }}" class="img-fluid rounded" />
                    @endif
                    </div>
                </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="m-0"><i class="fas fa-cogs"></i> THAO TÁC</h6>
                </div>

                <div class="card-body">
                    @can('exp-activity-create')
                        <a href="{{ Utilities::getUrlWithGoBack(route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.exp-activity.create'), $routeParams), Request::get('lastUrl')) }}" class="btn btn-primary btn-block btn-sm mb-2">
                            <i class="fas fa-file-alt"></i> Thêm mới
                        </a>
                    @endcan
                    @can('exp-activity-update')
                        <a href="{{ Utilities::getUrlWithGoBack(route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.exp-activity.edit'), array_merge($routeParams, ['id' => $obj->id])), Request::get('lastUrl')) }}" class="btn btn-info btn-block btn-sm mb-2">
                            <i class="fas fa-pencil-alt"></i> Chỉnh sửa
                        </a>
                    @endcan
                    @can('exp-activity-delete')
                        <a href="#" class="btn btn-danger btn-block btn-sm mb-2 btn-delete-one" data-id="{{ $obj->id }}" data-ajax-url="{{ route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.exp-activity.destroy'), $routeParams) }}" data-ajax-url-go-back="{{ Utilities::getGoBackUrl(route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.exp-activity.index'), $routeParams), Request::get('lastUrl')) }}">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </a>
                    @endcan
                    <a href="{{ Utilities::getGoBackUrl(route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.exp-activity.index'), $routeParams)) }}" class="btn btn-light btn-block btn-sm">
                        <i class="fas fa-undo"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection