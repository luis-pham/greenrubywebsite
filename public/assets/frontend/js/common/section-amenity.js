$(document).ready(function() {
    let sectionAmenity = $('#main .section-amenity');
    let slideAmenity = $('.slide-1 .owl-carousel', sectionAmenity);
    slideAmenity.owlCarousel({
        loop: false,
        dots: true,
        items: 1,
        margin: 12,
        autoplay: false,
        autoplayTimeout: 5000,
        smartSpeed: 400
    });

    let btnViewAll = $('.btn-view-all', sectionAmenity);
    btnViewAll.on('click', function () {
        let btn = $(this);
        let btnWrapper = btn.closest('.btn-view-all-wrapper');
        let section = btn.closest('section');
        $('.list-item:not(.owl-carousel) .item.d-none', section).each(function () {
            $(this).removeClass('d-none');
            initEllipsisTooltip($(this));
        });
        btnWrapper.remove();
    });

    let btnViewAllMobile = $('.btn-view-all-mobile', sectionAmenity);
    btnViewAllMobile.on('click', function () {
        let btn = $(this);
        let btnWrapper = btn.closest('.btn-view-all-wrapper');
        slideAmenity.trigger('destroy.owl.carousel');
        slideAmenity.removeClass('owl-theme owl-carousel owl-loaded');
        slideAmenity.find('.owl-stage-outer').children().unwrap();
        slideAmenity.removeData();
        slideAmenity.addClass('mobile');
        btnWrapper.remove();
    });
});