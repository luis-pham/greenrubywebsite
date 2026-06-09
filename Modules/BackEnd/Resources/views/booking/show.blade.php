@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    @php $languageCode = \Route::current()->parameter('languageCode'); $routeParams = $languageCode ? ['languageCode' => $languageCode] : []; @endphp
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h1 class="h4 m-0">{{ $title }}: {{ $booking->code }}</h1>
            <div>
                @if($booking->status !== \App\Models\Booking::statusCancelled() && $booking->status !== \App\Models\Booking::statusFailed())
                    <button type="button" class="btn btn-warning btn-sm btn-cancel-booking" data-cancel-url="{{ route(Utilities::getRouteName('backend.booking.cancel'), array_merge($routeParams, ['id' => $booking->id])) }}">
                        <i class="fas fa-ban"></i> {{ __('backend::booking.btn_cancel_booking') }}
                    </button>
                @endif
                <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.booking.index'), $routeParams)) }}" class="btn btn-secondary btn-sm">{{ __('backend::booking.btn_cancel') }}</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="border-bottom pb-2">{{ __('backend::booking.show_customer') }}</h5>
                    <p><strong>{{ $booking->full_name }}</strong></p>
                    <p class="mb-0">{{ __('backend::booking.show_email') }}: {{ $booking->email ?? '—' }}</p>
                    <p class="mb-0">{{ __('backend::booking.show_phone') }}: {{ $booking->phone ?? '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5 class="border-bottom pb-2">{{ __('backend::booking.show_itinerary') }}</h5>
                    <p class="mb-0">{{ __('backend::booking.show_itinerary_label') }}: {{ $booking->itinerary_name ?? '—' }}</p>
                    <p class="mb-0">{{ __('backend::booking.show_cruise') }}: {{ $booking->cruise_name ?? '—' }}</p>
                    <p class="mb-0">{{ __('backend::booking.show_departure_date') }}: {{ $booking->departure_date ? $booking->departure_date->format('d/m/Y') : '—' }}</p>
                    <p class="mb-0">{{ __('backend::booking.show_guests_count') }}: {{ $booking->guests_total ?? 0 }}</p>
                </div>
            </div>

            <h5 class="mt-4 border-bottom pb-2">{{ __('backend::booking.show_cabins') }}</h5>
            @if($booking->cabins->count())
                <table class="table table-sm table-bordered">
                    <thead><tr><th>{{ __('backend::booking.cabin_th_cabin') }}</th><th>{{ __('backend::booking.cabin_th_quantity') }}</th><th>{{ __('backend::booking.cabin_th_adults') }}</th><th>{{ __('backend::booking.cabin_th_6_12') }}</th><th>{{ __('backend::booking.cabin_th_2_5') }}</th><th>{{ __('backend::booking.cabin_th_infants') }}</th><th>{{ __('backend::booking.cabin_th_amount') }}</th></tr></thead>
                    <tbody>
                        @foreach($booking->cabins as $c)
                            @php
                                $priceStr = \Modules\BackEnd\Helpers\Utilities::formatStoredAmount($c->total_price, $booking->currency);
                            @endphp
                            <tr>
                                <td>{{ $c->cabin_name }}</td>
                                <td>{{ $c->quantity }}</td>
                                <td>{{ $c->adults }}</td>
                                <td>{{ $c->children_6_12 }}</td>
                                <td>{{ $c->children_2_5 }}</td>
                                <td>{{ $c->infants }}</td>
                                <td>{{ $priceStr }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">{{ __('backend::booking.no_cabins') }}</p>
            @endif

            <h5 class="mt-4 border-bottom pb-2">{{ __('backend::booking.show_amenities') }}</h5>
            @if($booking->amenities->count())
                <table class="table table-sm table-bordered">
                    <thead><tr><th>{{ __('backend::booking.amenity_th_name') }}</th><th>{{ __('backend::booking.amenity_th_quantity') }}</th><th>{{ __('backend::booking.amenity_th_amount') }}</th></tr></thead>
                    <tbody>
                        @foreach($booking->amenities as $a)
                            @php
                                $priceStr = \Modules\BackEnd\Helpers\Utilities::formatStoredAmount($a->total_price, $booking->currency);
                            @endphp
                            <tr><td>{{ $a->amenity_name }}</td><td>{{ $a->quantity }}</td><td>{{ $priceStr }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">Không có tiện ích</p>
            @endif

            <h5 class="mt-4 border-bottom pb-2">{{ __('backend::booking.show_payment') }}</h5>
            @php
                $p = $booking->payment;
                $gatewayKey = 'gateway_' . ($p ? $p->gateway : '');
                $gatewayLabel = $p ? (__('backend::booking.' . $gatewayKey) ?: $p->gateway) : '—';
                $paymentStatusKey = $p ? ('payment_' . $p->status) : '';
                $paymentStatusLabel = $p ? (__('backend::booking.' . $paymentStatusKey) ?: $p->status) : '—';
                $totalFormatted = \Modules\BackEnd\Helpers\Utilities::formatStoredAmount($booking->total_amount, $booking->currency);
            @endphp
            <p class="mb-0">{{ __('backend::booking.show_payment_method') }}: {{ $gatewayLabel }}</p>
            <p class="mb-0">{{ __('backend::booking.show_payment_status') }}: {{ $paymentStatusLabel }}</p>
            <p class="mb-0"><strong>{{ __('backend::booking.show_total_amount') }}: {{ $totalFormatted }}</strong></p>
            <p class="mb-0">
                {{ __('backend::booking.show_booking_status') }}:
                @php
                    $status = $booking->status;
                    if ($status === \App\Models\Booking::statusConfirmed()) {
                        $badgeClass = 'success';
                    } elseif ($status === \App\Models\Booking::statusPaid()) {
                        $badgeClass = 'info';
                    } elseif ($status === \App\Models\Booking::statusPending()) {
                        $badgeClass = 'warning';
                    } else {
                        $badgeClass = 'danger';
                    }
                @endphp
                <span class="badge badge-{{ $badgeClass }}">{{ \App\Models\Booking::statusLabel($booking->status) }}</span>
            </p>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            $(document).on('click', '.btn-cancel-booking', function() {
                var url = $(this).data('cancel-url');
                var confirmMsg = {{ json_encode(__('backend::booking.confirm_cancel_booking')) }};
                var errorGeneric = {{ json_encode(__('backend::booking.error_generic')) }};
                var errorConnection = {{ json_encode(__('backend::booking.error_connection')) }};
                var doCancel = function() {
                    $.post(url, { _token: $('input[name="_token"]').val() }).done(function(res) {
                        if (res.success) {
                            window.location.reload();
                        } else {
                            swalAlert.error(res.message || errorGeneric);
                        }
                    }).fail(function() {
                        swalAlert.error(errorConnection);
                    });
                };
                swalAlert.confirm(confirmMsg).then(function(result) {
                    if (result.isConfirmed) {
                        doCancel();
                    }
                });
            });
        });
    </script>
@endsection
