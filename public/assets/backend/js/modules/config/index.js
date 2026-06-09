var modalGalleryImageSelectId = 'modal-gallery-image-select';
var modalGalleryImageSelect = new coreui.Modal(document.getElementById(modalGalleryImageSelectId));
document.getElementById(modalGalleryImageSelectId).addEventListener('hidden.coreui.modal', function () {
    $('.gallery.active').removeClass('active');
});

var modalGalleryImageEditId = 'modal-gallery-image-edit';
var modalGalleryImageEdit = new coreui.Modal(document.getElementById(modalGalleryImageEditId));
document.getElementById(modalGalleryImageEditId).addEventListener('hidden.coreui.modal', function () {
    $('.gallery.active').removeClass('active');

    let modal = $(this);
    $('[name="title"]', modal).val('');
    $('[name="description"]', modal).val('');
    $('.btn-save', modal).removeAttr('data-idx');
});

class GalleryImage {
    constructor(key) {
        this.gallery = $('.gallery[key="' + key + '"]');
        this.listImage = $('.list-image', this.gallery);
        this.btnOpenModalSelect = $('.btn-open-modal-select', this.listImage);
        this.input = $('input[type="hidden"]', this.gallery);
    }

    init() {
        this.bindEvent();
    }

    bindEvent() {
        let self = this;

        self.listImage.sortable({
            handle: '.box-dragdrop',
            connectWith: '#' + self.listImage.attr('id'),
            zIndex: 99999,
            forcePlaceholderSize: true,
            opacity: 0.8,
            update: function () {
                self.updateValue();
            }
        });

        self.listImage.on('click', '.btn-edit', function (e) {
            e.preventDefault();

            let item = $(this).closest('.item');
            let obj = $(item).attr('data-obj');
            if (!Validate.prototype.isNullOrWhiteSpace(obj)) {
                obj = JSON.parse(obj);

                let idx = $(item).index();
                let image = $('img', item);
                let linkFull = image.attr('src');

                let modal = $('#' + modalGalleryImageEditId);
                let pnlFileTypeImage = $('.file-type-image', modal);
                $('a', pnlFileTypeImage).attr('href', linkFull);
                $('img', pnlFileTypeImage).attr('src', linkFull);
                $('[name="title"]', modal).val(obj.title);
                $('[name="description"]', modal).val(obj.description);
                $('.btn-save', modal).attr('data-idx', idx);
            }
            
            self.gallery.addClass('active');
            modalGalleryImageEdit.toggle();
        });

        self.listImage.on('click', '.btn-delete', function (e) {
            e.preventDefault();

            if (confirm('Bạn có chắc muốn xóa ảnh này?')) {
                $(this).closest('.item').remove();
                self.updateValue();
            }
        });

        self.btnOpenModalSelect.on('click', function (e) {
            e.preventDefault();
            
            self.gallery.addClass('active');
            modalGalleryImageSelect.toggle();
        });
    }

    updateValue() {
        let value = [];
        $('.item', this.listImage).each(function () {
            let obj = $(this).attr('data-obj');
            if (!Validate.prototype.isNullOrWhiteSpace(obj)) {
                value.push(JSON.parse(obj));
            }
        });
        this.input.val(JSON.stringify(value));
    }
}

function selectGalleryImageCallBack(list) {
    if (!Array.isArray(list)) {
        list = [list];
    }

    console.log(list);
    
    for (let i = 0; i < list.length; i++) {
        let obj = list[i];

        let dataObj = {
            id: obj.id,
            title: obj.name,
            link: obj.link,
            thumbnail: obj.thumbnail,
            extension: obj.extension,
            description: obj.description,
            is_360: obj.is_360
        };

        let gallery = $('.gallery.active');
        let input = $('input[type="hidden"]', gallery);
        let value = input.val();
        if (Validate.prototype.isNullOrWhiteSpace(value)) {
            value = '[]';
        }
        value = JSON.parse(value);
        value.push(dataObj);
        input.val(JSON.stringify(value));

        let template =
            '<div class="item col-4 col-lg-3" data-obj="{{ dataObj }}">' +
                '<div class="box-dragdrop position-relative ui-sortable-handle">' +
                    '<div class="image-wrapper position-relative">' +
                        '<a href="{{ link_full }}" data-fancybox="gallery-{{ key }}">' +
                            '<img src="{{ thumbnail_full }}" alt="{{ title }}" class="position-absolute w-100 h-100">' +
                        '</a>' +
                        '<div class="action position-absolute">' +
                            '<a href="#" class="btn-edit btn btn-info btn-sm">' +
                                '<i class="fas fa-pencil-alt"></i>' +
                            '</a>\n' +
                            '<a href="#" class="btn-delete btn btn-danger btn-sm">' +
                                '<i class="fas fa-trash-alt"></i>' +
                            '</a>' +
                        '</div>' +
                    '</div>' +
                    '<div class="name position-absolute w-100 text-center">' +
                        '<span class="give-ellipsis after-2-lines">{{ title }}</span>' +
                    '</div>' +
                '</div>' +
            '</div>';
        let html = $.mustache(template, {
            dataObj: JSON.stringify(dataObj),
            key: gallery.attr('key'),
            link_full: obj.link_full,
            thumbnail_full: Validate.prototype.isNullOrWhiteSpace(obj.thumbnail)
                ? obj.link_full
                : obj.thumbnail_full,
            title: dataObj.title
        });

        let listImage = $('.list-image', gallery);
        $('.item:last-child', listImage).before(html);
    }
    
    modalGalleryImageSelect.toggle();
}

$(document).ready(function () {
    $('.text-editor').textEditor({
        menubar: false,
        toolbar: [
            'bold italic underline strikethrough | backcolor forecolor | link unlink | removeformat | charmap | superscript subscript | code'
        ],
        contextmenu: 'cut copy paste',
        mobile: {
            toolbar: [
                'bold italic underline strikethrough | link unlink | removeformat'
            ],
        }
    });

    let modal = $('#' + modalGalleryImageEditId);
    $('.btn-save', modal).click(function (e) {
        e.preventDefault();

        let txtTitle = $('[name="title"]', modal);
        let txtDescription = $('[name="description"]', modal);

        if (Validate.prototype.isNullOrWhiteSpace(txtTitle.val())) {
            txtTitle.focus();
            alert('Bạn phải nhập Tiêu đề!');
            return;
        }

        let gallery = $('.gallery.active');
        let input = $('input[type="hidden"]', gallery);
        let value = input.val();
        if (Validate.prototype.isNullOrWhiteSpace(value)) {
            value = '[]';
        }
        value = JSON.parse(value);

        let idx = $(this).attr('data-idx');
        if (value[idx] == undefined) {
            return;
        }
        
        value[idx].title = txtTitle.val(),
        value[idx].description = txtDescription.val();
        input.val(JSON.stringify(value));

        let item = $('.item:eq(' + idx + ')', gallery);
        $(item).attr('data-obj', JSON.stringify(value[idx]));
        $('.name', item).text(value[idx].title);

        modalGalleryImageEdit.hide();
    });
});