class ModalShow {
    constructor(modal) {
        this.modal = modal;
        this.listFile = $('.list-file');
        this.lblName = $('.lbl-name', modal);
        this.lblType = $('.lbl-type', modal);
        this.lblLink = $('.lbl-link a', modal);
        this.lblSize = $('.lbl-size', modal);
        this.pnlFileTypeImage = $('.file-type-image', modal);
        this.pnlFileTypeAudio = $('.file-type-audio', modal);
        this.pnlFileTypeVideo = $('.file-type-video', modal);
        this.pnlFileTypeOther = $('.file-type-other', modal);
        this.btnDelete = $('.btn-delete-one', modal);
        this.btnClose = $('.close', modal);
    }

    init() {
        this.bindEvent();
    }

    bindEvent() {
        let self = this;

        let modal = new coreui.Modal(document.getElementById(this.modal.attr('id')));

        this.listFile.on('click', '.item', function (e) {
            e.preventDefault();

            let obj = JSON.parse($(this).attr('data-obj'));

            self.lblName.text(obj.name);
            self.lblLink.attr('href', obj.link_full);
            self.lblLink.text(obj.link_full);
            self.lblType.text(obj.type_name);
            self.lblSize.text(obj.size_name);
            self.btnDelete.attr('data-id', obj.id);

            switch (obj.type) {
                case fileType['image']:
                    self.pnlFileTypeImage.show();
                    $('a', self.pnlFileTypeImage).attr('href', obj.link_full);
                    $('img', self.pnlFileTypeImage).attr('src', obj.link_full);
                    break;
                case fileType['audio']:
                    self.pnlFileTypeAudio.show();
                    $('audio', self.pnlFileTypeAudio).attr('src', obj.link_full);
                    break;
                case fileType['video']:
                    self.pnlFileTypeVideo.show();
                    $('video', self.pnlFileTypeVideo).attr('src', obj.link_full);
                    break;
                default:
                    self.pnlFileTypeOther.show();
                    break;
            }

            modal.show();
        });

        document.getElementById(this.modal.attr('id')).addEventListener('hidden.coreui.modal', function (e) {
            self.lblName.text('');
            self.lblLink.attr('href', '#');
            self.lblLink.text('');
            self.lblType.text('');
            self.lblSize.text('');

            self.pnlFileTypeImage.hide();
            $('img', self.pnlFileTypeImage).attr('src', '');
            self.pnlFileTypeAudio.hide();
            $('audio', self.pnlFileTypeAudio).attr('src', '');
            self.pnlFileTypeVideo.hide();
            $('video', self.pnlFileTypeVideo).attr('src', '');
            self.pnlFileTypeOther.hide();
            self.btnDelete.removeAttr('data-id');
        });
    }
}

$(document).ready(function () {
    let modalShow = new ModalShow($('#modal-show'));
    modalShow.init();
});