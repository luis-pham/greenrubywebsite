$(document).ready(function () {
    const gallery = document.querySelector('.gallery-container');
    if (gallery === null) {
        return;
    }

    const isExperiencePage = document.getElementById('experience') !== null;
    const carouselBreakpoint = isExperiencePage ? '(max-width: 767px)' : '(max-width: 1200px)';
    const mql = window.matchMedia(carouselBreakpoint);

    function alignGalleryNav($carousel) {
        const $stage = $carousel.find('.owl-stage-outer').first();
        const $nav = $carousel.find('.owl-nav').first();
        if (!$stage.length || !$nav.length) {
            return;
        }

        const stageTop = $stage.position().top;
        const stageHeight = $stage.outerHeight();

        $nav.css({
            position: 'absolute',
            top: stageTop + 'px',
            left: 0,
            width: '100%',
            height: stageHeight + 'px',
            margin: 0,
            marginTop: 0
        });
    }

    function scheduleAlignGalleryNav($carousel) {
        alignGalleryNav($carousel);
        requestAnimationFrame(function () {
            alignGalleryNav($carousel);
        });
        window.setTimeout(function () {
            alignGalleryNav($carousel);
        }, 100);
    }

    const carouselOptions = {
        loop: false,
        margin: 16,
        nav: true,
        navText: [
            '<div class="button"><i class="fa-solid fa-chevron-left"></i></div>',
            '<div class="button"><i class="fa-solid fa-chevron-right"></i></div>'
        ],
        dots: true,
        responsive: isExperiencePage
            ? { 0: { items: 1 } }
            : {
                0: { items: 1 },
                576: { items: 2 },
                992: { items: 3 }
            },
        autoplay: false,
        onInitialized: function (event) {
            const $carousel = $(event.target);
            scheduleAlignGalleryNav($carousel);
            $carousel.find('img').on('load', function () {
                scheduleAlignGalleryNav($carousel);
            });
        },
        onResized: function (event) {
            scheduleAlignGalleryNav($(event.target));
        },
        onTranslated: function (event) {
            scheduleAlignGalleryNav($(event.target));
        }
    };

    $(window).on('resize', function () {
        if (gallery.classList.contains('owl-carousel')) {
            scheduleAlignGalleryNav($(gallery));
        }
    });

    function handleLayoutChange(e) {
        if (e.matches) {
            if (!gallery.classList.contains('owl-carousel')) {
                gallery.classList.remove('gallery-grid-layout');
                gallery.classList.add('owl-carousel', 'owl-theme');
                $(gallery).owlCarousel(carouselOptions);
            } else {
                scheduleAlignGalleryNav($(gallery));
            }
        } else if (gallery.classList.contains('owl-carousel')) {
            $(gallery).trigger('destroy.owl.carousel');
            gallery.classList.add('gallery-grid-layout');
            gallery.classList.remove('owl-carousel', 'owl-loaded', 'owl-theme');
            gallery.removeAttribute('style');
            gallery.querySelectorAll('.owl-nav, .owl-dots').forEach(function (el) {
                el.remove();
            });
        }
    }

    mql.addEventListener('change', handleLayoutChange);
    handleLayoutChange(mql);
});
