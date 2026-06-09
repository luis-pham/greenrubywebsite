$(document).ready(function () {
    // Slide page cover
    let slidePageCover = $('#main .section-cover .slide-1 .owl-carousel');
    if (slidePageCover.length > 0) {
        let slidePageCoverConfig = {
            loop: false,
            dots: true,
            nav: false,
            items: 1,
            margin: 0,
            autoplay: false,
            autoplayTimeout: 6000,
            smartSpeed: 1000,
            onInitialized: function (e) {
                $(e.target).closest('.section-cover').removeClass('is-loading');
            }
        };
        let fnPageCoverAutoplayTimeout = null;
        let slidePageCoverVideoTime = {};

        slidePageCover.on('changed.owl.carousel', function (e) {
            if (fnPageCoverAutoplayTimeout) {
                clearTimeout(fnPageCoverAutoplayTimeout);
                fnPageCoverAutoplayTimeout = null;
            }

            let currentItem = $(e.target).find('.owl-item').eq(e.item.index);
            let video = currentItem.find('video').get(0);
            slidePageCover.find('video').each(function () {
                if (this != video && !this.paused) {
                    this.pause();
                }
            });
            
            if (video) {
                let idx = e.relatedTarget.relative(e.item.index);
                if (slidePageCoverVideoTime[idx] != undefined) {
                    video.currentTime = slidePageCoverVideoTime[idx];
                }
                video.play();
                $(video).off('timeupdate ended').on('timeupdate', function () {
                    slidePageCoverVideoTime[idx] = video.currentTime;
                }).on('ended', function () {
                    slidePageCoverVideoTime[idx] = 0;
                    slidePageCover.trigger('next.owl.carousel');
                });
            } else {
                fnPageCoverAutoplayTimeout = setTimeout(function () {
                    slidePageCover.trigger('next.owl.carousel');
                }, slidePageCoverConfig.autoplayTimeout);
            }
        });

        slidePageCover.on('initialized.owl.carousel', function (e) {
            slidePageCover.find('video').each(function () {
                this.loop = false;
            });

            let currentItem = slidePageCover.find('.owl-item.active').first();
            let video = currentItem.find('video').get(0);
            if (video) {
                video.play();
            }
        });

        slidePageCover.owlCarousel(slidePageCoverConfig);
    }
});