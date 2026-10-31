{{--
    Japanese booking confirmation (i18n phase 2B). STATUS: DRAFT, PENDING_REVIEW — native Japanese review required.
    Sent only when 'ja' is in config i18n.mail.locales. Blocks marked data-review="LEGAL_REVIEW_REQUIRED" need legal review.
    No cancellation or refund rule is written here: the policy lives in the Booking Terms (English), linked below.
--}}
@php
    $mailFmt = \App\Services\Mail\BookingMailLocale::class;
@endphp
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ご予約内容のご確認</title>
    <style>
        body { font-family: "Hiragino Kaku Gothic ProN", "Hiragino Sans", Meiryo, Arial, sans-serif; line-height: 1.8; color: #333; margin: 20px; }
        .section-title { font-weight: bold; margin-top: 24px; margin-bottom: 12px; font-size: 14px; }
        .section { margin-bottom: 8px; }
        .section p { margin: 6px 0; }
        .section .label { font-weight: bold; }
        hr { margin: 24px 0; border: none; border-top: 1px solid #ccc; }
        .list-item { margin: 8px 0 8px 16px; }
    </style>
</head>
<body>

<p>{{ $booking->full_name }} 様</p>

<p>このたびはGreen Ruby Cruisesをお選びいただき、誠にありがとうございます。以下の内容でご予約を承りました。</p>

<hr>

<p class="section-title">ご予約内容</p>
<div class="section">
    <p>予約番号：{{ $booking->code }}</p>
    <p>行程：{{ $booking->itinerary_name ?? '—' }}</p>
    <p>クルーズ船：{{ $booking->cruise_name ?? '—' }}</p>
    <p>出発日：{{ $booking->departure_date ? $mailFmt::date($booking->departure_date) : '—' }}</p>
    <p>人数：{{ $booking->guests_total ?? 0 }}名</p>
</div>

<hr>

<p class="section-title">客室</p>
<div class="section">
@if($booking->cabins && $booking->cabins->count() > 0)
@foreach($booking->cabins as $cabin)
    @php
        $parts = [];
        if ($cabin->adults > 0) $parts[] = '大人' . $cabin->adults . '名';
        if ($cabin->children_6_12 > 0) $parts[] = '子ども（6〜12歳）' . $cabin->children_6_12 . '名';
        if ($cabin->children_2_5 > 0) $parts[] = '子ども（2〜5歳）' . $cabin->children_2_5 . '名';
        if ($cabin->infants > 0) $parts[] = '乳幼児' . $cabin->infants . '名';
    @endphp
    <p><span class="label">客室タイプ：</span>{{ $cabin->cabin_name }}</p>
    <p>人数：{{ implode('、', $parts) ?: '—' }}</p>
    <p>客室料金：{{ $mailFmt::money($cabin->total_price, $booking->currency) }}</p>
    <p style="margin-bottom: 16px;"></p>
@endforeach
@else
    <p>客室の指定はありません。</p>
@endif
</div>

<hr>

<p class="section-title">オプション</p>
<div class="section">
@if($booking->amenities && $booking->amenities->count() > 0)
@foreach($booking->amenities as $amenity)
    @php
        $amenityQty = max((int) ($amenity->quantity ?? 1), 1);
        $amenityLineTotal = ($amenity->unit_price ?? 0) * $amenityQty;
    @endphp
    <p><span class="label">{{ $amenity->amenity_name }}</span></p>
    <p>数量：{{ $amenityQty }}</p>
    <p>料金：{{ $mailFmt::money($amenityLineTotal, $booking->currency) }}</p>
    <p style="margin-bottom: 12px;"></p>
@endforeach
@else
    <p>なし</p>
@endif
</div>

<hr>

{{-- LEGAL_REVIEW_REQUIRED [confirmation.payment]: amounts, deposit/balance wording, payment method, balance link timing. COMMERCIAL_REVIEW_REQUIRED: USD display (US$). --}}
<div data-review="LEGAL_REVIEW_REQUIRED" data-review-id="confirmation.payment">
<p class="section-title">お支払い情報</p>
<div class="section">
    <p>合計金額：{{ $mailFmt::money($booking->total_amount, $booking->currency) }}</p>
    @if (($booking->payment_plan ?? null) === 'deposit')
        @if ($booking->balanceAmount() > 0)
            <p>お支払い済みの予約金：{{ $mailFmt::money($booking->amount_paid, $booking->currency) }}</p>
            <p>残金：{{ $mailFmt::money($booking->balanceAmount(), $booking->currency) }}@if ($booking->balance_due_date)（お支払い期日：{{ $mailFmt::date($booking->balance_due_date) }}）@endif</p>
            <p>残金のお支払い用の安全なリンクは、ご出発の約{{ $balanceRequestDays }}日前にメールでお送りします。</p>
        @else
            <p>お支払い済み：{{ $mailFmt::money($booking->amount_paid, $booking->currency) }}（全額）</p>
        @endif
    @endif
    <p>お支払い方法：</p>
    <p class="list-item">・{{ $paymentMethodLabel }}</p>
</div>
</div>
{{-- /LEGAL_REVIEW_REQUIRED [confirmation.payment] --}}

<hr>

{{-- LEGAL_REVIEW_REQUIRED [confirmation.cancellation]: no policy text here on purpose; it links to the Booking Terms (English). --}}
<div data-review="LEGAL_REVIEW_REQUIRED" data-review-id="confirmation.cancellation">
<p class="section-title">キャンセルポリシー</p>
<div class="section">
    <p>キャンセル料および返金の条件は、ご予約条件の「Cancellation by Guests」「Refunds」の項に定めています。ご予約条件は現在、英語版のみご用意しております。</p>
    <p class="list-item">・<a href="{{ $termsUrl }}">ご予約条件（英語）：キャンセル</a></p>
    <p class="list-item">・<a href="{{ $refundTermsUrl }}">ご予約条件（英語）：返金</a></p>
</div>
</div>
{{-- /LEGAL_REVIEW_REQUIRED [confirmation.cancellation] --}}

<p style="margin-top: 20px;">ご要望やご不明な点がございましたら、お気軽にお問い合わせください。</p>

<p>皆様のご乗船を心よりお待ちしております。</p>

<hr>

<p style="margin-top: 12px;">{{ $companyName }}<br>
予約担当チーム<br>
電話：{{ $hotline }}<br>
メール：{{ $supportEmail }}<br>
ウェブサイト：{{ $website }}</p>

</body>
</html>
