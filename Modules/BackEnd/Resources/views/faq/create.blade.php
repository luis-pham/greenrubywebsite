@extends('backend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');
@endphp

@section('content')
    @include('backend::shared.message')
    {{ Form::open(['route' => [Utilities::getRouteName('backend.faq.store'), ['languageCode' => $languageCode, 'lastUrl' => Request::get('lastUrl')]], 'id' => 'frm']) }}
        <div class="card">
            <div class="card-header">
                <h1 class="h5 m-0">{{ $title }}</h1>
            </div>
            <div class="card-body">
                <div class="form-horizontal">
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">Chuyên mục <span class="text-danger">*</span></label>
                        <div class="col-md-10">
                            <div class="select2">
                                {{ Form::select('group_id', $listGroup, null, ['class' => 'form-control', 'placeholder' => 'Chọn', 'autocomplete' => 'off']) }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">Câu hỏi <span class="text-danger">*</span></label>
                        <div class="col-md-10">
                            {{ Form::textarea('question', null, ['rows'=> 5, 'class' => 'form-control', 'placeholder' => 'Nhập câu hỏi...', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">Trả lời <span class="text-danger">*</span></label>
                        <div class="col-md-10">
                            {{ Form::textarea('answer', null, ['rows'=> 5, 'class' => 'form-control', 'placeholder' => 'Nhập trả lời...', 'autocomplete' => 'off']) }}
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
            <a href="{{ Utilities::getGoBackUrl(route('backend.faq.index')) }}" class="btn btn-light btn-sm">
                <i class="fas fa-undo"></i> Quay lại
            </a>
        </div>
    </footer>
@endsection

@section('scripts')
    @include('backend::shared.text-editor-script')
    <script type="text/javascript">
        $(document).ready(function () {
            $('[name="question"]').textEditor({ height: 300 });
            $('[name="answer"]').textEditor({ height: 400 });
        });
    </script>
@endsection