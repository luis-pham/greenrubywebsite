<div class="modal fade" id="modal-confirm-delete-image" tabindex="-1" role="dialog" aria-labelledby="modalConfirmDeleteImageLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title font-weight-bold" id="modalConfirmDeleteImageLabel">
                    <i class="fas fa-exclamation-triangle text-warning mr-2"></i> Xác nhận xóa
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center py-3">
                <p class="mb-0">Bạn có chắc muốn xóa ảnh này không?</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Hủy
                </button>
                <button type="button" class="btn btn-danger btn-sm px-4" id="btn-confirm-delete-image">
                    <i class="fas fa-trash-alt mr-1"></i> Xóa
                </button>
            </div>
        </div>
    </div>
</div>

<script>
window.confirmImageDelete = function (callback) {
    var $modal = $('#modal-confirm-delete-image');
    $modal.modal('show');
    $modal.off('click.confirmImageDelete').one('click.confirmImageDelete', '#btn-confirm-delete-image', function () {
        $modal.modal('hide');
        callback();
    });
};
</script>
