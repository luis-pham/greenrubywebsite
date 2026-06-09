@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    @php $languageCode = \Route::current()->parameter('languageCode'); $routeParams = $languageCode ? ['languageCode' => $languageCode] : []; @endphp
    <div id="booking-messages" class="d-none"
         data-confirm-order="{{ __('backend::booking.confirm_order_message') }}"
         data-confirm-delete="{{ __('backend::booking.confirm_delete_booking') }}"
         data-confirm-cancel="{{ __('backend::booking.confirm_cancel_booking') }}"
         data-confirm-delete-quote="{{ __('backend::booking.quote_confirm_delete') }}"
         data-loading="{{ __('backend::booking.loading') }}"
         data-error-load="{{ __('backend::booking.error_load_data') }}"
         data-journey-label="{{ __('backend::booking.journey_label') }}"
         data-error-generic="{{ __('backend::booking.error_generic') }}"
         data-error-connection="{{ __('backend::booking.error_connection') }}"
         data-not-found-booking="{{ __('backend::booking.not_found_booking') }}"
         data-confirm-delete-quote-fallback="{{ __('backend::booking.confirm_delete_quote_fallback') }}"></div>
    @php $currentTab = Request::get('tab', 'booking'); @endphp
    <div id="booking-page-loading" class="page-loading-overlay d-none">
        <div class="text-center text-muted">
            <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
            <div>{{ __('backend::booking.loading') }}</div>
        </div>
    </div>

    <div class="card" id="booking-index-card">
        <div class="card-header">
            <h1 class="h4 m-0">{{ $title }}</h1>
            <p class="text-muted small mb-0 mt-1">{{ __('backend::booking.page_subtitle') }}</p>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $currentTab !== 'quote' ? 'active' : '' }}" href="{{ route(Utilities::getRouteName('backend.booking.index'), array_merge($routeParams, Request::only('keyword', 'status'))) }}">{{ __('backend::booking.tab_booking') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $currentTab === 'quote' ? 'active' : '' }}" href="{{ route(Utilities::getRouteName('backend.booking.index'), array_merge($routeParams, ['tab' => 'quote'], Request::only('quote_keyword', 'quote_status'))) }}">{{ __('backend::booking.tab_quote') }}</a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane {{ $currentTab !== 'quote' ? 'show active' : '' }}" id="tab-booking">
            {{ Form::open(['method' => 'get', 'class' => 'form-search mb-3']) }}
                <div class="form-row align-items-end">
                    <div class="form-group col-md-4">
                        <label>{{ __('backend::booking.label_search') }}</label>
                        {{ Form::text('keyword', Request::get('keyword'), ['class' => 'form-control', 'placeholder' => __('backend::booking.placeholder_search'), 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-2">
                        <label>{{ __('backend::booking.label_status') }}</label>
                        {{ Form::select('status', [
                            '' => __('backend::booking.status_all'),
                            \App\Models\Booking::statusPending() => __('backend::booking.status_pending'),
                            \App\Models\Booking::statusPaid() => __('backend::booking.status_paid'),
                            \App\Models\Booking::statusConfirmed() => __('backend::booking.status_confirmed'),
                            \App\Models\Booking::statusCancelled() => __('backend::booking.status_cancelled'),
                            \App\Models\Booking::statusFailed() => __('backend::booking.status_failed'),
                        ], Request::get('status'), ['class' => 'form-control', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-2">
                        <label>Từ ngày</label>
                        {{ Form::text('from_date', Request::get('from_date'), ['class' => 'form-control date-picker', 'placeholder' => 'dd/mm/yyyy', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-2">
                        <label>Đến ngày</label>
                        {{ Form::text('to_date', Request::get('to_date'), ['class' => 'form-control date-picker', 'placeholder' => 'dd/mm/yyyy', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-2">
                        <label>&nbsp;</label>
                        <div class="d-flex">
                            {{ Form::button('<i class="fas fa-search"></i>', ['type' => 'submit', 'class' => 'btn btn-success flex-fill']) }}
                            <a href="{{ route(Utilities::getRouteName('backend.booking.index'), $routeParams) }}" class="btn btn-outline-secondary ml-2" title="{{ __('backend::booking.btn_refresh') }}"><i class="fas fa-sync-alt"></i></a>
                        </div>
                    </div>
                </div>
            {{ Form::close() }}

            <div class="table-responsive-sm">
                <table class="table table-striped table-data" id="booking-index-table">
                    <thead>
                        <tr>
                            <th>{{ __('backend::booking.col_code') }}</th>
                            <th>{{ __('backend::booking.col_customer') }}</th>
                            <th>{{ __('backend::booking.col_itinerary') }}</th>
                            <th>{{ __('backend::booking.col_booking_time') }}</th>
                            <th class="text-center">{{ __('backend::booking.col_guests') }}</th>
                            <th>{{ __('backend::booking.col_payment') }}</th>
                            <th class="text-right">{{ __('backend::booking.col_total') }}</th>
                            <th class="text-center">{{ __('backend::booking.col_status') }}</th>
                            <th class="text-center">{{ __('backend::booking.col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($list as $item)
                            @php
                                $payment = $item->payment;
                                $gatewayKey = 'gateway_' . ($payment ? $payment->gateway : '');
                                $gatewayLabel = $payment ? (__('backend::booking.' . $gatewayKey) ?: $payment->gateway) : '—';
                                $paymentStatus = $payment ? $payment->status : null;
                                $paymentStatusKey = 'payment_' . $paymentStatus;
                                $paymentStatusLabel = $paymentStatus ? (__('backend::booking.' . $paymentStatusKey) ?: $paymentStatus) : '—';
                                $statusLabel = \App\Models\Booking::statusLabel($item->status);
                                $currency = strtolower($item->currency ?? 'vnd');
                                $displayTotal = $currency === 'usd'
                                    ? $item->total_amount / 100
                                    : $item->total_amount;
                                $totalFormatted = $currency === 'usd'
                                    ? number_format($displayTotal, 2) . ' $'
                                    : number_format($displayTotal, 0, ',', '.') . ' ₫';
                            @endphp
                            <tr>
                                <td><strong>{{ $item->code }}</strong></td>
                                <td>
                                    <div>{{ $item->full_name }}</div>
                                    @if($item->phone)<div class="small text-muted">{{ $item->phone }}</div>@endif
                                </td>
                                <td>
                                    @if($item->itinerary_name || $item->cruise_name)
                                        <div>{{ $item->itinerary_name ?? '-' }}</div>
                                        @if($item->cruise_name)<div class="small text-muted">{{ $item->cruise_name }}</div>@endif
                                        @if($item->departure_date)<div class="small">{{ $item->departure_date->format('d/m/Y') }}</div>@endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '—' }}</td>
                                <td class="text-center">
                                    <i class="fas fa-user fa-fw text-muted"></i> {{ $item->guests_total ?? 0 }}
                                </td>
                                <td>
                                    <div>{{ $gatewayLabel }}</div>
                                    <div class="small">
                                        @if($paymentStatus === 'succeeded')
                                            <span class="badge badge-success">●</span>
                                        @elseif($paymentStatus === 'pending')
                                            <span class="badge badge-warning">●</span>
                                        @elseif($paymentStatus === 'failed' || $paymentStatus === 'canceled')
                                            <span class="badge badge-danger">●</span>
                                        @else
                                            <span class="badge badge-secondary">●</span>
                                        @endif
                                        {{ $paymentStatusLabel }}
                                    </div>
                                </td>
                                <td class="text-right font-weight-bold">{{ $totalFormatted }}</td>
                                <td class="text-center">
                                    @if($item->status === \App\Models\Booking::statusConfirmed())
                                        <span class="badge badge-success">{{ $statusLabel }}</span>
                                    @elseif($item->status === \App\Models\Booking::statusPaid())
                                        <span class="badge badge-info">{{ $statusLabel }}</span>
                                    @elseif($item->status === \App\Models\Booking::statusPending())
                                        <span class="badge badge-warning">{{ $statusLabel }}</span>
                                    @elseif($item->status === \App\Models\Booking::statusCancelled() || $item->status === \App\Models\Booking::statusFailed())
                                        <span class="badge badge-danger">{{ $statusLabel }}</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $statusLabel }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-info btn-sm btn-booking-detail" title="{{ __('backend::booking.btn_view') }}" data-id="{{ $item->id }}" data-detail-url="{{ route(Utilities::getRouteName('backend.booking.detail'), array_merge($routeParams, ['id' => $item->id])) }}" data-confirm-url="{{ route(Utilities::getRouteName('backend.booking.confirm'), array_merge($routeParams, ['id' => $item->id])) }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($item->status !== \App\Models\Booking::statusCancelled() && $item->status !== \App\Models\Booking::statusFailed())
                                        <button type="button" class="btn btn-warning btn-sm btn-cancel-booking" title="{{ __('backend::booking.btn_cancel_booking') }}" data-id="{{ $item->id }}" data-cancel-url="{{ route(Utilities::getRouteName('backend.booking.cancel'), array_merge($routeParams, ['id' => $item->id])) }}">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @endif
                                    @if($item->status === \App\Models\Booking::statusCancelled())
                                        <button type="button" class="btn btn-danger btn-sm btn-delete-one" title="{{ __('backend::booking.btn_delete') }}" data-id="{{ $item->id }}" data-ajax-url="{{ route(Utilities::getRouteName('backend.booking.destroy'), $routeParams) }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">{{ __('backend::booking.empty_table') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {!! $list->appends(Request::all())->links('backend::shared.pagination') !!}
                </div>

                <div class="tab-pane {{ $currentTab === 'quote' ? 'show active' : '' }}" id="tab-quote">
            {{ Form::open(['method' => 'get', 'class' => 'form-search mb-3']) }}
                {{ Form::hidden('tab', 'quote') }}
                <div class="form-row align-items-end">
                    <div class="form-group col-md-4">
                        <label>{{ __('backend::booking.quote_label_search') }}</label>
                        {{ Form::text('quote_keyword', Request::get('quote_keyword'), ['class' => 'form-control', 'placeholder' => __('backend::booking.quote_placeholder_search'), 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-2">
                        <label>{{ __('backend::booking.quote_col_status') }}</label>
                        {{ Form::select('quote_status', [
                            '' => __('backend::booking.quote_status_all'),
                            'new' => __('backend::booking.quote_status_new'),
                            'contacted' => __('backend::booking.quote_status_contacted'),
                        ], Request::get('quote_status'), ['class' => 'form-control', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-2">
                        <label>Từ ngày</label>
                        {{ Form::text('quote_from_date', Request::get('quote_from_date'), ['class' => 'form-control date-picker', 'placeholder' => 'dd/mm/yyyy', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-2">
                        <label>Đến ngày</label>
                        {{ Form::text('quote_to_date', Request::get('quote_to_date'), ['class' => 'form-control date-picker', 'placeholder' => 'dd/mm/yyyy', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-2">
                        <label>&nbsp;</label>
                        <div class="d-flex">
                            {{ Form::button('<i class="fas fa-search"></i>', ['type' => 'submit', 'class' => 'btn btn-success flex-fill']) }}
                            <a href="{{ route(Utilities::getRouteName('backend.booking.index'), array_merge($routeParams, ['tab' => 'quote'])) }}" class="btn btn-outline-secondary ml-2" title="{{ __('backend::booking.btn_refresh') }}"><i class="fas fa-sync-alt"></i></a>
                        </div>
                    </div>
                </div>
            {{ Form::close() }}

            <div class="table-responsive-sm">
                <table class="table table-striped table-data" id="quote-index-table">
                    <thead>
                        <tr>
                            <th>{{ __('backend::booking.quote_col_code') }}</th>
                            <th>{{ __('backend::booking.quote_col_customer') }}</th>
                            <th class="text-center">{{ __('backend::booking.quote_col_guests') }}</th>
                            <th>{{ __('backend::booking.quote_col_service') }}</th>
                            <th>{{ __('backend::booking.quote_col_date_sent') }}</th>
                            <th class="text-center">{{ __('backend::booking.quote_col_status') }}</th>
                            <th class="text-center">{{ __('backend::booking.col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quoteList as $q)
                            @php
                                $eventKey = 'quote_event_' . $q->event_type;
                                $eventLabel = $q->event_type ? (__('backend::booking.' . $eventKey) ?: $q->event_type) : '—';
                                $quoteStatusKey = 'quote_status_' . $q->status;
                                $quoteStatusLabel = __('backend::booking.' . $quoteStatusKey) ?: $q->status;
                            @endphp
                            <tr>
                                <td><strong>{{ $q->code }}</strong></td>
                                <td>
                                    <div>{{ $q->contact_name }}</div>
                                    @if($q->phone)<div class="small text-muted">{{ $q->phone }}</div>@endif
                                </td>
                                <td class="text-center">{{ $q->number ?? '—' }}</td>
                                <td>
                                    <div>{{ $eventLabel }}</div>
                                    @if($q->note)<div class="small text-muted">{{ Str::limit($q->note, 60) }}</div>@endif
                                </td>
                                <td>{{ $q->created_at ? $q->created_at->format('Y-m-d') : '—' }}</td>
                                <td class="text-center">
                                    @if($q->status === 'contacted')
                                        <span class="badge badge-success">{{ $quoteStatusLabel }}</span>
                                    @else
                                        <span class="badge badge-warning">{{ $quoteStatusLabel }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($q->status === 'new')
                                        <button type="button" class="btn btn-success btn-sm btn-quote-status" title="{{ __('backend::booking.quote_btn_contacted') }}" data-id="{{ $q->id }}" data-status-url="{{ route(Utilities::getRouteName('backend.booking.quoteStatus'), array_merge($routeParams, ['id' => $q->id])) }}">
                                            <i class="fas fa-phone-alt"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-danger btn-sm btn-quote-delete-one" title="{{ __('backend::booking.btn_delete') }}" data-id="{{ $q->id }}" data-ajax-url="{{ route(Utilities::getRouteName('backend.booking.quoteDestroy'), $routeParams) }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">{{ __('backend::booking.empty_table') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {!! $quoteList->appends(Request::all())->links('backend::shared.pagination') !!}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bookingDetailModal" tabindex="-1" role="dialog" aria-labelledby="bookingDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="rounded bg-primary text-white d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px;">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div>
                            <h5 class="modal-title font-weight-bold mb-0" id="bookingDetailModalLabel">{{ __('backend::booking.modal_title') }} <span id="bookingDetailCode">—</span></h5>
                            <p class="text-muted small mb-0 mt-1" id="bookingDetailJourney">—</p>
                        </div>
                    </div>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-2" id="bookingDetailBody">
                    <div class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin fa-2x"></i><br>{{ __('backend::booking.loading') }}</div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary text-dark" data-dismiss="modal">{{ __('backend::booking.btn_close') }}</button>
                    <button type="button" class="btn btn-primary" id="bookingDetailConfirmBtn" style="display: none;">
                        <i class="fas fa-check mr-1"></i> {{ __('backend::booking.btn_confirm_order') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            var messages = $('#booking-messages');
            var confirmOrderMsg = messages.attr('data-confirm-order');
            var confirmDeleteMsg = messages.attr('data-confirm-delete');
            var confirmCancelMsg = messages.attr('data-confirm-cancel');
            var confirmDeleteQuoteMsg = messages.attr('data-confirm-delete-quote');
            var confirmDeleteQuoteFallback = messages.attr('data-confirm-delete-quote-fallback');
            var loadingHtml = '<div class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin fa-2x"></i><br>' + (messages.attr('data-loading') || '') + '</div>';
            var errorLoad = messages.attr('data-error-load');
            var notFoundBooking = messages.attr('data-not-found-booking');
            var journeyLabel = messages.attr('data-journey-label');
            var errorGeneric = messages.attr('data-error-generic');
            var errorConnection = messages.attr('data-error-connection');
            var pageLoading = $('#booking-page-loading');

            function showPageLoading() {
                pageLoading.removeClass('d-none');
            }

            function hidePageLoading() {
                pageLoading.addClass('d-none');
            }

            $(document).on('click', '#booking-index-card .btn-booking-detail', function(e) {
                e.preventDefault();
                var btn = $(this);
                var url = btn.data('detail-url');
                var confirmUrl = btn.data('confirm-url');
                $('#bookingDetailBody').html(loadingHtml);
                $('#bookingDetailConfirmBtn').hide().off('click');
                $('#bookingDetailModal').modal('show');
                showPageLoading();
                $.get(url).done(function(res) {
                    hidePageLoading();
                    if (!res.success) {
                        $('#bookingDetailBody').html('<p class="text-danger">' + notFoundBooking + '</p>');
                        swalAlert.error(errorGeneric);
                        return;
                    }
                    $('#bookingDetailCode').text(res.title || '—');
                    $('#bookingDetailJourney').text(res.journey_name ? (journeyLabel + ': ' + res.journey_name) : '—');
                    $('#bookingDetailBody').html(res.html);
                    if (res.show_confirm && confirmUrl) {
                        $('#bookingDetailConfirmBtn').show().on('click', function() {
                            swalAlert.confirm(confirmOrderMsg).then(function(result) {
                                if (!result.isConfirmed) return;
                                showPageLoading();
                                $.post(confirmUrl, { _token: $('input[name="_token"]').val() }).done(function(r) {
                                    hidePageLoading();
                                    if (r.success) {
                                        $('#bookingDetailModal').modal('hide');
                                        window.location.reload();
                                    } else {
                                        swalAlert.error(r.message || errorGeneric);
                                    }
                                }).fail(function() {
                                    hidePageLoading();
                                    swalAlert.error(errorConnection);
                                });
                            });
                        });
                    }
                }).fail(function() {
                    hidePageLoading();
                    $('#bookingDetailBody').html('<p class="text-danger">' + errorLoad + '</p>');
                    swalAlert.error(errorLoad);
                });
            });

            $(document).on('click', '#booking-index-card .btn-delete-one', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var url = $(this).data('ajax-url');
                var doDelete = function() {
                    showPageLoading();
                    $.post(url, { id: id, _token: $('input[name="_token"]').val() }).done(function(res) {
                        hidePageLoading();
                        if (res.success) {
                            window.location.reload();
                        } else {
                            swalAlert.error(res.message || errorGeneric);
                        }
                    }).fail(function() {
                        hidePageLoading();
                        swalAlert.error(errorConnection);
                    });
                };
                swalAlert.confirm(confirmDeleteMsg).then(function(result) {
                    if (result.isConfirmed) {
                        doDelete();
                    }
                });
            });
            $(document).on('click', '#booking-index-card .btn-cancel-booking', function(e) {
                e.preventDefault();
                var url = $(this).data('cancel-url');
                var doCancel = function() {
                    showPageLoading();
                    $.post(url, { _token: $('input[name="_token"]').val() }).done(function(res) {
                        hidePageLoading();
                        if (res.success) {
                            window.location.reload();
                        } else {
                            swalAlert.error(res.message || errorGeneric);
                        }
                    }).fail(function() {
                        hidePageLoading();
                        swalAlert.error(errorConnection);
                    });
                };
                swalAlert.confirm(confirmCancelMsg).then(function(result) {
                    if (result.isConfirmed) {
                        doCancel();
                    }
                });
            });

            $(document).on('click', '#booking-index-card .btn-quote-delete-one', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var url = $(this).data('ajax-url');
                var message = confirmDeleteQuoteMsg || confirmDeleteQuoteFallback;
                var doDeleteQuote = function() {
                    showPageLoading();
                    $.post(url, { id: id, _token: $('input[name="_token"]').val() }).done(function(res) {
                        hidePageLoading();
                        if (res.success) {
                            window.location.reload();
                        } else {
                            swalAlert.error(res.message || errorGeneric);
                        }
                    }).fail(function() {
                        hidePageLoading();
                        swalAlert.error(errorConnection);
                    });
                };
                swalAlert.confirm(message).then(function(result) {
                    if (result.isConfirmed) {
                        doDeleteQuote();
                    }
                });
            });
            $(document).on('click', '#booking-index-card .btn-quote-status', function(e) {
                e.preventDefault();
                var url = $(this).data('status-url');
                showPageLoading();
                $.post(url, { _token: $('input[name="_token"]').val() }).done(function(res) {
                    hidePageLoading();
                    if (res.success) {
                        window.location.reload();
                    } else {
                        swalAlert.error(res.message || errorGeneric);
                    }
                }).fail(function() {
                    hidePageLoading();
                    swalAlert.error(errorConnection);
                });
            });
        });
    </script>
@endsection
