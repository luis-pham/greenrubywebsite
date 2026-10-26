<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $heading }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; max-width: 640px; margin: 0 auto; padding: 20px; }
        .facts { border-collapse: collapse; margin: 16px 0; }
        .facts td { padding: 6px 12px 6px 0; vertical-align: top; }
        .facts td:first-child { color: #666; }
        .button { display: inline-block; margin: 12px 0 20px; padding: 12px 22px; background: #13392f; color: #ffffff !important; text-decoration: none; font-weight: bold; }
        .small { color: #666; font-size: 12px; }
    </style>
</head>
<body>
@if($greeting)
    <p>{{ $greeting }}</p>
@endif
@foreach($paragraphs as $paragraph)
    <p>{{ $paragraph }}</p>
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

<p style="margin-top: 20px;">{{ $signature }}<br>
{{ $companyName }}<br>
@if($hotline)Hotline: {{ $hotline }}<br>@endif
@if($supportEmail)Email: {{ $supportEmail }}@endif</p>
</body>
</html>
