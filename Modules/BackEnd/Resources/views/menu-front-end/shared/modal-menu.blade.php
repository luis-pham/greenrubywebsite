<div class="modal fade" id="modal-menu" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <p class="modal-title h5 mb-0">Sửa menu</p>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <label class="col-md-4 col-form-label">Tên <span class="text-danger">*</span></label>
                    <div class="col-md-8">
                        <input type="text" name="name" class="form-control" autocomplete="off" />
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-4 col-form-label">Link <span class="text-danger">*</span></label>
                    <div class="col-md-8">
                        <input type="text" name="url" class="form-control" autocomplete="off" />
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-4 col-form-label">Biểu tượng</label>
                    <div class="col-md-8">
                        <input type="text" name="icon" class="form-control" autocomplete="off" />
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-4 col-form-label">Mở tab mới <span class="text-danger">*</span></label>
                    <div class="col-md-8">
                        <select name="target" class="form-control">
                            <option value="_self">Không</option>
                            <option value="_blank">Có</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="offset-md-4 col-md-8">
                        <button type="button" class="btn btn-primary btn-save">
                            <i class="fas fa-save"></i> Lưu lại
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> <span class="modal-btn-title">Thoát</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>