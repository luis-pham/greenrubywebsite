$(document).ready(function () {
    let sectionTestimonial = $('#main .section-testimonial');
    
    let slideTestimonial = $('.slide-1 .owl-carousel', sectionTestimonial);
    slideTestimonial.on('changed.owl.carousel', function (e) {
        let idx = e.relatedTarget.relative(e.item.index) || 0;

        let section = e.target.closest('section');
        let btnFilter = $('.list-filter .item', section);
        btnFilter.removeClass('active');
        btnFilter.eq(idx).addClass('active');

        // console.log(idx);
        // console.log(btnFilter.length);

        btnFilter.addClass('hide-on-mobile');
        let maxIdx = btnFilter.length - 1;
        let from = 0;
        let to = 0;
        if (idx == 0) {
            from = 0;
            to = 2;
        } else if (idx == maxIdx) {
            from = idx - 2;
            to = idx;
        } else {
            from = idx - 1;
            to = idx + 1;
        }

        if (to > maxIdx) {
            to = maxIdx;
        }
        if (from < 0) {
            from = 0;
        }

        btnFilter.slice(from, to + 1).removeClass('hide-on-mobile');
    });
    slideTestimonial.owlCarousel({
        loop: false,
        dots: true,
        nav: false,
        items: 1,
        margin: 16,
        autoplay: false,
        autoplayTimeout: 5000,
        smartSpeed: 400,
        responsiveClass: true,
        responsive: {
            576: {
                dots: false
            }
        },
        onInitialized: fnListItemCarouselOnInit,
        onResized: fnListItemCarouselOnInit
    });
    
    let btnFilter = $('.list-filter .item', sectionTestimonial);
    btnFilter.on('click', function () {
        let btn = $(this);
        if (btn.hasClass('active')) {
            return;
        }

        let idx = btnFilter.index(this);
        slideTestimonial.trigger('to.owl.carousel', idx);
    });
});