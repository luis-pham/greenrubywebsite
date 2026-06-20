$(document).ready(function () {
    $('#faq-category .list-faq .item .btn-toggle').on('click', function () {
        var btn = $(this);
        var item = btn.closest('.item');

        if (item.hasClass('expand')) {
            btn.removeClass('is-open');
            item.removeClass('expand');
        } else {
            btn.addClass('is-open');
            item.addClass('expand');
        }
    });
});
