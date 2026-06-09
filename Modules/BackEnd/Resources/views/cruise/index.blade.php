@extends('backend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');
    $queryStr = request()->query();
    $queryStr['languageCode'] = $languageCode;
@endphp

@section('content')
    @include('backend::shared.message')
    <div class="card">
        <div class="card-header">
            <h1 class="h4 m-0">{{ $title }}</h1>
        </div>
        <div class="card-body">
            @canany(['cruise-create', 'cruise-delete'])
                <div class="mb-3">
                    @can('cruise-delete')
                        <button type="button" class="btn btn-danger btn-sm btn-delete-multi" data-ajax-url="{{ route(Utilities::getRouteName('backend.cruise.destroy'), ['languageCode' => $languageCode]) }}">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </button>
                    @endcan
                    @can('cruise-create')
                        <a href="{{ route(Utilities::getRouteName('backend.cruise.create'), $queryStr) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-file-alt"></i> Thêm
                        </a>
                    @endcan
                </div>
            @endcanany
            <table class="table table-striped table-data">
                <thead>
                    <tr>
                        @can('cruise-delete')
                            <th class="text-center" style="width: 40px;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" id="chk-all" class="chk-all custom-control-input" autocomplete="off" />
                                    <label class="custom-control-label" for="chk-all"></label>
                                </div>
                            </th>
                        @endcan
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>Tên du thuyền</th>
                        <th style="width: 15%" class="text-center">Năm đóng</th>
                        <th style="width: 15%" class="text-center">Sức chứa</th>
                        <th style="width: 15%" class="text-center">Hạng</th>
                        @canany(['cruise-update', 'cruise-delete'])
                            <th class="text-center" style="width: 175px;">Ch.năng</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 0; $i < count($list); $i++)
                        @php
                            $r = $queryStr;
                            $r['id'] = $list[$i]->id;
                        @endphp
                        <tr data-id="{{ $list[$i]->id }}">
                            @can('cruise-delete')
                                <td class="text-center">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="chk-item-{{ $list[$i]->id }}" class="chk-item custom-control-input" value="{{ $list[$i]->id }}" autocomplete="off" />
                                        <label class="custom-control-label" for="chk-item-{{ $list[$i]->id }}"></label>
                                    </div>
                                </td>
                            @endcan
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>
                                <a href="{{ route(Utilities::getRouteName('backend.cruise.show'),$r) }}">
                                    {{ e($list[$i]->name) }}
                                </a>
                            </td>
                            <td class="text-center">{{ $list[$i]->year_built }}</td>
                            <td class="text-center">{{ $list[$i]->capacity }}</td>
                            <td class="text-center">{{ $list[$i]->star_rating }}</td>
                            @canany(['cruise-create','cruise-update', 'cruise-delete','cabin-manager-read'])
                                <td class="text-center">
                                    @can('cruise-create')
                                        <a href="javascript:void(0)" data-cruise-id="{{$list[$i]->id}}" class="btn btn-sm btn-success btn-open-itinerary-modal" title="Calendar">
                                            <i class="fas fa-calendar-alt"></i>
                                        </a>
                                    @endcan
                                    @can('cabin-manager-read')
                                        <a href="{{ route(Utilities::getRouteName('backend.cabin.index'), array_merge($queryStr,['cruise_id' => $list[$i]->id]))}}" class="btn btn-warning btn-sm" title="{{ __('backend::cabin.menu_name') }}">
                                            <i class="fas fa-door-closed"></i>
                                        </a>
                                    @endcan
                                    @can('cruise-update')
                                        <a href="{{ route(Utilities::getRouteName('backend.cruise.edit'),$r) }}" class="btn btn-info btn-sm" title="Sửa">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                    @endcan
                                    @can('cruise-delete')
                                        <a href="#" class="btn btn-danger btn-sm btn-delete-one" title="Xóa" data-id="{{ $list[$i]->id }}" data-ajax-url="{{ route(Utilities::getRouteName('backend.cruise.destroy'),$r) }}">
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
    @include('backend::cruise.partials.modal-cruise-itinerary',[
        'cruise' => $list
    ])
@endsection

@section('styles')
    <link href="{{ asset('assets/backend/css/modules/cruise/index.css') }}" rel="stylesheet"/>
@endsection
@section('scripts')
    <script>
        window.listItinerary = @json($listItinerary);
        window.itineraryData = @json(collect($list)->mapWithKeys(fn($cruise) => [
            $cruise->id => $cruise->itineraries->map(fn($itinerary) => array_merge(
                $itinerary->toArray(),
                ['start_at' => $itinerary->pivot->start_at]
            ))
        ]));
        window.listStoreUrl = @json($list->mapWithKeys(fn($cruise) =>
            [$cruise->id => route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.cruise.store-itinerary'),['languageCode' => $languageCode,'id' => $cruise->id])]
        ));
        window.listDeleteUrl = @json($list->mapWithKeys(fn($cruise) =>
            [$cruise->id => route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.cruise.destroy-itinerary'),['languageCode' => $languageCode,'id' => $cruise->id])]
        ));
    </script>
    <script src="{{asset('assets/backend/js/modules/cruise/index.js')}}" type="text/javascript"></script>
@endsection
