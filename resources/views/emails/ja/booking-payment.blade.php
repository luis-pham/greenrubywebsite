{{--
    Japanese payment messages (i18n phase 2B; copy in app/Mail/Copy/ja.php). STATUS: DRAFT, PENDING_REVIEW.
    Sent only when 'ja' is in config i18n.mail.locales. Paragraphs flagged in the copy are wrapped in a
    data-review="LEGAL_REVIEW_REQUIRED" block carrying their id.
--}}
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $heading }}</title>
    <style>
        body { font-family: "Hiragino Kaku Gothic ProN", "Hiragino Sans", Meiryo, Arial, sans-serif; color: #333; line-height: 1.8; max-width: 640px; margin: 0 auto; padding: 20px; }
        .facts { border-collapse: collapse; margin: 16px 0; }
        .facts td { padding: 6px 12px 6px 0; vertical-align: top; }
        .facts td:first-child { color: #666; }
        .button { display: inline-block; margin: 12px 0 20px; padding: 12px 22px; background: #13392f; color: #ffffff !important; text-decoration: none; font-weight: bold; }
        .small { color: #666; font-size: 12px; }
    </style>
</head>
<body>
<p>{{ $greeting }}</p>
@foreach($paragraphs as $paragraph)
@if($paragraph['review'])
    <div data-review="{{ $paragraph['review'] }}" data-review-id="{{ $paragraph['id'] }}"><p>{{ $paragraph['text'] }}</p></div>
@else
    <p>{{ $paragraph['text'] }}</p>
@endif
@endforeach

@if($facts)
    <table class="facts">
        @foreach($facts as [$label, $value])
            <tr><td>{{ $label }}</td><td><strong>{{ $value }}</strong></td></tr>
        @endforeach
    </table>
@endif

@if($button && $link)
    <p><a class="button" href="{{ $link }}">{{ $button }}</a></p>
    <p class="small">{{ $link }}</p>
@endif

@if($termsUrl)
    {{-- LEGAL_REVIEW_REQUIRED [payment.terms_link]: the governing terms are the English Booking Terms. --}}
    <div data-review="LEGAL_REVIEW_REQUIRED" data-review-id="payment.terms_link"><p class="small">{{ $termsNote }}<br><a href="{{ $termsUrl }}">{{ $termsLabel }}</a></p></div>
@endif

<p style="margin-top: 20px;">{{ $companyName }}<br>
{{ $signature }}<br>
@if($hotline)電話：{{ $hotline }}<br>@endif
@if($supportEmail)メール：{{ $supportEmail }}@endif</p>
</body>
</html>
