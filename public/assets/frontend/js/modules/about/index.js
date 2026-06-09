$(document).ready(function () {
    // Section 3 - sustainability slide
    $('#about .section-3 .slide-1 .owl-carousel').owlCarousel({
        loop: false,
        dots: false,
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
                dots: false,
                nav: true,
                navText: [
                    '<div class="button"><i class="fa-solid fa-chevron-left"></i></div>',
                    '<div class="button"><i class="fa-solid fa-chevron-right"></i></div>'
                ],
            },
            540: {
                items: 2,
                dots: false,
                nav: true,
                navText: [
                    '<div class="button"><i class="fa-solid fa-chevron-left"></i></div>',
                    '<div class="button"><i class="fa-solid fa-chevron-right"></i></div>'
                ],
            },
            992: {
                items: 4,
                dots: false
            },
            1400: {
                items: 4,
                margin: 32,
                dots: false
            }
        },
        onInitialized: fnListItemCarouselOnInit,
        onResized: fnListItemCarouselOnInit
    });

    // Section 5 - statistic mobile slide
    $('#about .section-5 .statistic-slide .owl-carousel').owlCarousel({
        loop: false,
        dots: true,
        nav: true,
        navText: [
            '<div class="button"><i class="fa-solid fa-chevron-left"></i></div>',
            '<div class="button"><i class="fa-solid fa-chevron-right"></i></div>'
        ],
        items: 1,
        margin: 16,
        autoplay: false,
        smartSpeed: 400,
        responsiveClass: true,
       
        onInitialized: fnListItemCarouselOnInit,
        onResized: fnListItemCarouselOnInit
    });

    // Section 4 - enviroment mobile slide
    $('#about .section-4 .enviroment-list-mobile .owl-carousel').owlCarousel({
        loop: false,
        dots: true,
        nav: true,
        navText: [
            '<div class="button"><i class="fa-solid fa-chevron-left"></i></div>',
            '<div class="button"><i class="fa-solid fa-chevron-right"></i></div>'
        ],
        items: 1,
        margin: 16,
        autoplay: false,
        autoplayTimeout: 5000,
        smartSpeed: 400,
        responsiveClass: true
    });
});
