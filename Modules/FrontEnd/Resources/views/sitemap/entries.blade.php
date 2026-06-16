@include('frontend::sitemap.partials.urlset-open')
@foreach ($entries as $entry)
    @include('frontend::sitemap.partials.url-entry', ['entry' => $entry])
@endforeach
@include('frontend::sitemap.partials.urlset-close')
