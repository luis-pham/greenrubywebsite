@extends('backend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');
@endphp

@section('content')
    @include('backend::shared.message')
    <h1 class="h3 mb-4 text-center">{{ $title }}</h1>
    {{ Form::open(['route' => [Utilities::getRouteName('backend.article.update'), ['languageCode' => $languageCode, 'id' => $obj->id, 'lastUrl' => Request::get('lastUrl')]], 'id' => 'frm', 'enctype' => 'multipart/form-data']) }}
        <div class="row">
            <div class="col-lg-8">    
                <div class="card">
                    <div class="card-header">
                        <p class="h5 m-0">Thông tin chính</p>
                    </div>
                    <div class="card-body">
                        <div class="form-horizontal">
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">Chuyên mục <span class="text-danger">*</span></label>
                                <div class="col-md-9">
                                    <div class="select2">
                                        {{ Form::select('category_id', $listCategory, old('category_id', $obj->category_id), ['class' => 'form-control', 'placeholder' => 'Chọn', 'autocomplete' => 'off']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">Tiêu đề <span class="text-danger">*</span></label>
                                <div class="col-md-9">
                                    {{ Form::text('title', old('title', $obj->title), ['class' => 'form-control', 'placeholder' => 'Nhập tiêu đề...', 'maxlength' => 255, 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">Slug URL</label>
                                <div class="col-md-9">
                                    {{ Form::text('slug', old('slug', $obj->slug), ['class' => 'form-control', 'placeholder' => 'Tự sinh từ tiêu đề...', 'maxlength' => 255, 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">Tiêu đề phụ</label>
                                <div class="col-md-9">
                                    {{ Form::text('sub_title', old('sub_title', $obj->sub_title), ['class' => 'form-control', 'placeholder' => 'Nhập tiêu đề phụ...', 'maxlength' => 255, 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-12 col-form-label">Trích dẫn <span class="text-danger">*</span></label>
                                <div class="col-md-12">
                                    {{ Form::textarea('lead', old('lead', $obj->lead), ['rows'=> 5, 'class' => 'form-control', 'placeholder' => 'Nhập trích dẫn...', 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-12 col-form-label">Nội dung <span class="text-danger">*</span></label>
                                <div class="col-md-12">
                                    {{ Form::textarea('content', old('content', $obj->content), ['rows'=> 5, 'class' => 'form-control', 'placeholder' => 'Nhập nội dung...', 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">TG xuất bản <span class="text-danger">*</span></label>
                                <div class="col-md-9">
                                    {{ Form::text('publish_date', old('publish_date', Utilities::formatDisplayDateTime($obj->publish_date)), ['class' => 'form-control date-time-picker', 'placeholder' => 'Nhập ngày xuất bản..', 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">Nổi bật</label>
                                <div class="col-md-9 col-form-label">
                                    <label class="c-switch c-switch-label c-switch-success mb-0">
                                        {{ Form::checkbox('is_featured', true, old('is_featured', $obj->is_featured), ['class' => 'c-switch-input', 'autocomplete' => 'off']) }}
                                        <span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">Xuất bản</label>
                                <div class="col-md-9 col-form-label">
                                    <label class="c-switch c-switch-label c-switch-success mb-0">
                                        {{ Form::checkbox('is_published', true, old('is_published', $obj->is_published), ['class' => 'c-switch-input', 'autocomplete' => 'off']) }}
                                        <span class="c-switch-slider" data-checked="On" data-unchecked="Off"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <p class="h5 m-0">Ảnh bìa</p>
                    </div>
                    <div class="card-body">
                        {{ Form::hidden('image_link', old('image_link', $obj->image_link), ['class' => 'image-select', 'data-link-full' => Utilities::getFileLink($obj->image_link), 'data-file-manager-url' => route('backend.file.index', ['layout' => 'popup', 'type' => config('backend.fileType.image')])]) }}
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
                                {{ Form::text('seo_title', old('seo_title', $obj->seo_title), ['class' => 'form-control', 'placeholder' => 'Nhập tiêu đề...', 'maxlength' => 50, 'autocomplete' => 'off']) }}
                            </div>
                            <div class="form-group">
                                <label>Mô tả</label>
                                {{ Form::text('seo_description', old('seo_description', $obj->seo_description), ['class' => 'form-control', 'placeholder' => 'Nhập mô tả...', 'maxlength' => 255, 'autocomplete' => 'off']) }}
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
            <a href="{{ Utilities::getGoBackUrl(route(Utilities::getRouteName('backend.article.index'), ['languageCode' => $languageCode])) }}" class="btn btn-light btn-sm">
                <i class="fas fa-undo"></i> Quay lại
            </a>
        </div>
    </footer>
@endsection

@section('scripts')
    @include('backend::shared.text-editor-script')
    <script type="text/javascript">
        $(document).ready(function () {
            $('[name="lead"]').textEditor({
                menubar: false,
                toolbar: [
                    'bold italic underline strikethrough | backcolor forecolor | link unlink | removeformat | charmap | superscript subscript | code'
                ],
                contextmenu: 'cut copy paste',
                mobile: {
                    toolbar: [
                        'bold italic underline strikethrough | link unlink | removeformat'
                    ],
                }
            });
            $('[name="content"]').textEditor({ height: 450 });
            $('input[name="slug"]').genAlias({
                ctrlName: $('input[name="title"]')
            });
        });
    </script>
@endsection