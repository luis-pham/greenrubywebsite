{{--
    Japanese booking cancellation (i18n phase 2B). STATUS: DRAFT, PENDING_REVIEW — native Japanese review required.
    Sent only when 'ja' is in config i18n.mail.locales. Blocks marked data-review="LEGAL_REVIEW_REQUIRED" need legal review.
    No cancellation charge or refund amount is stated here: they follow the Booking Terms (English), linked below.
--}}
@php
    $mailFmt = \App\Services\Mail\BookingMailLocale::class;
@endphp
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ご予約キャンセルのご確認</title>
    <style>
        body { font-family: "Hiragino Kaku Gothic ProN", "Hiragino Sans", Meiryo, Arial, sans-serif; line-height: 1.8; color: #333; margin: 20px; }
        .section-title { font-weight: bold; margin-top: 24px; margin-bottom: 12px; font-size: 14px; color: #d9534f; }
        .section { margin-bottom: 8px; }
        .section p { margin: 6px 0; }
        .section .label { font-weight: bold; }
        hr { margin: 24px 0; border: none; border-top: 1px solid #ccc; }
        .list-item { margin: 8px 0 8px 16px; }
    </style>
</head>
<body>

<p>{{ $booking->full_name }} 様</p>

{{-- LEGAL_REVIEW_REQUIRED [cancellation.statement]: confirms the cancellation "at the guest's request", as the English e-mail does. --}}
<div data-review="LEGAL_REVIEW_REQUIRED" data-review-id="cancellation.statement">
<p>ご依頼に基づき、下記のご予約（予約番号：<strong>{{ $booking->code }}</strong>）のキャンセル手続きが完了しましたので、お知らせいたします。</p>
</div>
{{-- /LEGAL_REVIEW_REQUIRED [cancellation.statement] --}}

<hr>

<p class="section-title">ご予約内容</p>
<div class="section">
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

{{-- LEGAL_REVIEW_REQUIRED [cancellation.payment_and_refund]: payment status and where the refund rules are; no amount or rule is stated. --}}
<div data-review="LEGAL_REVIEW_REQUIRED" data-review-id="cancellation.payment_and_refund">
<p class="section-title">お支払い情報</p>
<div class="section">
    <p>ご予約の合計金額：{{ $mailFmt::money($booking->total_amount, $booking->currency) }}</p>
    <p>お支払い状況：キャンセル済み</p>
    <p>キャンセル料および返金の条件は、ご予約条件に定めるとおりです。ご予約条件は現在、英語版のみご用意しております。</p>
    <p class="list-item">・<a href="{{ $termsUrl }}">ご予約条件（英語）：キャンセル</a></p>
    <p class="list-item">・<a href="{{ $refundTermsUrl }}">ご予約条件（英語）：返金</a></p>
</div>
</div>
{{-- /LEGAL_REVIEW_REQUIRED [cancellation.payment_and_refund] --}}

<hr>

<p>キャンセルについてご不明な点がある場合や、改めてご予約をご希望の場合は、お気軽にお問い合わせください。またのご利用を心よりお待ちしております。</p>

<hr>

<p style="margin-top: 12px;">{{ $companyName }}<br>
予約担当チーム<br>
電話：{{ $hotline }}<br>
メール：{{ $supportEmail }}<br>
ウェブサイト：{{ $website }}</p>

</body>
</html>
