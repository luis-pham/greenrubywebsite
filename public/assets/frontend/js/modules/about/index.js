$(document).ready(function () {
    // Section 3 - sustainability mobile carousel
    var $ecoCarousel = $('#about .section-3 .about-eco-grid-mobile.owl-carousel');
    if ($ecoCarousel.length && $.fn.owlCarousel) {
        $ecoCarousel.owlCarousel({
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
            responsiveClass: true,
            responsive: {
                0: {
                    items: 1,
                    margin: 16,
                    dots: true,
                    nav: true
                },
                576: {
                    items: 1,
                    margin: 16,
                    dots: true,
                    nav: true
                }
            },
            onInitialized: fnListItemCarouselOnInit,
            onResized: fnListItemCarouselOnInit
        });
    }

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
