<div id="modal-gallery-image-select" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-primary modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="h4 mb-0 modal-title">Chọn ảnh</h2>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body p-0">
                <div class="embed-responsive" style="height: 80vh">
                    <iframe id="iframe-gallery-image-select"
                            src="{{ route('backend.file.index', ['layout' => 'popup', 'isMultiSelect' => 'true', 'type' => sprintf("%d,%d", config('backend.fileType.image'), config('backend.fileType.video')), 'callback' => 'selectGalleryImageCallBack']) }}"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>