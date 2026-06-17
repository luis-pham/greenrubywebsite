$(document).ready(function(){
    $('#gallery .gallery-video-grid').find('[id^="vr-player-"]').each(function(){
        const id = $(this).attr('id');
        if (!id || typeof videojs === 'undefined') {
            return;
        }

        const vrPlayer = videojs(id, {
            controls: true
        });

        if (typeof vrPlayer.vr === 'function') {
            vrPlayer.vr({
                projection: '360'
            });
        }
    });

    const $memoriesCarousel = $('#gallery .gallery-memories-slide .gallery-memories-carousel');
    if ($memoriesCarousel.length && $.fn.owlCarousel) {
        const navChevronLeft = '<div class="button" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 6l-6 6l6 6"/></svg></div>';
        const navChevronRight = '<div class="button" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l6 6l-6 6"/></svg></div>';

        function alignMemoriesNav($carousel) {
            const $stage = $carousel.find('.owl-stage-outer').first();
            const $nav = $carousel.find('.owl-nav').first();
            if (!$stage.length || !$nav.length) {
                return;
            }

            const stageTop = $stage.position().top;
            const imageHeight = $carousel.find('.gallery-memory-image').first().outerHeight() || 240;

            $nav.css({
                position: 'absolute',
                top: stageTop + 'px',
                left: 0,
                width: '100%',
                height: imageHeight + 'px',
                margin: 0,
                marginTop: 0
            });
        }

        function scheduleAlignMemoriesNav($carousel) {
            alignMemoriesNav($carousel);
            requestAnimationFrame(function () {
                alignMemoriesNav($carousel);
            });
            window.setTimeout(function () {
                alignMemoriesNav($carousel);
            }, 100);
        }

        $memoriesCarousel.owlCarousel({
            loop: false,
            dots: true,
            nav: true,
            navText: [navChevronLeft, navChevronRight],
            items: 1,
            margin: 16,
            autoplay: false,
            smartSpeed: 400,
            onInitialized: function (event) {
                const $carousel = $(event.target);
                scheduleAlignMemoriesNav($carousel);
                $carousel.find('img').on('load', function () {
                    scheduleAlignMemoriesNav($carousel);
                });
                $carousel.find('.owl-prev').attr('aria-label', 'Previous slide');
                $carousel.find('.owl-next').attr('aria-label', 'Next slide');
            },
            onResized: function (event) {
                scheduleAlignMemoriesNav($(event.target));
            },
            onTranslated: function (event) {
                scheduleAlignMemoriesNav($(event.target));
            }
        });

        $(window).on('resize', function () {
            if ($memoriesCarousel.hasClass('owl-loaded')) {
                scheduleAlignMemoriesNav($memoriesCarousel);
            }
        });
    }
});
