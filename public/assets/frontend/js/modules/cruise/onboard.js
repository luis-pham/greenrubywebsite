(function ($) {
    'use strict';

    var images = [];
    var currentIdx = 0;

    var langPrefix = window.langCode
        ? '/' + window.langCode
        : '';

    var $mainImg = $('#onboard-main-img-src');
    var $capTitle = $('#onboard-cap-title');
    var $capDesc = $('#onboard-cap-desc');
    var $thumbWrap = $('#onboard-thumbs');
    var $current = $('#onboard-img-current');
    var $total = $('#onboard-img-total');

    $(document).on('click', '.onboard-main-tab', function () {
        $('.onboard-main-tab').removeClass('active');
        $(this).addClass('active');

        var tab = $(this).data('tab');
        $('.onboard-tab-panel').hide();
        $('#onboard-tab-' + tab).show();
    });

    $(document).on('click', '.onboard-cat-item', function () {
        $('.onboard-cat-item').removeClass('active');
        $(this).addClass('active');

        var id = $(this).data('id');
        var type = $(this).data('type');
        var title = $(this).data('title');
        var desc = $(this).data('desc');

        $capTitle.text(title);
        $capDesc.text(desc);

        var url;
        if (type === 'activity') {
            url = '/api/exp-activities/getById?id=' + id;
        } else {
            url = '/api' + langPrefix + '/cabins/getById?id=' + id;
        }

        $.get(url)
            .done(function (res) {
                var ok = (res.msg === 'success') || (res.success === true);
                if (!ok || !res.data) {
                    return;
                }

                var d = res.data;
                images = [];

                var mainSrc = d.image_link || d.image || '';
                if (mainSrc) {
                    images.push({
                        url: mainSrc,
                        label: d.name || title
                    });
                }

                var gallery = d.file || d.files || [];
                $.each(gallery, function (i, f) {
                    var src = f.link || f.url || '';
                    if (src) {
                        images.push({
                            url: src,
                            label: ''
                        });
                    }
                });

                currentIdx = 0;
                renderImages();
            })
            .fail(function () {
                console.warn('[Onboard] API error: ' + url);
            });
    });

    function renderImages() {
        if (!images.length) {
            return;
        }

        $mainImg.attr('src', images[0].url);
        $current.text(1);
        $total.text(images.length);

        $thumbWrap.empty();
        var max = Math.min(images.length, 4);

        for (var i = 0; i < max; i++) {
            (function (idx) {
                var $t = $(
                    '<div class="onboard-thumb' + (idx === 0 ? ' active' : '') + '">'
                    + '<img src="' + images[idx].url + '" alt="" loading="lazy"/>'
                    + '</div>'
                );
                $t.on('click', function () {
                    currentIdx = idx;
                    setMain(idx);
                    $('.onboard-thumb').removeClass('active');
                    $(this).addClass('active');
                });
                $thumbWrap.append($t);
            })(i);
        }

        if (images.length > 4) {
            $thumbWrap.append(
                '<div class="onboard-thumb onboard-thumb--more">+'
                + (images.length - 4)
                + '</div>'
            );
        }
    }

    function setMain(idx) {
        if (!images[idx]) {
            return;
        }
        $mainImg.attr('src', images[idx].url);
        $current.text(idx + 1);
    }

    $(document).on('click', '#onboard-prev', function () {
        if (!images.length) {
            return;
        }
        currentIdx = (currentIdx - 1 + images.length) % images.length;
        setMain(currentIdx);
        updateThumbActive();
    });

    $(document).on('click', '#onboard-next', function () {
        if (!images.length) {
            return;
        }
        currentIdx = (currentIdx + 1) % images.length;
        setMain(currentIdx);
        updateThumbActive();
    });

    function updateThumbActive() {
        $('.onboard-thumb').each(function (i) {
            $(this).toggleClass('active', i === currentIdx);
        });
    }

    $(document).ready(function () {
        var $first = $('.onboard-cat-item.active').first();
        if ($first.length) {
            $first.trigger('click');
        }
    });

})(jQuery);
