$(document).ready(function(){
    const gallery = document.querySelector('.gallery-container');
    const mql = window.matchMedia('(max-width: 1200px)');

    function handleLayoutChange(e) {
        if (gallery === null) {
            return;
        }
        if (e.matches) {
            if (!gallery.classList.contains('owl-carousel')) {
                gallery.classList.remove('gallery-grid-layout')
                gallery.classList.add('owl-carousel', 'owl-theme');

                $(gallery).owlCarousel({
                    loop: false,
                    margin: 16,
                    nav: false,
                    dots: true,
                    responsive: {
                        0:    { items: 1 },
                        576:  { items: 2 },
                        992:  { items: 3 },
                    },
                    autoplay: false,
                });
            }
        } else {
            if (gallery.classList.contains('owl-carousel')) {
                $(gallery).trigger('destroy.owl.carousel');
                gallery.classList.add('gallery-grid-layout');
                gallery.classList.remove('owl-carousel', 'owl-loaded', 'owl-theme');
                gallery.removeAttribute('style');
            }
        }
    }

    mql.addEventListener('change', handleLayoutChange);
    handleLayoutChange(mql);
})
