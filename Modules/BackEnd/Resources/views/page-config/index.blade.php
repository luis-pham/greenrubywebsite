@extends('backend::layouts.master')

@section('styles')
    <link href="{{ asset('/assets/backend/css/modules/config/index.css') }}" rel="stylesheet" />
@endsection

@php
    $languageCode = Route::current()->parameter('languageCode');
    $pageCode = Route::current()->parameter('pageCode');
    $listInputGalleryKey = [];
    $listInputSourceDataKey = [];
@endphp

@section('content')
    @include('backend::shared.message')
    <h1 class="h3 mb-4 text-center">{{ $title }}</h1>
    {{ Form::open(['route' => [Utilities::getRouteName('backend.page-config.update'), ['languageCode' => $languageCode, 'pageCode' => $pageCode]], 'id' => 'frm']) }}
        @for ($i = 0; $i < count($listSection); $i++)
            @php
                $dataConfig = array_key_exists($listSection[$i]->id, $listConfig)
                    ? $listConfig[$listSection[$i]->id]
                    : [];
            @endphp
            <div class="card">
                <div class="card-header">
                    <h1 class="h5 m-0">{{ $listSection[$i]->name }}</h1>
                </div>
                <div class="card-body">
                    <div class="form-horizontal">
                        @foreach ($dataConfig as $obj)
                            @if ($obj->type == config('backend.configInput.gallery'))
                                @php
                                    $listInputGalleryKey[] = $obj->key;
                                @endphp
                            @elseif ($obj->type == config('backend.configInput.sourceData'))
                                @php
                                    $listInputSourceDataKey[$obj->key] = SourceDataUtils::getSourceDataInfo($obj->list_value);
                                @endphp
                            @endif
                            @include('backend::config.shared.form-input', ['obj' => $obj])
                        @endforeach
                    </div>
                </div>
            </div>
        @endfor
    {{ Form::close() }}
    @include('backend::config.shared.modal-gallery-image.modal-select')
    @include('backend::config.shared.modal-gallery-image.modal-edit')
    @include('backend::source-data.modal-select', ['languageCode' => $languageCode])
@endsection

@section('footer')
    <footer class="c-footer c-footer-sticky pl-0 pr-0">
        <div class="container-fluid">
            <button type="button" class="btn btn-primary btn-sm" onclick="$('#frm').submit()">
                <i class="fas fa-save"></i> Lưu lại
            </button>
        </div>
    </footer>
@endsection

@section('scripts')
    @include('backend::shared.text-editor-script')
    <script src="{{ asset('/assets/backend/plugins/fancybox/jquery.fancybox.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/touchpunch/jquery.ui.touch-punch.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/mustache/mustache.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/mustache/jquery.mustache.js') }}"></script>
    <script src="{{ asset('/assets/backend/js/modules/config/index.js') }}"></script>
    <script src="{{ asset('/assets/backend/js/modules/config/source-data.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            @for ($i = 0; $i < count($listInputGalleryKey); $i++)
                @if ($i == 0)
                    let galleryImage = new GalleryImage('{{ $listInputGalleryKey[$i] }}');
                @else
                    galleryImage = new GalleryImage('{{ $listInputGalleryKey[$i] }}');
                @endif
                galleryImage.init();
            @endfor
            @foreach ($listInputSourceDataKey as $key => $value)
                @if ($i == 0)
                    let sourceDataInfo = {
                        title: '{{ $value['title'] }}',
                        url: {
                            selectData: '{{ $value['url']['selectData'] }}',
                            getData: '{{ $value['url']['getData'] }}'
                        }
                    };
                    let sourceData = new SourceData('{{ $key }}', sourceDataInfo.title, sourceDataInfo.url);
                @else
                    sourceDataInfo = {
                        title: '{{ $value['title'] }}',
                        url: {
                            selectData: '{{ $value['url']['selectData'] }}',
                            getData: '{{ $value['url']['getData'] }}'
                        }
                    };
                    sourceData = new SourceData('{{ $key }}', sourceDataInfo.title, sourceDataInfo.url);
                @endif
                sourceData.init();
            @endforeach
        });
    </script>
@endsection