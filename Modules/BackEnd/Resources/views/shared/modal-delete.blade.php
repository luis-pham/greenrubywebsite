<div class="modal fade" id="{{ $modalId ?? 'deleteModal' }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body d-flex flex-column align-items-center justify-content-center py-4">
                <i class="fas fa-exclamation-triangle fa-2x mb-2 text-danger"></i>
                <h4 class="mb-0 font-weight-bold mt-2">{{ $title ?? 'Xóa hoạt động này?' }}</h4>
                <p class="mt-2 text-center">
                    {!! $message ?? 'sẽ bị gỡ khỏi danh sách hiển thị' !!}
                </p>
                <div class="d-flex justify-content-center mt-2">
                    <button type="button" class="btn btn-secondary px-4 mr-2" data-dismiss="modal" style="min-width: 120px;">
                        Hủy
                    </button>
                    <button type="button" class="btn btn-danger px-4 confirm-delete-btn" style="min-width: 120px;">
                        Xác nhận xóa
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
    
<style>
    .modal-body i {
        background-color: rgb(249 232 232);
        padding: 15px;
        border-radius: 50%;
    }
</style>

<script>
(function() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initModalDelete{{ Str::studly($modalId ?? 'deleteModal') }});
    } else {
        initModalDelete{{ Str::studly($modalId ?? 'deleteModal') }}();
    }
    
    function initModalDelete{{ Str::studly($modalId ?? 'deleteModal') }}() {
        const modalId = '{{ $modalId ?? "deleteModal" }}';
        const $modal = $('#' + modalId);
        let deleteData = {};
        
        $(document).on('click', '[data-modal-delete="' + modalId + '"]', function(e) {
            e.preventDefault();
            
            deleteData = { 
                id: $(this).data('id'), 
                url: $(this).data('ajax-url'),
                name: $(this).data('name')
            };
            
            const $itemName = $modal.find('#delete-item-name');
            if ($itemName.length && deleteData.name) {
                $itemName.text('"' + deleteData.name + '"');
            }
            
            $modal.modal('show');
        });
        
        $modal.find('.confirm-delete-btn').click(function() {
            if (!deleteData.url || !deleteData.id) return;
            
            $.ajax({
                type: 'POST',
                url: deleteData.url,
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}',
                    id: deleteData.id
                },
                success: function(response) {
                    $modal.modal('hide');
                    if (response && response.status === 'success') {
                        location.reload();
                    } else {
                        location.reload(); 
                    }
                },
                error: function(xhr) {
                    $modal.modal('hide');
                    console.error('Delete error:', xhr);
                    if (typeof swalAlert !== 'undefined') {
                        swalAlert.error('Có lỗi xảy ra khi xóa!');
                    }
                }
            });
        });
        
        $modal.on('hidden.bs.modal', function() {
            deleteData = {};
        });
    }
})();
</script>

