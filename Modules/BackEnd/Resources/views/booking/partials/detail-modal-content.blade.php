@php
    $currency = strtolower($booking->currency ?? 'vnd');
    $isUsd = $currency === 'usd';
    $formatMoney = function($amount) use ($isUsd) {
        $display = $isUsd ? ($amount / 100) : $amount;
        return $isUsd ? number_format($display, 2) . ' $' : number_format($display, 0, ',', '.') . ' ₫';
    };
    $statusLabel = \App\Models\Booking::statusLabel($booking->status);
    $p = $booking->payment;
    $gatewayKey = 'gateway_' . ($p ? $p->gateway : '');
    $gatewayLabel = $p ? (__('backend::booking.' . $gatewayKey) ?: $p->gateway) : '—';
    $paymentStatusKey = 'payment_' . ($p ? $p->status : '');
    $paymentStatusLabel = $p ? (__('backend::booking.' . $paymentStatusKey) ?: $p->status) : '—';
    $paymentCompleted = $p && $p->status === 'succeeded';
    $customerInitial = mb_strtoupper(mb_substr($booking->full_name ?? 'N', 0, 1));
@endphp

<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3 mb-md-0">
        <div class="rounded border bg-light p-3 h-100">
            <div class="text-muted small text-uppercase">{{ __('backend::booking.col_status') }}</div>
            <div class="mt-1">
                @if($booking->status === \App\Models\Booking::statusConfirmed())
                    <span class="badge badge-success px-3 py-2">{{ $statusLabel }}</span>
                @elseif($booking->status === \App\Models\Booking::statusPaid())
                    <span class="badge badge-info px-3 py-2">{{ $statusLabel }}</span>
                @elseif($booking->status === \App\Models\Booking::statusPending())
                    <span class="badge badge-warning px-3 py-2">{{ $statusLabel }}</span>
                @elseif($booking->status === \App\Models\Booking::statusCancelled() || $booking->status === \App\Models\Booking::statusFailed())
                    <span class="badge badge-danger px-3 py-2">{{ $statusLabel }}</span>
                @else
                    <span class="badge badge-secondary px-3 py-2">{{ $statusLabel }}</span>
                @endif
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3 mb-md-0">
        <div class="rounded border bg-light p-3 h-100">
            <div class="text-muted small text-uppercase">{{ __('backend::booking.modal_departure') }}</div>
            <div class="mt-1 font-weight-bold">
                <i class="fas fa-calendar-alt text-muted mr-1"></i>
                {{ $booking->departure_date ? $booking->departure_date->format('d/m/Y') : '—' }}
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3 mb-md-0">
        <div class="rounded border bg-light p-3 h-100">
            <div class="text-muted small text-uppercase">{{ __('backend::booking.modal_total_guests') }}</div>
            <div class="mt-1 font-weight-bold">
                <i class="fas fa-users text-muted mr-1"></i>
                {{ $booking->guests_total ?? 0 }} {{ __('backend::booking.modal_guests_unit') }}
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rounded border bg-light p-3 h-100">
            <div class="text-muted small text-uppercase">{{ __('backend::booking.modal_total_amount') }}</div>
            <div class="mt-1 font-weight-bold text-primary">{{ $formatMoney($booking->total_amount) }}</div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <h6 class="pl-2 mb-2">{{ __('backend::booking.modal_customer_info') }}</h6>
        <div class="rounded border bg-white p-3">
            <div class="d-flex align-items-center mb-2">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3" style="width: 42px; height: 42px; font-weight: bold;">{{ $customerInitial }}</div>
                <strong class="text-dark">{{ $booking->full_name ?? '—' }}</strong>
            </div>
            <div class="small text-muted"><i class="fas fa-envelope mr-2"></i>{{ $booking->email ?? '—' }}</div>
            <div class="small text-muted"><i class="fas fa-phone mr-2"></i>{{ $booking->phone ?? '—' }}</div>
        </div>
    </div>
    <div class="col-md-6">
        <h6 class="pl-2 mb-2">{{ __('backend::booking.modal_payment_notes') }}</h6>
        <div class="rounded border bg-white p-3">
            <div class="mb-2">
                <span class="text-muted small">{{ __('backend::booking.modal_payment_method') }}:</span>
                <span class="ml-2"><i class="fas fa-university text-muted mr-1"></i>{{ $gatewayLabel }}</span>
            </div>
            <div>
                <span class="text-muted small">{{ __('backend::booking.col_status') }}:</span>
                @if($paymentCompleted)
                    <span class="badge badge-success ml-2">{{ $paymentStatusLabel }}</span>
                @else
                    <span class="badge badge-warning ml-2">{{ $paymentStatusLabel }}</span>
                @endif
            </div>
            @if(isset($booking->note) && (string)$booking->note !== '')
                <div class="mt-2 p-2 rounded bg-warning bg-light border border-warning">
                    <i class="fas fa-sticky-note text-muted mr-1"></i>
                    <em>{{ $booking->note }}</em>
                </div>
            @endif
        </div>
    </div>
</div>

<h6 class="pl-2 mb-2">{{ __('backend::booking.modal_cabins_list') }}</h6>
<div class="table-responsive mb-4">
    <table class="table table-sm table-bordered table-hover mb-0">
        <thead class="thead-light">
            <tr>
                <th>{{ __('backend::booking.modal_room_type') }}</th>
                <th class="text-center">{{ __('backend::booking.modal_quantity') }}</th>
                <th>{{ __('backend::booking.modal_guest_details') }}</th>
                <th class="text-right">{{ __('backend::booking.modal_amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($booking->cabins as $c)
                @php
                    $guestParts = [];
                    if (($c->adults ?? 0) > 0) $guestParts[] = __('backend::booking.modal_adults') . ': ' . $c->adults;
                    if (($c->children_6_12 ?? 0) > 0) $guestParts[] = __('backend::booking.modal_6_12') . ': ' . $c->children_6_12;
                    if (($c->children_2_5 ?? 0) > 0) $guestParts[] = __('backend::booking.modal_2_5') . ': ' . $c->children_2_5;
                    if (($c->infants ?? 0) > 0) $guestParts[] = __('backend::booking.modal_infants') . ': ' . $c->infants;
                    $guestStr = implode(', ', $guestParts) ?: '—';
                @endphp
                <tr>
                    <td><i class="fas fa-bed text-primary mr-1"></i>{{ $c->cabin_name }}</td>
                    <td class="text-center">x{{ $c->quantity }}</td>
                    <td class="text-primary small">{{ $guestStr }}</td>
                    <td class="text-right font-weight-bold">{{ $formatMoney($c->total_price) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-3">—</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<h6 class="pl-2 mb-2">{{ __('backend::booking.modal_amenities_list') }}</h6>
<div class="table-responsive">
    <table class="table table-sm table-bordered table-hover mb-0">
        <thead class="thead-light">
            <tr>
                <th>{{ __('backend::booking.modal_service_name') }}</th>
                <th class="text-center">{{ __('backend::booking.modal_quantity') }}</th>
                <th class="text-right">{{ __('backend::booking.modal_unit_price') }}</th>
                <th class="text-right">{{ __('backend::booking.modal_amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($booking->amenities as $a)
                @php
                    $amenityQty = max((int) ($a->quantity ?? 1), 1);
                    $amenityLineTotal = ($a->unit_price ?? 0) * $amenityQty;
                @endphp
                <tr>
                    <td>{{ $a->amenity_name }}</td>
                    <td class="text-center">{{ $amenityQty }}</td>
                    <td class="text-right">{{ $formatMoney($a->unit_price) }}</td>
                    <td class="text-right font-weight-bold">{{ $formatMoney($amenityLineTotal) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-3">—</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
