{{-- Site status of a language (phase 1E-a): disabled / editing / public. $status: one of LocaleRegistry::STATUSES. --}}
@php $labels = ['disabled' => ['Tắt', 'secondary'], 'editing' => ['Đang biên tập', 'warning'], 'public' => ['Công khai', 'success']]; @endphp
<span class="badge badge-{{ $labels[$status][1] ?? 'light' }}" title="site_status: {{ $status }}">{{ $labels[$status][0] ?? $status }}</span>
