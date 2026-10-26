{{-- Money panel (TPX decisions 3, 5, 6): balance, secure links, refunds with approval, audit trail. --}}
@php
    $money = fn ($amount) => \Modules\BackEnd\Helpers\Utilities::formatStoredAmount((int) $amount, $booking->currency);
    $major = fn ($amount) => strtolower((string) $booking->currency) === 'usd' ? number_format($amount / 100, 2, '.', '') : (string) (int) $amount;
    $who = fn ($id) => $id ? ($ops['names'][$id] ?? ('#' . $id)) : __('backend::booking.ops_system');
    $vn = fn ($d) => $d ? \Carbon\Carbon::parse($d)->setTimezone(config('booking.timezone'))->format('d/m/Y H:i') : '—';
@endphp
<div class="card mt-3">
    <div class="card-header"><h2 class="h5 m-0">{{ __('backend::booking.ops_title') }}</h2></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm mb-3">
                    <tr><th>{{ __('backend::booking.modal_payment_plan') }}</th><td>{{ $booking->payment_plan ? __('backend::booking.plan_' . $booking->payment_plan) : '—' }}</td></tr>
                    <tr><th>{{ __('backend::booking.ops_payment_status') }}</th><td>{{ $booking->payment_status ? __('backend::booking.pay_status_' . $booking->payment_status) : '—' }}</td></tr>
                    <tr><th>{{ __('backend::booking.show_total_amount') }}</th><td>{{ $money($booking->total_amount) }}</td></tr>
                    <tr><th>{{ __('backend::booking.modal_amount_paid') }}</th><td>{{ $money($booking->amount_paid) }}</td></tr>
                    <tr><th>{{ __('backend::booking.ops_refunded') }}</th><td>{{ $money($booking->amount_refunded) }}</td></tr>
                    @if($booking->balanceAmount() > 0)
                        <tr class="table-warning"><th>{{ __('backend::booking.modal_balance') }}</th><td>{{ $money($booking->balanceAmount()) }} · {{ __('backend::booking.modal_balance_due') }} {{ optional($booking->balance_due_date)->format('d/m/Y') }}</td></tr>
                    @endif
                    @if($booking->cure_deadline_at)
                        <tr class="table-danger"><th>{{ __('backend::booking.ops_cure_deadline') }}</th><td>{{ $vn($booking->cure_deadline_at) }}</td></tr>
                    @endif
                    <tr><th>{{ __('backend::booking.modal_terms') }}</th><td>{{ $booking->terms_version ? $booking->terms_version . ' · ' . $vn($booking->terms_accepted_at) . ' · ' . strtoupper((string) $booking->terms_locale) : '—' }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h3 class="h6">{{ __('backend::booking.ops_link_title') }}</h3>
                @if($ops['linkPurpose'])
                    <p class="small text-muted mb-2">{{ __('backend::booking.ops_link_help_' . $ops['linkPurpose']) }}</p>
                    <form method="post" action="{{ route('backend.booking.paymentLink', ['id' => $booking->id]) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm" @if($ops['linkPurpose'] === 'booking' && !$ops['termsConfigured']) disabled @endif>
                            {{ __('backend::booking.ops_link_send_' . $ops['linkPurpose']) }}
                        </button>
                    </form>
                    @if($ops['linkPurpose'] === 'booking' && !$ops['termsConfigured'])
                        <p class="small text-danger mt-1">{{ __('backend::booking.link_terms_not_configured') }}</p>
                    @endif
                @else
                    <p class="small text-muted">{{ __('backend::booking.link_not_available') }}</p>
                @endif

                <h3 class="h6 mt-3">{{ __('backend::booking.ops_notifications') }}</h3>
                @forelse($ops['notifications'] as $n)
                    <div class="small">{{ $vn($n->sent_at) }} · {{ __('backend::booking.notification_' . $n->type) }}@if(str_contains((string) $n->meta, '"skipped":true')) ({{ __('backend::booking.ops_skipped') }})@endif</div>
                @empty
                    <p class="small text-muted">—</p>
                @endforelse
            </div>
        </div>

        <h3 class="h6 mt-3 border-top pt-3">{{ __('backend::booking.ops_refunds') }}</h3>
        <p class="small text-muted">{{ __('backend::booking.ops_refund_rules') }}</p>
        @if($ops['refunds']->count())
            <table class="table table-sm table-bordered">
                <thead><tr>
                    <th>#</th><th>{{ __('backend::booking.ops_refund_amount') }}</th><th>{{ __('backend::booking.ops_refund_reason') }}</th>
                    <th>{{ __('backend::booking.ops_refund_status') }}</th><th>{{ __('backend::booking.ops_refund_trail') }}</th><th></th>
                </tr></thead>
                <tbody>
                @foreach($ops['refunds'] as $r)
                    <tr>
                        <td>{{ $r->id }}</td>
                        <td>{{ $money($r->amount) }}</td>
                        <td>{{ __('backend::booking.refund_reason_' . $r->reason) }}@if($r->note)<br><span class="small text-muted">{{ $r->note }}</span>@endif</td>
                        <td>{{ __('backend::booking.refund_status_' . $r->status) }}</td>
                        <td class="small">
                            {{ __('backend::booking.ops_requested_by') }} {{ $who($r->requested_by) }} · {{ $vn($r->requested_at) }}
                            @if($r->decided_by)<br>{{ __('backend::booking.ops_decided_by') }} {{ $who($r->decided_by) }} · {{ $vn($r->decided_at) }}@if($r->decision_note) · {{ $r->decision_note }}@endif @endif
                            @if($r->processed_by)<br>{{ __('backend::booking.ops_processed_by') }} {{ $who($r->processed_by) }} · {{ $vn($r->processed_at) }} · {{ $r->provider_reference }}@endif
                        </td>
                        <td style="min-width: 220px">
                            @if($ops['isFinance'] && $r->status === 'requested' && $r->requested_by !== $ops['userId'])
                                <form method="post" action="{{ route('backend.booking.refundDecision', ['refundId' => $r->id]) }}" class="mb-1">
                                    @csrf
                                    <input type="text" name="note" class="form-control form-control-sm mb-1" placeholder="{{ __('backend::booking.ops_decision_note') }}">
                                    <button name="decision" value="approve" class="btn btn-success btn-sm">{{ __('backend::booking.ops_approve') }}</button>
                                    <button name="decision" value="reject" class="btn btn-outline-danger btn-sm">{{ __('backend::booking.ops_reject') }}</button>
                                </form>
                            @elseif($r->status === 'requested')
                                <span class="small text-muted">{{ __('backend::booking.ops_waiting_finance') }}</span>
                            @endif
                            @if($ops['isFinance'] && $r->status === 'approved')
                                <form method="post" action="{{ route('backend.booking.refundProcessed', ['refundId' => $r->id]) }}">
                                    @csrf
                                    <input type="text" name="provider_reference" required class="form-control form-control-sm mb-1" placeholder="{{ __('backend::booking.ops_provider_reference') }}">
                                    <button class="btn btn-primary btn-sm">{{ __('backend::booking.ops_mark_processed') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

        @if($ops['refundable'] > 0)
            <form method="post" action="{{ route('backend.booking.refundRequest', ['id' => $booking->id]) }}" class="border rounded p-3 bg-light">
                @csrf
                <h4 class="h6">{{ __('backend::booking.ops_refund_request') }}</h4>
                <div class="form-row">
                    <div class="col-md-4 mb-2">
                        <label class="small">{{ __('backend::booking.ops_refund_reason') }}</label>
                        <select name="reason" class="form-control form-control-sm" id="refund-reason">
                            @foreach($ops['suggestions'] as $reason => $s)
                                <option value="{{ $reason }}" data-suggested="{{ $s['amount'] === null ? '' : $major($s['amount']) }}" data-basis="{{ $s['basis'] }}">{{ __('backend::booking.refund_reason_' . $reason) }}</option>
                            @endforeach
                        </select>
                        <div class="small text-muted mt-1" id="refund-basis"></div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small">{{ __('backend::booking.ops_refund_amount') }} ({{ strtoupper((string) $booking->currency) }}, ≤ {{ $money($ops['refundable']) }})</label>
                        <input type="number" step="{{ strtolower((string) $booking->currency) === 'usd' ? '0.01' : '1' }}" min="0" name="amount" id="refund-amount" required class="form-control form-control-sm" value="{{ old('amount') }}">
                    </div>
                    <div class="col-md-5 mb-2">
                        <label class="small">{{ __('backend::booking.ops_refund_note') }}</label>
                        <input type="text" name="note" class="form-control form-control-sm" value="{{ old('note') }}">
                    </div>
                </div>
                <button class="btn btn-warning btn-sm">{{ __('backend::booking.ops_refund_submit') }}</button>
            </form>
        @endif

        <h3 class="h6 mt-4 border-top pt-3">{{ __('backend::booking.ops_audit') }}</h3>
        @forelse($ops['audit'] as $a)
            <div class="small">{{ $vn($a->created_at) }} · {{ $who($a->user_id) }} · <code>{{ $a->action }}</code> @if($a->data)<span class="text-muted">{{ \Illuminate\Support\Str::limit($a->data, 160) }}</span>@endif</div>
        @empty
            <p class="small text-muted">—</p>
        @endforelse
    </div>
</div>

<script>
    (function () {
        var select = document.getElementById('refund-reason');
        if (!select) return;
        var fill = function () {
            var o = select.options[select.selectedIndex];
            document.getElementById('refund-basis').textContent = o.dataset.basis || '';
            if (o.dataset.suggested !== '') document.getElementById('refund-amount').value = o.dataset.suggested;
        };
        select.addEventListener('change', fill);
        fill();
    })();
</script>
