@extends('backend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');
    $queryStr = request()->query();
    $routeParams = $queryStr;
    $routeParams['languageCode'] = $languageCode;
@endphp

@section('content')
    @include('backend::shared.message')
    <div class="card">
        <div class="card-header">
            <h1 class="h4 m-0">{{ $title }}</h1>
        </div>
        <div class="card-body">
            {{
                Form::open([
                    'method' => 'GET',
                    'class' => 'row mb-3'
                ])
            }}
                <div class="form-group col">
                    <label class="col-form-label">Tìm theo tên</label>
                    {{
                        Form::text('name',$queryStr['name'] ?? null,['class' => 'form-control','placeholder' => 'Nhập tên...','autocomplete' => 'off','id' => 'form-input-name'])
                    }}
                </div>
                <div class="form-group col-3 col-xl-2">
                    <label class="col-form-label">Thời lượng</label>
                    {{
                        Form::select('duration',$listDuration,$queryStr['duration'] ?? null,['class' => 'form-control','placeholder' => 'Tất cả','id' => 'form-input-duration'])
                    }}
                </div>
                <div class="form-group col-auto">
                    <label class="col-form-label">&nbsp;</label>
                    <div class="d-flex">
                        <button type="submit" class="btn mr-2 flex-grow-1 btn-dark">Tìm kiếm</button>
                        <a class="btn btn-light" href="#" id="form-btn-clear-input">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
            {{ Form::close() }}
            @canany(['itinerary-create', 'itinerary-delete'])
                <div class="mb-3">
                    @can('itinerary-delete')
                        <button type="button" class="btn btn-danger btn-sm btn-delete-multi" data-ajax-url="{{ route(Utilities::getRouteName('backend.itinerary.destroy'), ['languageCode' => $languageCode]) }}">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </button>
                    @endcan
                    @can('itinerary-create')
                        <a href="{{ route(Utilities::getRouteName('backend.itinerary.create'), $routeParams) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-file-alt"></i> Thêm
                        </a>
                    @endcan
                </div>
            @endcanany
            <table class="table table-striped table-data">
                <thead>
                    <tr>
                        @can('itinerary-delete')
                            <th class="text-center" style="width: 40px;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" id="chk-all" class="chk-all custom-control-input" autocomplete="off" />
                                    <label class="custom-control-label" for="chk-all"></label>
                                </div>
                            </th>
                        @endcan
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>Hành trình</th>
                        <th style="width: 15%" class="text-center">Thời lượng</th>
                        <th style="width: 15%" class="text-center">Địa danh</th>
                        @canany(['itinerary-update', 'itinerary-delete'])
                            <th class="text-center" style="width: 110px;">Ch.năng</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                @for ($i = 0; $i < count($list); $i++)
                    @php
                        $r = $routeParams;
                        $r['id'] = $list[$i]->id;
                    @endphp
                    <tr data-id="{{ $list[$i]->id }}">
                        @can('itinerary-delete')
                            <td class="text-center">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" id="chk-item-{{ $list[$i]->id }}" class="chk-item custom-control-input" value="{{ $list[$i]->id }}" autocomplete="off" />
                                    <label class="custom-control-label" for="chk-item-{{ $list[$i]->id }}"></label>
                                </div>
                            </td>
                        @endcan
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>
                            <a href="{{ route(Utilities::getRouteName('backend.itinerary.show'),$r) }}">
                                {{ e($list[$i]->name) }}
                            </a>
                        </td>
                        <td class="text-center">{{ CruiseUtils::formatDisplayDurationName($list[$i]->duration) }}</td>
                        <td class="text-center">{{ CruiseUtils::formatDisplayItineraryDestination($list[$i]->destination) }}</td>
                        @canany(['itinerary-update', 'itinerary-delete'])
                            <td class="text-center">
                                @can('itinerary-update')
                                    <a href="{{ route(Utilities::getRouteName('backend.itinerary.edit'),$r) }}" class="btn btn-info btn-sm" title="Sửa">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                @endcan
                                @can('itinerary-delete')
                                    <a href="#" class="btn btn-danger btn-sm btn-delete-one" title="Xóa" data-id="{{ $list[$i]->id }}" data-ajax-url="{{ route(Utilities::getRouteName('backend.itinerary.destroy'), ['languageCode' => $languageCode]) }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                @endcan
                            </td>
                        @endcanany
                    </tr>
                @endfor
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function(){
            $('#form-btn-clear-input').click((e) => {
                e.preventDefault();

                $('#form-input-name').val('');
                $('#form-input-duration').val('');
            })
        })
    </script>
@endsection

