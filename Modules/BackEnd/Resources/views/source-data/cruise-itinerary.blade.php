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
                    <div class="form-group col-md-8">
                        <label>Từ khóa</label>
                        {{ Form::text('keyword', Request::get('keyword'), ['class' => 'form-control', 'placeholder' => 'Nhập từ khóa...', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-3">
                        <label>Thời lượng</label>
                        <div class="select2">
                            {{ Form::select('duration', $listDuration, Request::get('duration'), ['class' => 'form-control', 'placeholder' => 'Tất cả', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="form-group col-md-1">
                        <label>&nbsp;</label>
                        {{ Form::button('<i class="fas fa-search"></i>', ['type' => 'submit', 'class' => 'btn btn-success btn-block']) }}
                    </div>
                </div>
            {{ Form::close() }}
            <div class="table-responsive-sm">
                <table class="table table-striped table-data">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>Hành trình</th>
                            <th class="text-center" style="width: 120px;">Du thuyền</th>
                            <th class="text-center" style="width: 120px;">Thời lượng</th>
                            <th class="text-center" style="width: 120px;">Địa danh</th>
                            <th class="text-center" style="width: 120px;">Ch.năng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < count($list); $i++)
                            @php
                                $dataObj = SourceDataUtils::bindSourceDataCruiseItineraryDetail($list[$i]);
                            @endphp
                            <tr data-obj="{{ json_encode($dataObj, JSON_UNESCAPED_UNICODE) }}">
                                <td class="text-center">{{ $i + $list->firstItem() }}</td>
                                <td>
                                    @if (\Auth::user()->can('itinerary-read'))
                                        <div class="media">
                                            @if ($list[$i]->image_link)
                                                <div class="image-wrapper image-16-9 position-relative mr-3">
                                                    {{-- <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.itinerary.show'), ['languageCode' => $languageCode, 'id' => $list[$i]->id])) }}" target="_blank"> --}}
                                                        <img src="{{ Utilities::getFileLink($list[$i]->image_link) }}" alt="{{ $list[$i]->name }}" class="position-absolute w-100 h-100">
                                                    {{-- </a> --}}
                                                </div>
                                            @endif
                                            <div class="media-body">
                                                {{-- <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.itinerary.show'), ['languageCode' => $languageCode, 'id' => $list[$i]->id])) }}" target="_blank"> --}}
                                                    {{ $list[$i]->name }}
                                                {{-- </a> --}}
                                            </div>
                                        </div>
                                    @else
                                        <div class="media">
                                            @if ($list[$i]->image_link)
                                                <div class="image-wrapper image-16-9 position-relative mr-3">
                                                    <img src="{{ Utilities::getFileLink($list[$i]->image_link) }}" alt="{{ $list[$i]->name }}" class="position-absolute w-100 h-100">
                                                </div>
                                            @endif
                                            <div class="media-body">
                                                {{ $list[$i]->name }}
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{-- @if (\Auth::user()->can('cruise-read'))
                                        <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.cruise.show'), ['languageCode' => $languageCode, 'id' => $list[$i]->cruise_id])) }}" target="_blank">
                                            {{ $list[$i]->cruise_name }}
                                        </a>
                                    @else --}}
                                        {{ $list[$i]->cruise_name }}
                                    {{-- @endif --}}
                                </td>
                                <td class="text-center">{{ CruiseUtils::formatDisplayDurationName($list[$i]->duration) }}</td>
                                <td class="text-center">{{ CruiseUtils::formatDisplayItineraryDestination($list[$i]->destination) }}</td>
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