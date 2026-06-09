/*	=====================================
 *	jQuery Plugin Text Editor
 *	Version: 2.0.0
 *
 *	@author: Dinh Viet Bao
 *  @email: vietbao273@gmail.com
 *  @created at: 26/05/2020
 *  @updated at: 
 	=====================================*/

/* ========== Plugin: Insert File ========== */
tinymce.PluginManager.add('insertfile', function (editor) {
    function openInsertFile() {
        editor.focus(true);

        let title = 'Chèn file';
        if (typeof editor.settings.filemanager_title !== 'undefined' && editor.settings.filemanager_title) {
            title = editor.settings.filemanager_title;
        }

        editor.windowManager.openUrl({
            title: title,
            url: Utilities.prototype.setQueryString(uploadUrl, 'callback', 'filePopupSelect'),
            onClose: function () {
                $('html').css('overflowY', '');
            }
        });
    }

    // Add a button that opens a window
    editor.ui.registry.addButton('insertfile', {
        tooltip: 'Chèn file',
        icon: 'gallery',
        onAction: openInsertFile
    });

    // Adds a menu item to the context menu
    editor.ui.registry.addMenuItem('insertfile', {
        text: 'Chèn file',
        icon: 'gallery',
        onAction: openInsertFile
    });
});

function filePopupSelect(obj) {
    let ed = window.tinymce.activeEditor;
    if (ed == null) return;

    let html = '';
    switch (obj.type) {
        case fileType['image']:
            html = '<img src="' + obj.link_full + '" alt="' + obj.name + '" />';
            break;
        case fileType['video']:
            html = '<video src="' + obj.link_full + '" controls>Your browser does not support the video element.</video>';
            break;
        case fileType['audio']:
            html = '<audio src="' + obj.link_full + '" controls>Your browser does not support the audio element.</audio>';
            break;
        default:
            html = '<p><a href="' + obj.link_full + '">' + obj.name + '</a></p>';
    }

    ed.selection.setContent(html);
    ed.windowManager.close();
}

/* ========== Plugin: Insert Youtube ========== */
tinymce.PluginManager.add('insertyoutube', function(editor, url) {
    var openDialog = function () {
        return editor.windowManager.open({
            title: 'Chèn video Youtube',
            body: {
                type: 'panel',
                items: [{
                    type: 'input',
                    name: 'link',
                    label: 'Link'
                }]
            },
            buttons: [{
                type: 'cancel',
                text: 'Close'
            }, {
                type: 'submit',
                text: 'Save',
                primary: true
            }],
            onSubmit: function (api) {
                let data = api.getData();
                if (data.link.trim() == '') {
                    alert('Bạn phải nhập link!');
                    return;
                }

                if (data.link.indexOf('v=') != -1) {
                    let videoId = data.link.split('v=')[1];
                    let endPosition = videoId.indexOf('&');
                    endPosition = endPosition != -1 ? endPosition : videoId.length;
                    videoId = videoId.substring(0, endPosition);
                    let link = 'https://www.youtube.com/embed/' + videoId;
                    let html =
                        '<div class="embed-iframe iframe-16-9">' +
                            '<iframe src="' + link + '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>' +
                        '</div>';
                    editor.insertContent(html);
                }

                api.close();
            }
        });
    };

    // Add a button that opens a window
    editor.ui.registry.addButton('insertyoutube', {
        tooltip: 'Chèn video Youtube',
        text: '<i class="fab fa-youtube"></i>',
        onAction: function () {
            openDialog();
        }
    });
});

/* ========== Plugin: Insert Embed Code ========== */
tinymce.PluginManager.add('insertembedcode', function(editor, url) {
    var openDialog = function () {
        return editor.windowManager.open({
            title: 'Chèn Mã nhúng',
            body: {
                type: 'panel',
                items: [{
                    type: 'input',
                    name: 'embed',
                    label: 'Mã nhúng'
                }, {
                    type: 'selectbox',
                    name: 'ratio',
                    label: 'Tỷ lệ',
                    items: [{
                        text: '1:1',
                        value: '1'
                    }, {
                        text: '16:9',
                        value: '2'
                    }, {
                        text: '4:3',
                        value: '3'
                    }, {
                        text: '3:2',
                        value: '4'
                    }]
                }]
            },
            buttons: [{
                type: 'cancel',
                text: 'Close'
            }, {
                type: 'submit',
                text: 'Save',
                primary: true
            }],
            onSubmit: function (api) {
                let data = api.getData();
                if (data.embed.trim() == '') {
                    alert('Bạn phải nhập mã nhúng!');
                    return;
                }

                let ratio = '';
                switch (data.ratio) {
                    case '2':
                        ratio = ' iframe-16-9';
                        break;
                    case '3':
                        ratio = ' iframe-4-3';
                        break;
                    case '4':
                        ratio = ' iframe-3-2';
                        break;
                    default:
                        break;
                }

                let html = '<div class="embed-iframe' + ratio +'">' + data.embed + '</div>';
                editor.insertContent(html);
                api.close();
            }
        });
    };

    // Add a button that opens a window
    editor.ui.registry.addButton('insertembedcode', {
        tooltip: 'Chèn Mã nhúng',
        text: '<i class="fal fa-code"></i>',
        onAction: function () {
            openDialog();
        }
    });
});

(function () {
    $.fn.extend({
        textEditor: function (options) {           
            let element = $(this);

            let defaults = {
                language: 'vi',
                height: 220,
                menubar: true,
                plugins: [
                    'print preview paste importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount ' + /*imagetools*/ ' textpattern noneditable help charmap ' + /*quickbars*/ ' emoticons',
                    'insertfile insertyoutube insertembedcode'
                ],
                toolbar: [
                    'formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | outdent indent',
                    'bullist numlist | backcolor forecolor | link unlink | removeformat | charmap | superscript subscript | table | insertfile insertyoutube | insertembedcode | code'
                ],
                content_css: '/assets/backend/css/tinymce.css?v=1.0.8',
                keep_styles: false,
                paste_data_images: true,
                paste_as_text: true,
                convert_urls: false,
                relative_urls: false,
                remove_script_host: false,
                entity_encoding: 'raw',
                valid_elements: '*[*]',
                contextmenu: 'cut copy paste | template | insertfile',
                templates: [
                    { title: 'Box ảnh/video mặc định', description: '', url: '/assets/backend/plugins/tinymce/template/box-media-default.html' },
                    { title: 'Box ảnh/video trái', description: '', url: '/assets/backend/plugins/tinymce/template/box-media-left.html' },
                    { title: 'Box ảnh/video phải', description: '', url: '/assets/backend/plugins/tinymce/template/box-media-right.html' },
                    { title: 'Box trích đoạn', description: '', url: '/assets/backend/plugins/tinymce/template/box-quote.html' }
                ],
                fontsize_formats: '8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 22pt 24pt 26pt 28pt 36pt 48pt 72pt',
                mobile: {
                    toolbar: [
                        'bold italic underline strikethrough | alignleft aligncenter alignright alignjustify',
                        'bullist numlist | link unlink | removeformat | insertfile insertyoutube insertembedcode'
                    ],
                }
            };

            options = $.extend(defaults, options);

            return this.each(function () {
                element.tinymce(options);
            });
        }
    });
})(jQuery);