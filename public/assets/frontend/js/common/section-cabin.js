$(document).ready(function () {
    let sectionCabin = $('#main .section-cabin');
    let listCabin = $('.list-itinerary-cruise', sectionCabin);
    let listCabinHtml = listCabin.html();

    // Slide cabin
    let slideCabin = $('.slide-1 .list-itinerary-cruise', sectionCabin);
    let isSuitesGrid = slideCabin.hasClass('suites-grid');
    let slideCabinConfig = {
        loop: false,
        dots: true,
        nav: true,
        navText: [
            '<div class="button"><i class="fa-solid fa-chevron-left"></i></div>',
            '<div class="button"><i class="fa-solid fa-chevron-right"></i></div>'
        ],
        items: 1,
        margin: isSuitesGrid ? 16 : 24,
        autoplay: false,
        autoplayTimeout: 5000,
        smartSpeed: 400,
        responsiveClass: true,
        responsive: isSuitesGrid ? {
            0: {
                items: 1,
                margin: 16,
                dots: true,
                nav: true
            },
            1024: {
                items: 1,
                margin: 0,
                dots: false,
                nav: false
            }
        } : {
            0: {
                items: 1
            },
            1024: {
                dots: false,
                items: 2
            },
            1200: {
                dots: false,
                items: 3
            },
            1400: {
                dots: false,
                items: 3,
                margin: 32
            }
        },
        onInitialized: function (event) {
            fnListItemCarouselOnInit(event);
            if (isSuitesGrid && $(window).width() < 1024) {
                $(event.target).trigger('refresh.owl.carousel');
            }
        },
        onResized: function (event) {
            fnListItemCarouselOnInit(event);
            if (isSuitesGrid && $(window).width() < 1024) {
                $(event.target).trigger('refresh.owl.carousel');
            }
        }
    };
    slideCabin.owlCarousel(slideCabinConfig);

    let btnTabFilter = $('.tab-filter .list-button .item button', sectionCabin);
    btnTabFilter.on('click', function () {
        let btn = $(this);
        if (btn.hasClass('active')) {
            return;
        }

        let section = btn.closest('section');
        let btnFilter = $('.tab-filter .list-button .item button', section);
        btnFilter.removeClass('active');
        btn.addClass('active');

        let cabinClass = btn.attr('data-cabin-class');

        slideCabin.find('.owl-item .item').each(function(){
            const $item = $(this);
            const itemCabinClass = $item.data('cabin-class');

            if(cabinClass !== "" && itemCabinClass !== cabinClass){
                $item.addClass('disabled');
            }
            else{
                $item.removeClass('disabled');
            }
        });
    });
});
