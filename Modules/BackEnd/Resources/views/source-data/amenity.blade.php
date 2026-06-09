@extends('backend::layouts.popup')

@php
    $languageCode = Route::current()->parameter('languageCode');
@endphp

@section('content')
    <div class="card mb-0">
        <div class="card-body">
            {{ Form::open(['method' => 'get', 'class' => 'form-search mb-2']) }}
                {{ Form::hidden('exclude_id', Request::get('exclude_id')) }}
                {{ Form::hidden('callback', Request::get('callback')) }}
                <div class="form-row">
                    <div class="form-group col-md-10">
                        <label>Từ khóa</label>
                        {{ Form::text('keyword', Request::get('keyword'), ['class' => 'form-control', 'placeholder' => 'Nhập từ khóa...', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-2">
                        <label>&nbsp;</label>
                        {{ Form::button('<i class="fas fa-search"></i> Tìm kiếm', ['type' => 'submit', 'class' => 'btn btn-success btn-block']) }}
                    </div>
                </div>
            {{ Form::close() }}
            <div class="table-responsive-sm">
                <table class="table table-striped table-data">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>Amenity</th>
                            <th class="text-center" style="width: 120px;">Ch.năng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < count($list); $i++)
                            @php
                                $dataObj = SourceDataUtils::bindSourceDataAmenityDetail($list[$i], $languageCode);
                            @endphp
                            <tr data-obj="{{ json_encode($dataObj, JSON_UNESCAPED_UNICODE) }}">
                                <td class="text-center">{{ $i + $list->firstItem() }}</td>
                                <td>
                                    @if (\Auth::user()->can('amenity-read'))
                                        <div class="media">
                                            @if ($list[$i]->icon)
                                                <div class="image-wrapper position-relative mr-3">
                                                    <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.amenity.show'), ['languageCode' => $languageCode, 'id' => $list[$i]->id])) }}" target="_blank">
                                                        <img src="{{ Utilities::getFileLink($list[$i]->icon) }}" alt="{{ $list[$i]->name }}" class="position-absolute w-100 h-100">
                                                    </a>
                                                </div>
                                            @endif
                                            <div class="media-body">
                                                <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.amenity.show'), ['languageCode' => $languageCode, 'id' => $list[$i]->id])) }}" target="_blank">
                                                    {{ $list[$i]->name }}
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        <div class="media">
                                            @if ($list[$i]->icon)
                                                <div class="image-wrapper position-relative mr-3">
                                                    <img src="{{ Utilities::getFileLink($list[$i]->icon) }}" alt="{{ $list[$i]->name }}" class="position-absolute w-100 h-100">
                                                </div>
                                            @endif
                                            <div class="media-body">
                                                {{ $list[$i]->name }}
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="#" class="btn-select btn btn-primary btn-sm" title="Chọn">
                                        <i class="far fa-plus"></i>
                                    </a>
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            
            {!! $list->appends(Request::all())->links('backend::shared.pagination') !!}
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('/assets/backend/js/modules/source-data/common.js') }}"></script>
@endsection