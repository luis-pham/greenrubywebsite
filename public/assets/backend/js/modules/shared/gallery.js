var galleryModalSelectId = 'modal-gallery-image-select';
var galleryModalSelect = null;
var galleryModalElement = document.getElementById(galleryModalSelectId);

if (galleryModalElement && typeof coreui !== 'undefined' && coreui.Modal) {
    galleryModalElement.style.display = 'none';
    galleryModalSelect = new coreui.Modal(galleryModalElement);
    galleryModalElement.addEventListener('hidden.coreui.modal', function () {
        $('.gallery.active').removeClass('active');
    });
}

class GalleryImage {
    constructor(key) {
        this.key = key;
        this.gallery = $('.gallery[key="' + key + '"]');
        this.listImage = $('.list-image', this.gallery);
        this.btnOpenModalSelect = $('.btn-open-modal-select', this.listImage);
        this.input = $('input[type="hidden"]', this.gallery);
    }

    init() {
        if (!this.gallery.length) { return; }
        this.bindEvent();
    }

    bindEvent() {
        var self = this;

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

        self.listImage.on('click', '.btn-delete', function (e) {
            e.preventDefault();
            var $item = $(this).closest('.item');
            if (typeof window.confirmImageDelete === 'function') {
                window.confirmImageDelete(function () {
                    $item.remove();
                    self.updateValue();
                });
            } else if (confirm('Bạn có chắc muốn xóa ảnh này?')) {
                $item.remove();
                self.updateValue();
            }
        });

        self.btnOpenModalSelect.on('click', function (e) {
            e.preventDefault();
            self.gallery.addClass('active');
            updateIframeExcludeId();
            galleryModalSelect.toggle();
        });
    }

    updateValue() {
        var value = [];
        $('.item', this.listImage).each(function () {
            var obj = $(this).attr('data-obj');
            if (!Validate.prototype.isNullOrWhiteSpace(obj)) {
                value.push(JSON.parse(obj));
            }
        });
        this.input.val(JSON.stringify(value));
        updateIframeExcludeId();
    }
}

function selectGalleryImageCallBack(list) {
    if (!Array.isArray(list)) {
        list = [list];
    }

    for (var i = 0; i < list.length; i++) {
        var obj = list[i];
        var dataObj = {
            title: obj.name,
            link: obj.link,
            thumbnail: obj.thumbnail,
            extension: obj.extension,
            description: obj.description,
            is_360: obj.is_360
        };
        if (obj.id != null && obj.id !== undefined) {
            dataObj.id = obj.id;
        }

        var gallery = $('.gallery.active');
        if (!gallery.length) { return; }
        var input = $('input[type="hidden"]', gallery);
        var value = input.val();
        if (Validate.prototype.isNullOrWhiteSpace(value)) {
            value = '[]';
        }
        value = JSON.parse(value);
        value.push(dataObj);
        input.val(JSON.stringify(value));

        var template =
            '<div class="item col-4 col-lg-3" data-obj="{{ dataObj }}">' +
                '<div class="box-dragdrop position-relative ui-sortable-handle">' +
                    '<div class="image-wrapper position-relative">' +
                        '<a href="{{ link_full }}" data-fancybox="gallery-{{ key }}">' +
                            '<img src="{{ thumbnail_full }}" alt="{{ title }}" class="position-absolute w-100 h-100">' +
                        '</a>' +
                        '<div class="action position-absolute">' +
                            '<a href="#" class="btn-delete btn btn-danger btn-sm" title="Xóa"><i class="fas fa-trash-alt"></i></a>' +
                        '</div>' +
                    '</div>' +
                    '<div class="name position-absolute w-100 text-center">' +
                        '<span class="give-ellipsis after-2-lines">{{ title }}</span>' +
                    '</div>' +
                '</div>' +
            '</div>';
        var html = $.mustache(template, {
            dataObj: JSON.stringify(dataObj),
            key: gallery.attr('key'),
            link_full: obj.link_full,
            thumbnail_full: Validate.prototype.isNullOrWhiteSpace(obj.thumbnail) ? obj.link_full : obj.thumbnail_full,
            title: dataObj.title
        });

        var listImage = $('.list-image', gallery);
        $('.item:last-child', listImage).before(html);
    }

    galleryModalSelect.toggle();
}

function updateIframeExcludeId() {
    let gallery = $('.gallery.active');
    if (gallery.length === 0) return;

    let excludeIds = [];
    let input = $('input[type="hidden"]', gallery);
    let value = input.val();
    if (value) {
        try {
            let images = JSON.parse(value);
            excludeIds = (images || []).filter(function (img) { return img && (img.id != null && img.id !== undefined); }).map(function (img) { return img.id; });
        } catch (e) {}
    }

    let iframe = document.getElementById('iframe-gallery-image-select');
    if (iframe) {
        let baseUrl = iframe.getAttribute('src');
        if (baseUrl) {
            baseUrl = baseUrl.replace(/([?&])exclude_id=[^&]*/g, '').replace(/&$/, '');
            let sep = baseUrl.indexOf('?') >= 0 ? '&' : '?';
            iframe.src = baseUrl + sep + 'exclude_id=' + excludeIds.join(',');
        }
    }
}


$(document).ready(function () {
    $('.gallery[key]').each(function () {
        var key = $(this).attr('key');
        if (key) {
            new GalleryImage(key).init();
        }
    });
});
