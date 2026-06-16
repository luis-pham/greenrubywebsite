$(document).ready(function () {
    const section    = $('#experience .section-experience');

    // GRID (desktop)
    const $gridList  = $('.itinerary-cruise .list-itinerary-cruise.row', section);
    const $gridItems = $('[data-group]', $gridList);

    // SLIDE (mobile)
    const $slideList        = $('.slide-1 .list-itinerary-cruise', section);
    const slideOriginalHtml = $slideList.html();
    let   $carousel         = $('.slide-1 .owl-carousel', section);

    const carouselConfig = {
        loop: false,
        dots: false,
        nav: true,
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
            0:    { items: 1, margin: 16 },
            768:  { items: 2, margin: 24 },
            992:  { items: 3 },
            1200: { items: 4 }
        },
        onInitialized: fnListItemCarouselOnInit,
        onResized: fnListItemCarouselOnInit
    };

    if ($carousel.length) {
        $carousel.owlCarousel(carouselConfig);
    }

    const $buttons = $('.filter .list-item .item button', section);

    // groups: [] hoặc null = all; mảng string = lọc theo nhiều group
    function applyFilter(groups) {
        const isAll      = !groups || groups.length === 0;
        const isDesktop  = window.innerWidth >= 992;

        if (isDesktop) {
            $gridItems.each(function () {
                const itemGroup = (($(this).data('group')) || '').toString().trim();
                const match     = isAll || groups.indexOf(itemGroup) !== -1;
                $(this).toggleClass('is-hidden', !match);
            });
        } else {
            if ($carousel.data('owl.carousel')) {
                $carousel.trigger('destroy.owl.carousel');
            }

            if (!isAll) {
                let html = '';
                $(slideOriginalHtml).filter('.item').each(function () {
                    const itemGroup = (($(this).data('group')) || '').toString().trim();
                    if (groups.indexOf(itemGroup) !== -1) {
                        html += this.outerHTML;
                    }
                });
                $slideList.html(html);
            } else {
                $slideList.html(slideOriginalHtml);
            }

            $carousel = $('.slide-1 .owl-carousel', section);
            $carousel.owlCarousel(carouselConfig);
        }
    }

    function getGroups($btn) {
        let raw = ($btn.attr('data-groups') || '').trim();
        let groups = [];
        if (raw) {
            try { groups = JSON.parse(raw); } catch (e) { groups = [raw]; }
        }
        return groups;
    }

    $buttons.on('click', function () {
        const $btn = $(this);
        if ($btn.hasClass('active')) return;

        $buttons.removeClass('active');
        $btn.addClass('active');

        applyFilter(getGroups($btn));
    });

    // Tự động apply filter nút đang active khi load trang
    const $activeBtn = $buttons.filter('.active').first();
    if ($activeBtn.length) {
        applyFilter(getGroups($activeBtn));
    }
});
