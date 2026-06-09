$(document).ready(function(){
    $('.section-faq .grid-faq-inner.owl-carousel').owlCarousel({
        margin: 24,
        nav: false,
        dots: true,
        responsive: {
            0: {
                items: 1       // Mobile: 1 item
            },
            992: {
                items: 2       // Tablet: 2 items
            },
            1400: {
                items: 3       // Desktop: 3 items
            }
        }
    })
})
window.addEventListener('load',function () {
    const $sectionFaq = $('.section-faq');
    const $gridFaq = $sectionFaq.find('.grid-faq-inner');
    const $sectionContact = $('.section-contact');

    if (!$gridFaq.length) return;

    function updateOverlap() {

        $gridFaq.css({
            position: 'absolute',
        });
        requestAnimationFrame(function(){
            const height = $gridFaq.outerHeight(true);
            const halfHeight = Math.round(height / 2);

            $sectionFaq.find('.container-fluid').first().css({
                paddingBottom: `${halfHeight}px`
            });

            $sectionContact.find('.container-fluid').first().css({
                paddingTop: `${halfHeight + 72}px`
            });
        })
    }

    $gridFaq.on('resized.owl.carousel', function () {
        updateOverlap();
    });

    let resizeTimer;
    function debouncedResize() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(updateOverlap, 200);
    }

    $(window).on('resize', debouncedResize);

    setTimeout(updateOverlap, 100);
});
