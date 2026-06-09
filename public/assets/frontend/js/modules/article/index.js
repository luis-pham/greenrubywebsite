$(document).ready(function () {
    $('#article-category .section-3 .slide-1 .owl-carousel').owlCarousel({
        loop: false,
        dots: true,
        nav: false,
        items: 1,
        margin: 24,
        autoplay: false,
        autoplayTimeout: 5000,
        smartSpeed: 400,
        onInitialized: fnListItemCarouselOnInit,
        onResized: fnListItemCarouselOnInit
    });
});