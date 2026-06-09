@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')

    @php
        $languageCode = request()->route('languageCode');
        $routeParams = $languageCode ? ['languageCode' => $languageCode] : [];
        $breadcrumbItems = [
            ['label' => 'Quản lý đánh giá', 'url' => route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.testimonial.index'), $routeParams)],
            ['label' => 'Chi tiết đánh giá']
        ];
    @endphp

    <div class="page-header-row d-flex justify-content-between align-items-center mb-4">
        @include('backend::shared.breadcrumb', ['breadcrumbItems' => $breadcrumbItems])
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="m-0"><i class="fas fa-info-circle"></i> THÔNG TIN ĐÁNH GIÁ</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Họ và tên <span class="text-danger">*</span></label>
                        <div class="form-control-plaintext font-weight-bold">{{ $obj->fullname }}</div>
                    </div>
                    <div class="form-group">
                        <label>Vị trí/Chức vụ <span class="text-danger">*</span></label>
                        <div class="form-control-plaintext">{{ $obj->position ?? '-' }}</div>
                    </div>
                    <div class="form-group">
                        <label>Nội dung đánh giá <span class="text-danger">*</span></label>
                        <div class="form-control-plaintext">{!! nl2br(e($obj->content ?? '-')) !!}</div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="m-0"><i class="fas fa-image"></i> ẢNH ĐẠI DIỆN <span class="text-danger">*</span></h6>
                </div>
                <div class="card-body">
                    @if($obj->avatar)
                        <img src="{{ Utilities::getFileLink($obj->avatar) }}" alt="{{ $obj->fullname }}" class="img-fluid rounded" />
                    @else
                        <div class="text-muted">-</div>
                    @endif
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="m-0"><i class="fas fa-image"></i> ẢNH BÌA</h6>
                </div>
                <div class="card-body">
                    @if($obj->cover_link)
                        <img src="{{ Utilities::getFileLink($obj->cover_link) }}" alt="{{ $obj->fullname }}" class="img-fluid rounded" />
                    @else
                        <div class="text-muted">-</div>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="m-0"><i class="fas fa-cogs"></i> THAO TÁC</h6>
                </div>
                <div class="card-body">
               
                    <a href="{{ Utilities::getGoBackUrl(route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.testimonial.index'), $routeParams)) }}" class="btn btn-light btn-block btn-sm">
                        <i class="fas fa-undo"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
