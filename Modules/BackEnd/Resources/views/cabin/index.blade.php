@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    <div id="cabin-page-loading" class="page-loading-overlay d-none">
        <div class="text-center text-muted">
            <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
        </div>
    </div>

    <div class="card" id="cabin-index-card">
        <div class="card-header">
            <h1 class="h4 m-0">{{ $title }}</h1>
        </div>
        <div class="card-body">
            {{ Form::open(['method' => 'get', 'class' => 'form-search mb-2']) }}
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>{{ __('backend::cabin.label_cabin_name') }}</label>
                        {{ Form::text('keyword', Request::get('keyword'), ['class' => 'form-control', 'placeholder' => __('backend::cabin.placeholder_search_name'), 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-3">
                        <label>{{ __('backend::cabin.label_facility_type') }}</label>
                        {{ Form::select('group_id', $listCabinType ?? [], Request::get('group_id'), ['class' => 'form-control', 'placeholder' => __('backend::cabin.placeholder_all_facility_type'), 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-3">
                        <label>{{ __('backend::cabin.label_cruise') }}</label>
                        {{ Form::select('cruise_id', $listCruise, Request::get('cruise_id'), ['class' => 'form-control', 'placeholder' => __('backend::cabin.placeholder_all_cruise'), 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-2">
                        <label>&nbsp;</label>
                        {{ Form::button('<i class="fas fa-search"></i> ' . __('backend::cabin.btn_search'), ['type' => 'submit', 'class' => 'btn btn-success btn-block']) }}
                    </div>
                </div>
            {{ Form::close() }}
            @php
                $languageCode = request()->route('languageCode');
                $routeParams = $languageCode ? ['languageCode' => $languageCode] : [];
            @endphp
            @can('cabin-manager-create')
                <div class="mb-3">
                    <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.cabin.create'), $routeParams)) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-file-alt"></i> {{ __('backend::cabin.btn_add') }}
                    </a>
                </div>
            @endcan
            <div class="table-responsive-sm cabin-index-wrapper">
                <table class="table table-striped table-data" id="cabin-index-table">
                    <thead>
                        <tr>
                            <th>{{ __('backend::cabin.col_cabin') }}</th>
                            <th class="text-center cabin-col-type">{{ __('backend::cabin.col_space_type') }}</th>
                            <th class="text-center cabin-col-capacity">{{ __('backend::cabin.col_capacity') }}</th>
                            <th class="text-center cabin-col-area">{{ __('backend::cabin.col_area') }}</th>
                            <th class="text-center cabin-col-price">{{ __('backend::cabin.col_price') }}</th>
                            <th class="text-center cabin-col-cruise">{{ __('backend::cabin.label_cruise') }}</th>
                            @canany(['cabin-manager-update', 'cabin-manager-delete'])
                                <th class="text-center cabin-col-actions">{{ __('backend::cabin.col_actions') }}</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < count($list); $i++)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-start">
                                        @if($list[$i]->image_link)
                                            <div class="position-relative mr-3 cabin-index-thumb">
                                                <img src="{{ Utilities::getFileLink($list[$i]->image_link) }}" alt="{{ $list[$i]->name }}" class="img-thumbnail">
                                                @if($list[$i]->discount_percent && $list[$i]->discount_percent > 0)
                                                    <span class="badge badge-danger position-absolute cabin-index-badge">-{{ $list[$i]->discount_percent }}%</span>
                                                @endif
                                            </div>
                                        @endif
                                        <div class="flex-grow-1">
                                            <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.cabin.show'), array_merge($routeParams, ['id' => $list[$i]->id]))) }}" class="font-weight-bold text-primary cabin-name" title="{{ $list[$i]->name }}">
                                                {{ $list[$i]->name }}
                                            </a>
                                            @if($list[$i]->summary)
                                                <div class="text-muted small mt-1 cabin-summary" title="{{ $list[$i]->summary }}">{{ $list[$i]->summary }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">{{ $list[$i]->group_name ?? '-' }}</td>
                                <td class="text-center">{{ $list[$i]->capacity ? $list[$i]->capacity . ' ' . __('backend::cabin.people') : '-' }}</td>
                                <td class="text-center">{{ $list[$i]->area ? $list[$i]->area . ' m²' : '-' }}</td>
                                <td class="text-center">
                                    @if(isset($list[$i]->min_price) && $list[$i]->min_price)
                                        <div>
                                            <span class="text-primary font-weight-bold cabin-price-value">{{ Utilities::formatDisplayCurrency($list[$i]->min_price) }}</span>
                                        </div>
                                        @if($list[$i]->discount_percent && $list[$i]->discount_percent > 0)
                                            <div class="text-muted small mt-1">{{ __('backend::cabin.price_standard') }}</div>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(!empty($list[$i]->cruise_name))
                                        <span class="cabin-cruise-name" title="{{ $list[$i]->cruise_name }}">{{ $list[$i]->cruise_name }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                @canany(['cabin-manager-update', 'cabin-manager-delete'])
                                    <td class="text-center">
                                        @can('cabin-manager-update')
                                            <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.cabin.edit'), array_merge($routeParams, ['id' => $list[$i]->id]))) }}" class="btn btn-info btn-sm" title="{{ __('backend::cabin.btn_edit') }}">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                        @endcan
                                        @can('cabin-manager-delete')
                                            <a href="#" class="btn btn-danger btn-sm btn-delete-one" title="{{ __('backend::cabin.btn_delete') }}" data-id="{{ $list[$i]->id }}" data-ajax-url="{{ route(Utilities::getRouteName('backend.cabin.destroy'), $routeParams) }}">
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

            {!! $list->appends(Request::all())->links('backend::shared.pagination') !!}
        </div>
    </div>
@endsection

@section('styles')
    <link href="{{ asset('/assets/backend/css/modules/cabin/index.css') }}" rel="stylesheet">
@endsection
