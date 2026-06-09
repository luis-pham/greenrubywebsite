/* =====================================
* jQuery Plugin Image Select
* Version: 1.0.2
*
* @author: Dinh Viet Bao
* @email: vietbao273@gmail.com
* @created at: 24/10/2017
* @updated at: 04/04/2021
=====================================*/
(function () {
    $.fn.extend({
        imageSelect: function () {
            function genId() {
                let d = new Date().getTime();
                let id = 'image-select-xxxx-xxxx'.replace(/[xy]/g, function (c) {
                    let r = (d + Math.random() * 16) % 16 | 0;
                    d = Math.floor(d / 16);
                    return (c == 'x' ? r : (r & 0x7 | 0x8)).toString(16);
                });
                return id;
            }

            function setQueryString(uri, key, value) {
                let reg = new RegExp('([?&])' + key + '=.*?(&|$)', 'i');
                let separator = uri.indexOf('?') !== -1 ? '&' : '?';
                if (uri.match(reg)) {
                    return uri.replace(re, '$1' + key + '=' + value + '$2');
                } else {
                    return uri + separator + key + '=' + value;
                }
            }

            return this.each(function () {
                let element = $(this);
                let id = genId();
                let fileManagerUrl = element.attr('data-file-manager-url');
                fileManagerUrl = setQueryString(fileManagerUrl, 'callback', id);

                let html =
                    '<div class="box-image-link">' +
                        '<img class="img-image-link img-fluid mb-2" style="display: none">' +
                        '<div>' +
                            '<a href="#" class="btn btn-success btn-sm btn-select">Chọn ảnh</a>' +
                            '&nbsp;' +
                            '<a href="#" class="btn btn-danger btn-sm btn-delete" style="display: none;">Xóa ảnh</a>' +
                        '</div>' +
                        '<div id="' + id + '" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">' +
                            '<div class="modal-dialog modal-primary modal-xl" role="document">' +
                                '<div class="modal-content">' +
                                    '<div class="modal-header">' +
                                        '<h2 class="h4 mb-0 modal-title">Chọn ảnh đại diện</h2>' +
                                        '<button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>' +
                                    '</div>' +
                                    '<div class="modal-body p-0">' +
                                        '<div class="embed-responsive" style="height: 80vh">' +
                                            '<iframe></iframe>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
                element.after(html);

                var box = element.next();
                var modal = new coreui.Modal(document.getElementById(id));

                window[id] = function (obj) {
                    element.val(obj.link).attr('data-link-full', obj.link_full);
                    $('.img-image-link', box).attr('src', obj.link_full).show();
                    $('.btn-delete', box).show();
                    modal.hide();
                }

                if (element.val() != '') {
                    let fileLinkFull = element.attr('data-link-full');
                    if (!(fileLinkFull == '' || fileLinkFull == null || fileLinkFull == undefined)) {
                        element.attr('data-link-full', fileLinkFull);
                        $('.img-image-link', box).attr('src', fileLinkFull).show();
                        $('.btn-delete', box).show();
                    }
                }

                document.getElementById(id).addEventListener('show.coreui.modal', function () {
                    $('iframe', $(this)).attr('src', fileManagerUrl);
                });

                document.getElementById(id).addEventListener('shown.coreui.modal', function () {
                    $('iframe', $(this)).css({
                        width: '100%',
                        height: '100%',
                        border: 'none'
                    });
                });

                $('.btn-select', box).click(function (e) {
                    e.preventDefault();
                    modal.toggle();
                });

                $('.btn-delete', box).click(function (e) {
                    e.preventDefault();

                    if (confirm('Bạn có chắc muốn xóa?')) {
                        $(element).val('').removeAttr('data-link-full');
                        $('.img-image-link', box).attr('src', '').hide();
                        $('.btn-delete', box).hide();
                    }
                });
            });
        }
    });
})(jQuery);