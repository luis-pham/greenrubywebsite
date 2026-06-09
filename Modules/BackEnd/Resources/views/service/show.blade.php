@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    
    @php
        $languageCode = request()->route('languageCode');
        $routeParams = $languageCode ? ['languageCode' => $languageCode] : [];
        $breadcrumbItems = [
            ['label' => 'Quản lý dịch vụ', 'url' => route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.service.index'), $routeParams)],
            ['label' => 'Chi tiết dịch vụ']
        ];
    @endphp
    
    <div class="page-header-row d-flex justify-content-between align-items-center mb-4">
        @include('backend::shared.breadcrumb', ['breadcrumbItems' => $breadcrumbItems])
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="m-0"><i class="fas fa-info-circle"></i> THÔNG TIN DỊCH VỤ</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Tên dịch vụ <span class="text-danger">*</span></label>
                        <div class="form-control-plaintext font-weight-bold">{{ $obj->name }}</div>
                    </div>
                    <div class="form-group">
                        <label>Nhóm dịch vụ</label>
                        <div class="form-control-plaintext">{{ $obj->group_name ?? '-' }}</div>
                    </div>
                    <div class="form-group">
                        <label>Mô tả</label>
                        <div class="form-control-plaintext">{{ $obj->description ?? '-' }}</div>
                    </div>
                    <div class="form-group">
                        <label>Loại hình</label>
                        <div class="form-control-plaintext">
                            @if($obj->type == 1)
                                <span class="badge badge-primary">Dịch vụ bao gồm</span>
                            @else
                                <span class="badge badge-secondary">Dịch vụ không bao gồm</span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label>Giá niêm yết</label>
                        <div class="form-control-plaintext">
                            @if($obj->type == 1)
                                <div class="alert alert-success mb-0 d-inline-flex align-items-center">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <span>Miễn phí</span>
                                </div>
                            @else
                                {{ Utilities::formatDisplayCurrency($obj->price) }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

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
                                            <a href="{{ $fullLink }}" data-fancybox="service-gallery">
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
                    <h6 class="m-0"><i class="fas fa-cogs"></i> THAO TÁC</h6>
                </div>
                <div class="card-body">
                    @can('service-create')
                        <a href="{{ Utilities::getUrlWithGoBack(route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.service.create'), $routeParams), Request::get('lastUrl')) }}" class="btn btn-primary btn-block btn-sm mb-2">
                            <i class="fas fa-file-alt"></i> Thêm
                        </a>
                    @endcan
                    @can('service-update')
                        <a href="{{ Utilities::getUrlWithGoBack(route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.service.edit'), array_merge($routeParams, ['id' => $obj->id])), Request::get('lastUrl')) }}" class="btn btn-info btn-block btn-sm mb-2">
                            <i class="fas fa-pencil-alt"></i> Sửa
                        </a>
                    @endcan
                    @can('service-delete')
                        <a href="#" class="btn btn-danger btn-block btn-sm mb-2 btn-delete-one" data-id="{{ $obj->id }}" data-ajax-url="{{ route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.service.destroy'), $routeParams) }}" data-ajax-url-go-back="{{ Utilities::getGoBackUrl(route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.service.index'), $routeParams), Request::get('lastUrl')) }}">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </a>
                    @endcan
                    <a href="{{ Utilities::getGoBackUrl(route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.service.index'), $routeParams)) }}" class="btn btn-light btn-block btn-sm">
                        <i class="fas fa-undo"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection