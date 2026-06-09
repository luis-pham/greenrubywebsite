<script src="{{ asset('/assets/backend/plugins/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('/assets/backend/plugins/tinymce/jquery.tinymce.min.js') }}"></script>
<script type="text/javascript">
    const uploadUrl = '{{ route('backend.file.index', ['layout' => 'popup']) }}';
    const fileType = {!! json_encode(config('backend.fileType'), JSON_UNESCAPED_UNICODE) !!};
</script>
<script src="{{ asset('/assets/backend/plugins/tinymce/texteditor.js?v=1.0.11') }}"></script>