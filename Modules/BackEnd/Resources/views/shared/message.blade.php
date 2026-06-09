@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <p class="h4 alert-heading">Có lỗi xảy ra:</p>
        @foreach ($errors->all() as $error)
            <p class="mb-0">{{ $error }}</p>
        @endforeach
    </div>
@endif

@if (Session::get('flash-message'))
    <div class="alert alert-success" role="alert">{{ Session::get('flash-message') }}</div>
@endif