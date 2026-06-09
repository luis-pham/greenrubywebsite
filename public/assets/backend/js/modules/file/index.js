class ModalUpload {
    constructor(modal) {
        this.modal = modal;
        this.btnClose = $('.close', modal);
        this.frmUpload = $('#frm-upload', modal);
    }

    init() {
        this.bindEvent();
    }

    getModal() {
        return new coreui.Modal(document.getElementById(this.modal.attr('id')), {
            backdrop: 'static',
            keyboard: false
        });
    }

    bindEvent() {
        let self = this;

        let dropzone = this.frmUpload.dropzone({
            url: fileUrl.store,
            paramName: 'file',
            uploadMultiple: true,
            parallelUploads: fileConfig.maxFileUpload,
            maxFiles: fileConfig.maxFileUpload,
            maxFilesize: fileConfig.maxFileSize,
            acceptedFiles: fileConfig.fileAllowUpload,
            init: function () {
                let success = true;

                this.on('sendingmultiple', function (file, xhr, formData) {
                    self.btnClose.removeAttr('data-dismiss');
                    self.btnClose.prop('disabled', true);
                    formData.append('_token', $('input[name^=_token]').first().val());
                });

                this.on('successmultiple', function (file, response) {
                    for (let i = 0; i < file.length; i++) {
                        for (let j = 0; j < response.data.error.length; j++) {
                            if (response.data.error[j].idx == i) {
                                let element = $(file[i].previewElement);
                                element.removeClass('dz-processing dz-success');
                                element.addClass('dz-error');
                                $('.dz-error-message', element).addClass('text-center').text('Lỗi hệ thống!');
                            }
                        }
                    }

                    success = response.msg == 'success';

                    if (response.data.success.length > 0) {
                        self.modal.attr('data-reload', 'true');
                    }

                    self.btnClose.attr('data-dismiss', 'modal');
                    self.btnClose.prop('disabled', false);
                });

                this.on('errormultiple', function (file, response) {
                    success = false;
                });

                this.on('completemultiple', function (file) {
                    if (this.getUploadingFiles().length == 0 && this.getQueuedFiles().length == 0) {
                        if (self.modal.attr('data-show-flash-message') == 'true')
                        {
                            if (!success) {
                                UserInterface.prototype.showFlashMessageError('Upload file lỗi!');
                            } else {
                                UserInterface.prototype.showFlashMessageInfo('Upload file thành công!');
                            }

                            self.modal.removeAttr('data-show-flash-message');
                        }
                    }
                });
            }
        });

        document.getElementById(this.modal.attr('id')).addEventListener('shown.coreui.modal', function (e) {
            self.modal.attr('data-show-flash-message', 'true');
        });

        document.getElementById(this.modal.attr('id')).addEventListener('hidden.coreui.modal', function (e) {
            if ($(this).attr('data-reload') == 'true') {
                window.location.reload();
            } else {
                dropzone[0].dropzone.removeAllFiles();
            }
        });
    }
}

$(document).ready(function () {
    let modalUpload = new ModalUpload($('#modal-upload'));
    modalUpload.init();

    $('#btn-upload').on('click', function (e) {
        e.preventDefault();

        let modal = modalUpload.getModal();
        modal.toggle();
    });
});