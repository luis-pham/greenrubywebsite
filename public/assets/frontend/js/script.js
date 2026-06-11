const apiStatus = {
    error: 0,
    success: 1
};

class Validate {
    isInt(value) {
        let x = parseFloat(value);
        return !isNaN(value) && (x | 0) === x && value.toString().indexOf('e') == -1;
    }

    isDate(value) {
        value = value.split('/');
        let date = new Date(value[2] + '/' + value[1] + '/' + value[0]);
        return !!(date && (date.getMonth() + 1) == value[1] && date.getDate() == Number(value[0]));
    }

    isTime(value) {
        let reg = /^([01]?[0-9]|2[0-3])(:[0-5][0-9])$/;
        return reg.test(value);
    }

    isEmail(value) {
        let reg = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return reg.test(String(value).toLowerCase());
    }

    isNullOrWhiteSpace(value) {
        if (value == undefined)
            return true;

        if (value == null)
            return true;

        return value.toString().replace(/\s/g, '').length == 0;
    }
}

class UserInterface {
    showLoading() {
        $('body').addClass('show-loading');
    }

    hideLoading() {
        $('body').removeClass('show-loading');
    }

    isInView(element) {
        if (element.length == 0) {
            return;
        }

        let win = $(window);
        let winHeight = win.height();
        let winTopPosition = win.scrollTop();
        let winBottomPosition = (winTopPosition + winHeight);

        let elementHeight = element.outerHeight();
        let elementTopPosition = element.offset().top;
        let elementBottomPosition = (elementTopPosition + elementHeight);

        return elementBottomPosition >= winTopPosition && elementTopPosition <= winBottomPosition;
    }
}

class Utilities {
    getQueryString(key) {
        let url = window.location.search.substring(1);
        let list = url.split('&');
        for (let i = 0; i < list.length; i++) {
            let qs = list[i].split('=');
            if (qs[0] == key) {
                return decodeURIComponent(qs[1]);
            }
        }
        return null;
    }

    guid() {
        function s4() {
            return Math.floor((1 + Math.random()) * 0x10000)
                .toString(16)
                .substring(1);
        }
        return s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
    }
}

class CarouselGallery {
    constructor(element, listFile) {
        this.element = element;
        this.listFile = listFile;
    }

    init() {
        this.bindHtml();
        this.bindEvent();
    }

    setActiveThumbnail(index) {
        if (!this.listThumbnail.length) {
            return;
        }
        let item = $('.item', this.listThumbnail);
        let active = item.removeClass('active').eq(index);
        if (!active.length) {
            return;
        }
        active.addClass('active');

        if (this.thumbnail.length) {
            let wrapperWidth = this.thumbnail.width();
            let currentScroll = this.thumbnail.scrollLeft();
            let left = active.position().left;
            let itemWidth = active.outerWidth(true);

            if (left < 0 || (left + itemWidth) > (wrapperWidth)) {
                let newScroll = left + currentScroll - (wrapperWidth - itemWidth) / 2;
                this.thumbnail.stop().animate({ scrollLeft: newScroll }, 200);
            }
        }
    };

    bindEvent() {
        let self = this;

        let slideImage = $('.slide-1 .list-image', this.element);
        if (slideImage.length > 0) {
            slideImage.owlCarousel({
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
                onInitialized: function(event) {
                    self.setActiveThumbnail(event.item.index || 0);
                },
                onChanged: function(event) {
                    self.setActiveThumbnail(event.item.index || 0);
                }
            });
        }

        if (this.listThumbnail.length > 0 && slideImage.length > 0) {
            let isDown = false;
            let isDragging = false;
            let startX = 0;
            let scrollLeft = 0;
            let dragThreshold = 5;

            this.thumbnail.on('mousedown', function(e) {
                isDown = true;
                isDragging = false;
                self.thumbnail.addClass('is-dragging');
                startX = e.pageX - self.thumbnail.offset().left;
                scrollLeft = self.thumbnail.scrollLeft();
            });

            this.thumbnail.on('mouseleave mouseup', function() {
                isDown = false;
                setTimeout(function() {
                    isDragging = false;
                }, 0);
                self.thumbnail.removeClass('is-dragging');
            });

            this.thumbnail.on('mousemove', function(e) {
                if (!isDown) {
                    return;
                }
                e.preventDefault();
                let x = e.pageX - self.thumbnail.offset().left;
                let walk = x - startX;
                if (Math.abs(walk) > dragThreshold) {
                    isDragging = true;
                }
                self.thumbnail.scrollLeft(scrollLeft - walk);
            });

            this.listThumbnail.on('click', '.item', function(e) {
                if (isDragging) {
                    e.preventDefault();
                    return;
                }
                let index = $(this).data('index');
                slideImage.trigger('to.owl.carousel', [index, 300, true]);
                self.setActiveThumbnail(index);
            });
        }
    }

    bindHtml() {
        let html = '<div class="carousel-gallery">';
        if (this.listFile.length > 0) {
            html += '<div class="slide-1">';
            html += '<div class="list-image owl-carousel owl-theme">';
            for (let i = 0; i < this.listFile.length; i++) {
                html +=
                    `<div class="item">
                        <div class="image-wrapper image-4-3 position-relative">
                            <img src="${this.listFile[i].link}" alt="${this.listFile[i].name}" class="position-absolute w-100 h-100" />
                        </div>
                    </div>`;
            }
            html += '</div>';
            html += '</div>';

            html += '<div class="thumbnail d-none d-md-block position-absolute">';
            html += '<div class="list-thumbnail d-flex">';
            for (let i = 0; i < this.listFile.length; i++) {
                let imageLink = !Validate.prototype.isNullOrWhiteSpace(this.listFile[i].thumbnail)
                    ? this.listFile[i].thumbnail
                    : this.listFile[i].link;
                html +=
                    `<div class="item" data-index="${i}">
                        <div class="image-wrapper position-relative">
                            <img src="${imageLink}" alt="${this.listFile[i].name}" class="position-absolute w-100 h-100" />
                        </div>
                    </div>`;
            }
            html += '</div>';
            html += '</div>';
        } else {
            html += '<div class="image-wrapper image-4-3 position-relative">';
            html += '<img src="/assets/frontend/images/blank.gif" alt="" class="position-absolute w-100 h-100" />';
            html += '</div>';
        }
        html += '</div>';
        this.element.html(html);
        this.listThumbnail = $('.list-thumbnail', this.element);
        this.thumbnail = $('.thumbnail', this.element);
    }
}

class Cookie {
    setConsent(value) {
        let banner = $('#cookie-banner');
        if (banner.length == 0) {
            return;
        }

        let gtmId = banner.data('gtm-id');
        banner.remove();

        let self = this;
        $.ajax({
            url: apiCookie.consent,
            type: 'POST',
            data: JSON.stringify({
                consent: value
            }),
            contentType: 'application/json',
            traditional: true,
            success: function(response) {
                if (response.msg == 'success') {
                    if (value == 'accepted') {
                        self.loadGTM(gtmId);
                    }
                } else {
                    console.error(response.err);
                }
            }
        });
    }

    loadGTM(gtmId) {
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            'gtm.start': new Date().getTime(),
            event: 'gtm.js'
        });

        let script = document.createElement('script');
        script.async = true;
        script.src = 'https://www.googletagmanager.com/gtm.js?id=' + gtmId;
        document.head.appendChild(script);

        let noscript = document.createElement('noscript');
        let iframe = document.createElement('iframe');
        iframe.src = 'https://www.googletagmanager.com/ns.html?id=' + gtmId;
        iframe.height = '0';
        iframe.width = '0';
        iframe.style.cssText = 'display:none;visibility:hidden';
        noscript.appendChild(iframe);
        document.body.prepend(noscript);
    }
}

function initializeDatePicker(container, listDateEnabled = [] , optionInit = {}) {
    if (!container) {
        return;
    }

    let id = Utilities.prototype.guid();

    container.attr('data-target', '#' + id);
    container.removeClass('date-picker');
    container.addClass('datetimepicker-input');
    container.wrap('<div id="' + id + '" class="date-picker" data-target-input="nearest"></div>');

    let locale = $('html').attr('lang');
    if (Validate.prototype.isNullOrWhiteSpace(locale)) {
        locale = 'en';
    }
    let option = $.extend(true, {}, {
        format: 'DD/MM/YYYY',
        locale: locale,
        icons: {
            time: 'fa-regular fa-clock',
            date: 'fa-regular fa-calendar-alt',
            up: 'fa-solid fa-chevron-up',
            down: 'fa-solid fa-chevron-down',
            previous: 'fa-solid fa-chevron-left',
            next: 'fa-solid fa-chevron-right',
            today: 'fa-solid fa-bullseye',
            clear: 'fa-regular fa-trash-alt',
            close: 'fa-solid fa-times'
        },
        allowInputToggle: true,
        ignoreReadonly: true
    }, optionInit);
    if (listDateEnabled.length > 0) {
        option.enabledDates = listDateEnabled;
    }

    let wrapper = container.parent();
    wrapper.datetimepicker(option);

    let datePickerInput = container.closest('.date-picker-itinerary');
    if (datePickerInput.length > 0) {
        container.on('focus', function () {
            setTimeout(function () {
                wrapper.trigger('datepicker.opened');
            }, 50);
        });
        wrapper.on('datepicker.opened', function () {
            setTimeout(function () {
                let widget = wrapper.find('.bootstrap-datetimepicker-widget');
                if (widget.length > 0 && !widget.hasClass('rendered')) {
                    widget.append(`
                        <div class="departure-date">
                            <div class="square"></div>
                            <span class="d-block">${commonLabel.departureDate}</span>
                        </div>
                    `);
                    widget.addClass('rendered');
                }
            }, 0);
        });
    }
}

function isEllipsisActive(el) {
    const hasInlineChildren = [...el.children].some(child => {
        return getComputedStyle(child).display === 'inline' ||
            getComputedStyle(child).display === 'inline-block';
    });

    if (!hasInlineChildren) {
        if ((el.scrollWidth > 0 && el.offsetWidth < el.scrollWidth) || (el.scrollHeight > 0 && el.offsetHeight < el.scrollHeight)) {
            return true;
        }
    }
    const computed = getComputedStyle(el);
    const clone = el.cloneNode(true);

    Object.assign(clone.style, {
        position:              'fixed',
        visibility:            'hidden',
        overflow:              'visible',
        whiteSpace:            computed.whiteSpace,
        wordBreak:             computed.wordBreak,
        letterSpacing:         computed.letterSpacing,
        fontSize:              computed.fontSize,
        fontFamily:            computed.fontFamily,
        fontWeight:            computed.fontWeight,
        lineHeight:            computed.lineHeight,
        width:                 el.offsetWidth + 'px',
        height:                'auto',
        maxHeight:             'none',
        webkitLineClamp:       'unset',
        webkitBoxOrient:       'unset',
        display:               'block',
    });
    document.body.appendChild(clone);
    const isClipped = (clone.offsetHeight > 0 && el.offsetHeight > 0 && clone.offsetHeight > el.offsetHeight) || (clone.offsetWidth > 0 && el.offsetWidth > 0 && clone.offsetWidth > el.offsetWidth);
    document.body.removeChild(clone);
    return isClipped;
}

function initEllipsisTooltip(container = $('body')) {
    const $tooltip = $('.give-ellipsis',container);
    $tooltip.each(function () {
        const el = this;
        const $el = $(el);

        if (isEllipsisActive(el)) {
            $el.tooltip({
                boundary: 'window',
                trigger: 'hover',
                placement: 'bottom',
                title: $el.text().trim(),
                template: `
                    <div class="custom-tooltip tooltip" role="tooltip">
                        <div class="arrow"></div>
                        <div class="tooltip-inner"></div>
                    </div>
                `,
                popperConfig: {
                    modifiers: {
                        flip: {
                            behavior: ['bottom','top', 'right', 'left']
                        }
                    }
                }
            });
        } else {
            // destroy if previously initialized but no longer clamped
            if ($el.data('bs.tooltip')) {
                $el.tooltip('dispose');
            }
        }
    });
}

var fnAlignCarouselNavToImage = function ($carousel) {
    if (!$carousel || !$carousel.length) {
        return;
    }

    let $header = $carousel.find('.owl-item:not(.cloned) .item-header').first();
    let $nav = $carousel.find('.owl-nav');
    if (!$header.length || !$nav.length) {
        return;
    }

    let carouselTop = $carousel.offset().top;
    let headerTop = $header.offset().top;
    let headerHeight = $header.outerHeight();

    if (!headerHeight) {
        return;
    }

    $nav.css({
        top: (headerTop - carouselTop) + 'px',
        height: headerHeight + 'px'
    });
};

var fnListItemCarouselOnInit = function (event) {
    let target = $(event.target);
    let item = $('.item', target);
    let maxHeight = 0;

    item.removeClass('h-100');
    item.each(function (index) {
        let height = $(this).outerHeight();
        if (height > maxHeight) {
            maxHeight = height;
        }
        $(this).addClass('h-100');
    });
    $('.owl-item', target).css({ height: maxHeight + 'px' });

    let isHomeCuratedJourneys = target.closest('#home .section-4').length > 0;
    let isHomeSuitesMobile = target.closest('#home .section-6').length > 0 && $(window).width() < 1024;
    let isCruiseSuitesMobile = target.closest('#cruise-detail .section-cabin').length > 0
        && target.hasClass('suites-grid')
        && $(window).width() < 1024;
    let isItinerarySuitesMobile = target.closest('#itinerary-detail .section-cabin').length > 0
        && target.hasClass('suites-grid')
        && $(window).width() < 1024;
    if (isHomeCuratedJourneys || isHomeSuitesMobile || isCruiseSuitesMobile || isItinerarySuitesMobile) {
        fnAlignCarouselNavToImage(target);
        target.find('.owl-prev').attr('aria-label', 'Previous slide');
        target.find('.owl-next').attr('aria-label', 'Next slide');
    }

    initEllipsisTooltip(target);
};

function initFabTooltip($el){
    if($el.data('bs.tooltip')){
        $el.tooltip('dispose');
    }

    if($(window).width() < 576) return;

    $el.tooltip({
        boundary: 'window',
        trigger: 'hover',
        template: `
                    <div class="btn-tooltip tooltip" role="tooltip">
                        <div class="tooltip-inner"></div>
                    </div>
                `,
    });
}

$(document).ready(function () {
    $.ajaxPrefilter(function (options, originalOptions, jqXhr) {
        if (options.type.toUpperCase() === 'POST') {
            let token = $('input[name^=_token]').first();
            if (!token.length) return;

            let tokenName = token.attr('name');

            if (options.contentType === false) {
                return;
            };

            if (options.contentType.indexOf('application/json') === 0) {
                options.url += ((options.url.indexOf('?') === -1) ? '?' : '&') + token.serialize();
            } else if (typeof options.data === 'string' && options.data.indexOf(tokenName) === -1) {
                options.data += (options.data ? '&' : '') + token.serialize();
            }
        }
    });

    $('.date-picker').each(function () {
        initializeDatePicker($(this));
    });

    $('a').click(function(e) {
        let href = $(this).attr('href');
        if (Validate.prototype.isNullOrWhiteSpace(href) || !href.includes('#') || href == '#chat-with-ai') {
            return;
        }

        let list = href.split('#');
        if (list.length == 0) {
            return;
        }

        let id = list[list.length - 1];
        if (!Validate.prototype.isNullOrWhiteSpace(id)) {
            let target = $('#' + id);
            if (target.length == 0) {
                return;
            }

            e.preventDefault();
            $('html, body').animate({
                scrollTop: target.offset().top
            }, 800);
        }
    });

    let frmSearch = $('.frm-search');
    let btnSearch = $('.btn-search', frmSearch);
    btnSearch.click(function () {
        let frmSearch = $(this).closest('form');
        // let txtKeyword = $('[name="k"]', frmSearch);
        // let keyword = txtKeyword.val();
        // if (Validate.prototype.isNullOrWhiteSpace(keyword)) {
        //     alert('Please enter the search keyword!');
        //     txtKeyword.focus();
        //     return;
        // }

        frmSearch.submit();
    });

    let txtKeyword = $('[name="k"]', frmSearch);
    txtKeyword.on('keyup keydown', function (e) {
        if (e.which == 13) {
            e.preventDefault();
            if (e.type === 'keyup') {
                let frmSearch = $(this).closest('form');
                let btnSearch = $('.btn-search', frmSearch);
                btnSearch.trigger('click');
            }
        }
        let val = $(this).val();
        txtKeyword.val(val);
    });

    $('.article-content a[data-fancybox]').each(function (i) {
        let html = '<div class="hint d-flex position-absolute text-white">' +  (i == 0 ? '<span class="d-flex align-items-center float-left mr-2">' + commonLabel.viewFullScreen + '</span>' : '') +' <i class="fa-solid fa-expand"></i></div>';

        $(this).append(html);
    });

    let header = $('#header');
    let headerDesktop = $('.header-desktop', header);
    let headerMobile = $('.header-mobile',header);
    let menuMobile = $('#menu-mobile');
    let fabs = $('#fabs');

    // Menu Extend
    let btnToggleMenuExt = $('.btn-toggle-menu-ext', headerDesktop);
    let menuExt = $('.menu-ext', btnToggleMenuExt);

    $(document).click(function (e) {
        if ($(e.target).is(btnToggleMenuExt) || $(e.target).closest('.btn-toggle-menu-ext').is(btnToggleMenuExt)) {
            return;
        }

        menuExt.removeClass('show');
    });

    btnToggleMenuExt.click(function () {
        menuExt.toggleClass('show');
    });

    // Navigation
    let navigation = $('.navigation', headerDesktop);
    $('ul > li.has-child', navigation).hover(function () {
        menuExt.removeClass('show');
    });

    // Menu mobile
    let btnExpandMenuMobile = $('.btn-expand-menu-mobile',headerMobile);

    btnExpandMenuMobile.on('click',function(e){
        e.preventDefault();
        menuMobile.addClass('expand');
        $('body').addClass('menu-mobile-show')
    })

    menuMobile.on('click', '.body .navigation .list-item .item.has-child > .item-body', function(e) {
        $(this).closest('.item').toggleClass('show');
    });

    menuMobile.on('click','.header .btn-close-menu,.overlay',function(){
        menuMobile.removeClass('expand');
        $('body').removeClass('menu-mobile-show')
    })

    // Fabs
    fabs.on('click','.btn-navigate-to-top',function(){
        $('html, body').animate({ scrollTop: 0 }, { duration: 800, easing: 'swing' });
    })

    let fnChatWithAi = function() {
        if (typeof swalAlert !== 'undefined' && swalAlert && typeof swalAlert.alert === 'function') {
            swalAlert.alert({
                icon: 'warning',
                title: commonLabel.functionUnderDevelopment,
                customClass: {
                    popup: 'swal2-popup--dev',
                    icon: 'swal2-icon--dev'
                }
            });
            return;
        }

        alert(commonLabel.functionUnderDevelopment);
    }

    // Chat with AI
    $('.btn-chat-with-ai').click(fnChatWithAi);
    $('#footer .footer-nav-link[href="#chat-with-ai"]').click(fnChatWithAi);

    // List FAQ (exclude FAQ page — handled in modules/faq/index.js)
    let listFaq = $('.list-faq').not('#faq-category .list-faq');
    let btnToggleFaq = $('.item .btn-toggle', listFaq);
    btnToggleFaq.on('click', function () {
        let btn = $(this);
        let item = btn.closest('.item');
        if (item.hasClass('expand')) {
            $('i', btn).removeClass('fa-minus').addClass('fa-plus');
            item.removeClass('expand');
        } else {
            $('i', btn).removeClass('fa-plus').addClass('fa-minus');
            item.addClass('expand');
        }
    });

    if ($.fn.fancybox) {
        $('[data-fancybox="gallery"]').fancybox({
            loop: true,
            buttons: ["zoom", "slideShow", 'download',"fullScreen", 'thumbs',"close"],
            animationEffect: "zoom",
        });
    }

    // if (window.location.hash) {
    //     let target = $(window.location.hash);
    //     if (target.length > 0) {
    //         $('html, body').scrollTop(0);
    //         setTimeout(function () {
    //             $('html, body').animate({
    //                     scrollTop: target.offset().top
    //                 }, 800);
    //         }, 100);
    //     }
    // }

    initEllipsisTooltip();
    initFabTooltip($('.btn-tooltip',fabs))

    let tooltipResizeTimer;
    $(window).on('resize', function () {
        clearTimeout(tooltipResizeTimer);
        tooltipResizeTimer = setTimeout(function(){
            initEllipsisTooltip();
            initFabTooltip($('.btn-tooltip',fabs))
        }, 150);
    });
});
