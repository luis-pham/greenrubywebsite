@extends('backend::layouts.master')

@php
    $typeName = Route::current()->parameter('typeName');
    $languageCode = Route::current()->parameter('languageCode');
@endphp

@section('content')
    @include('backend::shared.message')
    <h1 class="h3 mb-4 text-center">{{ $title }}</h1>
    {{ Form::open(['route' => [Utilities::getRouteName('backend.group.store'), ['languageCode' => $languageCode, 'typeName' => $typeName, 'lastUrl' => Request::get('lastUrl')]], 'id' => 'frm']) }}
        <div class="row">
            <div class="col-lg-8">    
                <div class="card">
                    <div class="card-header">
                        <p class="h5 m-0">Thông tin chính</p>
                    </div>
                    <div class="card-body">
                        <div class="form-horizontal">
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">Tên <span class="text-danger">*</span></label>
                                <div class="col-md-8">
                                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Nhập tên...', 'maxlength' => 50, 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                            @if ($type == config('backend.groupType.expActivity'))
                                <div class="form-group row">
                                    <label class="col-md-4 col-form-label">Tab</label>
                                    <div class="col-md-8">
                                        {{ Form::select('tab', $listTab, null, ['class' => 'form-control', 'placeholder' => 'Chọn', 'autocomplete' => 'off']) }}
                                    </div>
                                </div>
                            @endif
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">Slug <span class="text-danger">*</span></label>
                                <div class="col-md-8">
                                    {{ Form::text('slug', null, ['class' => 'form-control', 'placeholder' => 'Nhập slug...', 'maxlength' => 50, 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">
                                    Category Key
                                    <small class="text-muted d-block">(multilingual mapping — không đổi theo ngôn ngữ)</small>
                                </label>
                                <div class="col-md-8">
                                    <select name="category_key" class="form-control">
                                        <option value="">— None —</option>
                                        <option value="dining" {{ old('category_key', '') == 'dining' ? 'selected' : '' }}>Dining & Social</option>
                                        <option value="pools" {{ old('category_key', '') == 'pools' ? 'selected' : '' }}>Pools & Outdoors</option>
                                        <option value="wellness" {{ old('category_key', '') == 'wellness' ? 'selected' : '' }}>Wellness & Activities</option>
                                        <option value="events" {{ old('category_key', '') == 'events' ? 'selected' : '' }}>Events</option>
                                    </select>
                                    <span class="help-block text-muted">
                                        Dùng để gom nhóm facility/activity trên trang cruise detail. Để trống nếu không cần hiển thị trong section Onboard Experience.
                                    </span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label">Mô tả</label>
                                <div class="col-md-8">
                                    {{ Form::textarea('description', null, ['rows'=> 5, 'class' => 'form-control', 'placeholder' => 'Nhập mô tả...', 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <p class="h5 m-0">Ảnh đại diện</p>
                    </div>
                    <div class="card-body">
                        {{ Form::hidden('image_link', null, ['class' => 'image-select', 'data-link-full' => Utilities::getFileLink(old('image_link')), 'data-file-manager-url' => route('backend.file.index', ['layout' => 'popup', 'type' => config('backend.fileType.image')])]) }}
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <p class="h5 m-0">Thông tin SEO</p>
                    </div>
                    <div class="card-body">
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label>Tiêu đề</label>
                                {{ Form::text('seo_title', null, ['class' => 'form-control', 'placeholder' => 'Nhập tiêu đề...', 'maxlength' => 65, 'autocomplete' => 'off']) }}
                            </div>
                            <div class="form-group">
                                <label>Mô tả</label>
                                {{ Form::text('seo_description', null, ['class' => 'form-control', 'placeholder' => 'Nhập mô tả...', 'maxlength' => 255, 'autocomplete' => 'off']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {{ Form::close() }}
@endsection

@section('footer')
    <footer class="c-footer c-footer-sticky pl-0 pr-0">
        <div class="container-fluid">
            <button type="button" class="btn btn-primary btn-sm" onclick="$('#frm').submit()">
                <i class="fas fa-save"></i> Lưu lại
            </button>
            <a href="{{ Utilities::getGoBackUrl(route(Utilities::getRouteName('backend.group.index'), ['languageCode' => $languageCode, 'typeName' => $typeName])) }}" class="btn btn-light btn-sm">
                <i class="fas fa-undo"></i> Quay lại
            </a>
        </div>
    </footer>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            $('input[name="slug"]').genAlias({
                ctrlName: $('input[name="name"]')
            });
        });
    </script>
@endsection