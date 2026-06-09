@php
    $isPopup = Request::get('layout') == 'popup';
    $isMultiSelect = Request::get('isMultiSelect') == 'true';
@endphp

@extends($isPopup ? 'backend::layouts.popup' : 'backend::layouts.master')

@section('styles')
    <link href="{{ asset('/assets/backend/css/modules/file/index.css?v=1.0.0') }}" rel="stylesheet" />
@endsection

@section('content')
    @include('backend::shared.message')
    <div class="card {{ $isPopup ? 'mb-0' : '' }}">
        @if (!$isPopup)
            <div class="card-header">
                <h1 class="h4 m-0">{{ $title }}</h1>
            </div>
        @endif
        <div class="card-body">
            {{ Form::open(['method' => 'get', 'class' => 'form-search mb-2']) }}
                @if ($isPopup)
                    {{ Form::hidden('layout', 'popup') }}
                    {{ Form::hidden('type', Request::get('type')) }}
                    {{ Form::hidden('callback', Request::get('callback')) }}
                @endif
                {{ Form::hidden('exclude_id', Request::get('exclude_id')) }}
                <div class="form-row">
                    <div class="form-group {{ $isPopup ? 'col-md-7' : 'col-md-4' }}">
                        <label>Từ khóa</label>
                        {{ Form::text('keyword', Request::get('keyword'), ['class' => 'form-control', 'placeholder' => 'Nhập từ khóa...', 'autocomplete' => 'off']) }}
                    </div>
                    @if (!$isPopup)
                        <div class="form-group col-md-3">
                            <label>Loại</label>
                            {{ Form::select('type', $listFileType, Request::get('type'), ['class' => 'form-control', 'placeholder' => 'Tất cả' , 'autocomplete' => 'off']) }}
                        </div>
                    @endif
                    <div class="form-group col-md-2">
                        <label>Từ ngày</label>
                        {{ Form::text('from_date', Request::get('from_date'), ['class' => 'form-control date-picker', 'placeholder' => 'Nhập từ ngày...', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-2">
                        <label>Đến ngày</label>
                        {{ Form::text('to_date', Request::get('to_date'), ['class' => 'form-control date-picker', 'placeholder' => 'Nhập đến ngày...', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-1">
                        <label>&nbsp;</label>
                        {{ Form::button('<i class="fas fa-search"></i>', ['type' => 'submit', 'class' => 'btn btn-success btn-block']) }}
                    </div>
                </div>
            {{ Form::close() }}
            @canany(['file-create', 'file-delete'])
                <div class="mb-3">
                    @can('file-create')
                        <a href="#" id="btn-upload" class="btn btn-primary btn-sm">
                            <i class="fas fa-upload"></i> Tải lên
                        </a>
                    @endcan
                    @if ($isPopup && $isMultiSelect)
                        <button type="button" class="btn-select-multi btn btn-info btn-sm">
                            <i class="fas fa-check"></i> Chọn
                        </button>
                    @endif
                </div>
            @endcanany

            <div class="row mb-4 list-file">
                @for ($i = 0; $i < count($list); $i++)
                    @php
                        $list[$i]->link_full = Utilities::getFileLink($list[$i]->link);
                        $list[$i]->thumbnail_full = $list[$i]->thumbnail ? Utilities::getFileLink($list[$i]->thumbnail) : null;
                        $list[$i]->type = Utilities::getFileTypeByExtension($list[$i]->extension);
                        $list[$i]->size_name = Utilities::formatDisplayFileSize($list[$i]->size, 'MB', true);
                        $list[$i]->type_name = Utilities::getFileTypeNameByExtension($list[$i]->extension);
                    @endphp
                    <div class="col-6 col-md-3 col-lg-2">
                        <div class="item-wrapper position-relative">
                            @if ($isPopup && $isMultiSelect)
                                <div class="position-absolute custom-control custom-checkbox">
                                    <input type="checkbox" id="chk-item-{{ $list[$i]->id }}" class="chk-item custom-control-input" value="{{ $list[$i]->id }}" autocomplete="off" />
                                    <label class="custom-control-label" for="chk-item-{{ $list[$i]->id }}"></label>
                                </div>
                            @endif
                            <a href="javascript:void(0)" class="item d-block position-relative" data-obj="{{ json_encode($list[$i]->toArray(), JSON_UNESCAPED_UNICODE) }}">
                                <div class="image-wrapper position-relative">
                                    @if ($list[$i]->type == config('backend.fileType.image'))
                                        <img src="{{ $list[$i]->link_full }}" alt="{{ $list[$i]->name }}" class="position-absolute w-100 h-100" />
                                    @elseif ($list[$i]->type == config('backend.fileType.audio'))
                                        <div class="icon position-absolute w-100 h-100">
                                            <i class="fas fa-file-audio"></i>
                                        </div>
                                    @elseif ($list[$i]->type == config('backend.fileType.video'))
                                        @if ($list[$i]->thumbnail_full)
                                            <img src="{{ $list[$i]->thumbnail_full }}" alt="{{ $list[$i]->name }}" class="position-absolute w-100 h-100" />
                                        @else
                                            <div class="icon position-absolute w-100 h-100">
                                                <i class="fas fa-file-video"></i>
                                            </div>
                                        @endif
                                    @else
                                        <div class="icon position-absolute w-100 h-100">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="name position-absolute w-100 text-center">
                                    <span class="give-ellipsis after-2-lines">{{ $list[$i]->name }}</span>
                                </div>
                            </a>
                        </div>
                    </div>
                @endfor
            </div>

            {!! $list->appends(Request::all())->links('backend::shared.pagination') !!}
        </div>
    </div>

    <div class="modal fade" id="modal-upload" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-primary modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="h4 mb-0 modal-title">Tải lên</h2>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="dropzone" id="frm-upload">
                        <div class="dz-message needsclick">
                            <button type="button" class="dz-button mb-2"><span class="h4 mb-0 font-weight-normal">Thả file hoặc Bấm vào đây để thực hiện Tải lên.</span></button>
                            <p class="mb-0">Dung lượng tối đa: <strong>{{ Utilities::getUploadMaxFileSize('MB', true) }}</strong></p>
                            <p class="mb-0">Định dạng hỗ trợ: {{ implode(', ', Utilities::getFileTypeAllowUpload()) }}</p>
                        </div>
                    </div>   
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-show" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-primary modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="h4 mb-0 modal-title">Chi tiết file</h2>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-2 text-center file-type-image" style="display: none">
                                <a href="#" data-fancybox="gallery">
                                    <img class="img-fluid" />
                                </a>
                            </div>
                            <div class="mb-2 file-type-audio" style="display: none">
                                <div class="image-wrapper image-16-9 mb-2">
                                    <div class="icon border-secondary">
                                        <i class="fas fa-file-audio"></i>
                                    </div>
                                </div>
                                <audio class="w-100" controls>
                                    Your browser does not support the audio element.
                                </audio>
                            </div>
                            <div class="mb-2 file-type-video" style="display: none">
                                <video class="w-100" controls>
                                    Your browser does not support the video element.
                                </video>
                            </div>
                            <div class="mb-2 file-type-other" style="display: none">
                                <div class="image-wrapper image-16-9">
                                    <div class="icon border-secondary">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-horizontal">
                                <dl class="row">
                                    <dt class="col-md-12">Tên</dt>
                                    <dd class="col-md-12 lbl-name"></dd>
                                </dl>
                                <dl class="row">
                                    <dt class="col-md-12">Loại</dt>
                                    <dd class="col-md-12 lbl-type"></dd>
                                </dl>
                                <dl class="row">
                                    <dt class="col-md-12">Đường dẫn</dt>
                                    <dd class="col-md-12 lbl-link"><a href="#" target="_blank"></a></dd>
                                </dl>
                                <dl class="row">
                                    <dt class="col-md-12">Kích thước</dt>
                                    <dd class="col-md-12 lbl-size"></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-start">
                    @can('file-delete')
                        <a href="#" class="btn btn-danger btn-sm btn-delete-one" data-ajax-url="{{ route('backend.file.destroy') }}">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </a>
                    @endcan
                    <button class="btn btn-light btn-sm" type="button" data-dismiss="modal">
                        <i class="fas fa-undo"></i> Thoát
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        const fileUrl = {
            store: '{{ route('backend.file.store') }}'
        };
        const fileConfig = {
            maxFileUpload: {{ ini_get('max_file_uploads') }},
            maxFileSize: {{ Utilities::getUploadMaxFileSize('MB') }},
            fileAllowUpload: '{{ implode(',', preg_filter('/^/', '.', Utilities::getFileTypeAllowUpload())) }}'
        };
        const fileType = {!! json_encode(config('backend.fileType'), JSON_UNESCAPED_UNICODE) !!};
    </script>
    <script src="{{ asset('/assets/backend/plugins/fancybox/jquery.fancybox.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/js/modules/file/index.js?v=1.0.0') }}"></script>
    @if ($isPopup)
        <script src="{{ asset('/assets/backend/js/modules/file/index-popup.js?v=1.0.0') }}"></script>
    @else
        <script src="{{ asset('/assets/backend/js/modules/file/index-master.js?v=1.0.0') }}"></script>
    @endif
@endsection