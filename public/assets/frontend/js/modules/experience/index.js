$(document).ready(function () {
    $('#experience .section-2 .slide-1 .owl-carousel').owlCarousel({
        loop: false,
        dots: true,
        nav: false,
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
            0: {
                items: 1,
                dots: true,
                nav: true,
                margin: 16,
                navText: [
                    '<div class="button"><i class="fa-solid fa-chevron-left"></i></div>',
                    '<div class="button"><i class="fa-solid fa-chevron-right"></i></div>'
                ],
            },
            768: {
                items: 2,
                dots: false,
                nav: true,
                margin: 24,
                navText: [
                    '<div class="button"><i class="fa-solid fa-chevron-left"></i></div>',
                    '<div class="button"><i class="fa-solid fa-chevron-right"></i></div>'
                ],
            },
            992: {
                items: 3,
                dots: false
            },
            1400: {
                items: 3,
                margin: 24,
                dots: false
            }
        },
        onInitialized: fnListItemCarouselOnInit,
        onResized: fnListItemCarouselOnInit
    });

});