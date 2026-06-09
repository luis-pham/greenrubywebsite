@php
    $modalId = isset($modalId) ? $modalId : 'room-modal';
    $title = isset($title) ? $title : 'Thêm phòng bên trong';
    $nameInputId = isset($nameInputId) ? $nameInputId : 'new-room-name';
    $descriptionInputId = isset($descriptionInputId) ? $descriptionInputId : 'new-room-description';
    $addButtonClass = isset($addButtonClass) ? $addButtonClass : 'btn-add-new-room';
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $title }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Tên phòng</label>
                    <input type="text" id="{{ $nameInputId }}" class="form-control" maxlength="50" autocomplete="off">
                </div>
                <div class="form-group">
                    <label>Mô tả</label>
                    <input type="text" id="{{ $descriptionInputId }}" class="form-control" maxlength="200" autocomplete="off">
                </div>
                <button type="button" class="btn btn-success {{ $addButtonClass }}">Thêm phòng</button>
            </div>
        </div>
    </div>
</div>
