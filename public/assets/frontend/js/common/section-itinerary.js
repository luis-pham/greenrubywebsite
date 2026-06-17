$(document).ready(function () {
    let sectionItinerary = $('#main .section-itinerary');
    let listItinerary = $('.list-itinerary-cruise', sectionItinerary);
    // let listItineraryHtml = listItinerary.html();

    // Slide initerary
    let slideItinerary = $('#main .section-itinerary .slide-1 .owl-carousel').not('#cruise-detail .cruise-itinerary-carousel');
    let slideItineraryConfig = {
        loop: false,
        dots: true,
        nav: true,
        navText: [
            '<div class="button" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 6l-6 6l6 6"/></svg></div>',
            '<div class="button" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l6 6l-6 6"/></svg></div>'
        ],
        items: 1,
        margin: 0,
        autoplay: false,
        autoplayTimeout: 5000,
        smartSpeed: 400,
        responsiveClass: true,
        responsive: {
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
                items: 4
            }
        },
        onInitialized: fnListItemCarouselOnInit,
        onResized: fnListItemCarouselOnInit
    };
    slideItinerary.owlCarousel(slideItineraryConfig);

    let btnTabFilter = $('#main #itinerary .gallery-filter-bar button[data-bay], #main .section-itinerary .tab-filter .list-button .item button');
    btnTabFilter.on('click', function () {
        let btn = $(this);
        if (btn.hasClass('active')) {
            return;
        }

        let filterScope = btn.closest('#itinerary');
        if (!filterScope.length) {
            filterScope = btn.closest('section.section-itinerary');
        }
        filterScope.find('.gallery-filter-bar button[data-bay], .tab-filter .list-button .item button').removeClass('active');
        btn.addClass('active');

        let section = btn.closest('section.section-itinerary');
        if (!section.length) {
            section = btn.closest('#itinerary').find('.section-itinerary').first();
        }
        listItinerary = $('.list-itinerary-cruise', section);

        let bay = btn.attr('data-bay');
        if (!Validate.prototype.isNullOrWhiteSpace(bay)) {
            $('.item', listItinerary).each(function () {
                if ($(this).attr('data-bay') == bay) {
                    $(this).removeClass('disabled');
                } else {
                    $(this).addClass('disabled');
                }
            });
        } else {
            $('.item', listItinerary).removeClass('disabled');
        }

        // slideItinerary.trigger('destroy.owl.carousel');
        // let bay = btn.attr('data-bay');
        // if (!Validate.prototype.isNullOrWhiteSpace(bay)) {
        //     let html = '';
        //     $(listItineraryHtml).filter('.item').each(function () {
        //         if ($(this).attr('data-bay') == bay) {
        //             html += this.outerHTML;
        //         }
        //     });
        //     listItinerary.html(html);
        // } else {
        //     listItinerary.html(listItineraryHtml);
        // }
        // slideItinerary.owlCarousel(slideItineraryConfig);
    });
});