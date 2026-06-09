var modalSourceDataSelectId = 'modal-source-data-select';
var modalSourceDataSelect = new coreui.Modal(document.getElementById(modalSourceDataSelectId));
document.getElementById(modalSourceDataSelectId).addEventListener('hidden.coreui.modal', function () {
    $('.source-data.active').removeClass('active');
});

class SourceData {
    constructor(key, title, url) {
        this.title = title;
        this.url = url;
        this.sourceData = $('.source-data[key="' + key + '"]');
        this.listItem = $('.list-item', this.sourceData);
        this.btnOpenModalSelect = $('.btn-open-modal-select', this.sourceData);
        this.input = $('input[type="hidden"]', this.sourceData);
    }

    init() {
        this.bindData();
        this.bindEvent();
    }

    bindData() {
        let self = this;

        let listId = this.input.val();
        if (Validate.prototype.isNullOrWhiteSpace(listId)) {
            listId = '[]';
        }
        listId = JSON.parse(listId);

        if (listId.length > 0) {
            $.ajax({
                url: self.url.getData,
                type: 'POST',
                data: {
                    id: listId
                },
                success: function (response) {
                    for (let i = 0; i < listId.length; i++) {
                        if (response.data[listId[i]] != undefined) {
                            let obj = response.data[listId[i]];
                            let html = self.bindItemHtml(obj);
                            self.listItem.append(html);
                        }
                    }
                }
            });
        }
    }

    bindEvent() {
        let self = this;

        self.listItem.sortable({
            handle: '.box-dragdrop',
            connectWith: '#' + self.listItem.attr('id'),
            zIndex: 99999,
            forcePlaceholderSize: true,
            opacity: 0.8,
            update: function () {
                self.updateValue();
            }
        });

        self.listItem.on('click', '.btn-delete', function (e) {
            e.preventDefault();

            if (confirm('Bạn có chắc muốn xóa?')) {
                $(this).closest('.item').remove();
                self.updateValue();
            }
        });

        self.btnOpenModalSelect.on('click', function (e) {
            e.preventDefault();

            let modal = $('#' + modalSourceDataSelectId);
            $('.modal-title', modal).text('Chọn ' + self.title);

            let iframe = $('iframe', modal);
            let iframeSrc = iframe.attr('data-url') != self.url.selectData ? self.url.selectData : iframe[0].contentWindow.location.href;
            let listId = self.input.val();
            if (!Validate.prototype.isNullOrWhiteSpace(listId)) {
                iframeSrc = Utilities.prototype.setQueryString(iframeSrc, 'exclude_id', listId);
            }

            if (iframe.attr('data-url') != self.url.selectData) {
                iframe.attr('data-url', self.url.selectData);
            }

            let fnShowModal = function () {
                self.sourceData.addClass('active');
                modalSourceDataSelect.toggle();
            };
            if (iframeSrc != iframe.attr('src')) {
                UserInterface.prototype.showLoading();
                iframe.one('load', function () {
                    UserInterface.prototype.hideLoading();
                    fnShowModal();
                });
                iframe.attr('src', iframeSrc);
            } else {
                fnShowModal();
            }
        });
    }

    updateValue() {
        let value = [];
        $('.item', this.listItem).each(function () {
            let id = $(this).attr('data-id');
            if (!Validate.prototype.isNullOrWhiteSpace(id)) {
                value.push(Validate.prototype.isInt(id) ? parseInt(id) : id);
            }
        });
        this.input.val(JSON.stringify(value));
    }

    bindItemHtml(obj) {
        let template = '<div class="item mb-2" data-id="{{ id }}">' +
            '<div class="box-dragdrop ui-sortable-handle">' +
                '<div class="media">' +
                    '{{#image_link_full}}' +
                        '{{#url}}' +
                            '<a href="{{ url }}" class="image-wrapper position-relative mr-2" target="_blank">' +
                                '<img src="{{ image_link_full }}" alt="" class="position-absolute w-100 h-100"></a>' +
                            '</a>' +
                        '{{/url}}' +
                        '{{^url}}' +
                            '<div class="image-wrapper position-relative mr-2">' +
                                '<img src="{{ image_link_full }}" alt="" class="position-absolute w-100 h-100">' +
                            '</div>' +
                        '{{/url}}' +
                    '{{/image_link_full}}' +
                    '<div class="media-body">' +
                        '<p class="give-ellipsis after-2-lines mb-0">' +
                            '{{#url}}' +
                                '<a href="{{ url }}" target="_blank" class="text-reset">{{ title }}</a>' +
                            '{{/url}}' +
                            '{{^url}}' +
                                '{{ title }}' +
                            '{{/url}}' +
                        '</p>' +
                        '<a href="#" class="btn-delete text-danger"><i class="fas fa-trash-alt"></i> Xóa</a>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
        return $.mustache(template, obj);
    }
}

function selectCallBack(obj) {
    let id = obj.id;

    let sourceData = $('.source-data.active');

    let input = $('input[type="hidden"]', sourceData);
    let value = input.val();
    if (Validate.prototype.isNullOrWhiteSpace(value)) {
        value = '[]';
    }
    value = JSON.parse(value);
    if (value.indexOf(id) == -1) {
        value.push(id);
    }
    input.val(JSON.stringify(value));

    let html = SourceData.prototype.bindItemHtml(obj);
    let listItem = $('.list-item', sourceData);
    listItem.append(html);

    modalSourceDataSelect.toggle();
}