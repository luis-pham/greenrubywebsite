$(document).ready(function () {
    let sectionArticle = $('#article .section-3');

    // Slide article related
    $('.slide-1 .owl-carousel', sectionArticle).owlCarousel({
        loop: false,
        dots: true,
        nav: true,
        navText: [
            '<div class="button"><i class="fa-solid fa-chevron-left"></i></div>',
            '<div class="button"><i class="fa-solid fa-chevron-right"></i></div>'
        ],
        items: 1,
        margin: 24,
        autoplay: false,
        autoplayTimeout: 5000,
        smartSpeed: 400,
        responsiveClass: true,
        responsive: {
            576: {
                items: 2
            },
            992: {
                items: 3
            },
            1400: {
                items: 3,
                margin: 32
            }
        },
        onInitialized: fnListItemCarouselOnInit,
        onResized: fnListItemCarouselOnInit
    });
});