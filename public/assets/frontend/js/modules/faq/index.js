$(document).ready(function () {
    $('#faq-category .list-faq .item .btn-toggle').on('click', function () {
        var btn = $(this);
        var item = btn.closest('.item');

        if (item.hasClass('expand')) {
            $('i', btn).removeClass('fa-minus').addClass('fa-plus');
            item.removeClass('expand');
        } else {
            $('i', btn).removeClass('fa-plus').addClass('fa-minus');
            item.addClass('expand');
        }
    });
});
