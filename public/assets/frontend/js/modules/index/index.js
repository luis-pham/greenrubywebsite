$(document).ready(function () {
    let runWhenNearViewport = function (selector, callback, rootMargin) {
        let target = $(selector).get(0);
        if (!target || typeof callback !== 'function') {
            return;
        }

        if (!('IntersectionObserver' in window)) {
            callback();
            return;
        }

        let hasRun = false;
        let observer = new IntersectionObserver(function (entries) {
            if (hasRun) {
                return;
            }

            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }

                hasRun = true;
                observer.disconnect();
                callback();
            });
        }, {
            rootMargin: rootMargin || '0px 0px 200px 0px',
            threshold: 0
        });

        observer.observe(target);
    };

    /*let searchTour = async function (param) {
        let data = {
            data: null,
            status: null
        };

        await $.ajax({
            url: apiHomepage.searchTour,
            type: 'POST',
            data: param,
            success: function(response) {
                if (response.msg == 'success') {
                    data.data = response.data;
                    data.status = apiStatus.success;
                } else {
                    data.data = null;
                    data.status = apiStatus.error;

                    console.error(response.err);
                }
            }
        });

        return data;
    };

    let bindInputDate = async function (element, param) {
        let data = await searchTour(param);
        let listDateEnabled = (data.data || []).map(item => item.start_at);
        let wrapper = element.closest('.date-picker');
        let picker = wrapper.data('DateTimePicker') || wrapper.data('datetimepicker');
        if (picker) {
            let oldOptions = picker.options ? picker.options() : {};
            let newOptions = $.extend(true, {}, oldOptions, {
                enabledDates: listDateEnabled
            });
            try {
                wrapper.datetimepicker('destroy');
            } catch (e) {
                console.warn('destroy datetimepicker error', e);
            }
            wrapper.datetimepicker(newOptions);
        } else {
            initializeDatePicker(element, listDateEnabled, {
                widgetPositioning: {
                    horizontal: 'right',
                }
            });
        }
        element.prop('disabled', listDateEnabled.length == 0);

        // if (listDateEnabled.length > 0) {   
        //     txtDate.val(moment(listDateEnabled[0]).format('DD/MM/YYYY'));
        // } else {
        //     txtDate.val(moment().format('DD/MM/YYYY'));
        // }
    };

    // Search tour
    let frmSearchTour = $('#home .section-2 .info-bar');
    let lsbCruise = $('select[name="cruise"]', frmSearchTour);
    let lsbItinerary = $('select[name="itinerary"]', frmSearchTour);
    let txtDate = $('input[name="date"]', frmSearchTour);
    let lsbGuest = $('select[name="guest"]', frmSearchTour);
    let sectionItinerary = $('#home .section-4');
    let listItinerary = $('.list-itinerary-cruise', sectionItinerary);
    let btnTabFilterItineraryAll = $('.tab-filter .list-button .item button[data-bay=""]', sectionItinerary);

    // if (txtDate.length > 0 && typeof txtDate[0].showPicker === 'function') {
    //     txtDate.on('click focus', function () {
    //         try {
    //             this.showPicker();
    //         } catch (e) {
                
    //         }
    //     });
    // }

    lsbCruise.on('change', async function() {
        txtDate.prop('disabled', true);
        btnSearch.prop('disabled', true);
        await bindInputDate(txtDate, {
            cruise_id: $(this).val(),
            itinerary_id: lsbItinerary.val(),
            guest: lsbGuest.val()
        });
        txtDate.prop('disabled', false);
        btnSearch.prop('disabled', false);
    });

    lsbItinerary.on('change', async function() {
        txtDate.prop('disabled', true);
        btnSearch.prop('disabled', true);
        bindInputDate(txtDate, {
            cruise_id: lsbCruise.val(),
            itinerary_id: $(this).val(),
            guest: lsbGuest.val()
        });
        txtDate.prop('disabled', false);
        btnSearch.prop('disabled', false);
    });

    if (txtDate.length > 0) {
        bindInputDate(txtDate, {
            cruise_id: lsbCruise.val(),
            itinerary_id: lsbItinerary.val(),
            guest: lsbGuest.val()
        });
    }

    let btnSearch = $('button', frmSearchTour);
    btnSearch.on('click', async function () {
        UserInterface.prototype.showLoading();
        let data = await searchTour({
            cruise_id: lsbCruise.val(),
            itinerary_id: lsbItinerary.val(),
            date: txtDate.val(),
            guest: lsbGuest.val()
        });

        if (data.status == apiStatus.success) {
            btnTabFilterItineraryAll.trigger('click');
            $('.item', listItinerary).addClass('disabled');
           
            let list = data.data;
            for (let i = 0; i < list.length; i++) {
                $('.item[data-itinerary-id="' + list[i].itinerary_id + '"][data-cruise-id="' + list[i].cruise_id + '"]', listItinerary).removeClass('disabled');
            }

            $('html, body').animate({
                scrollTop: sectionItinerary.offset().top
            }, 800);
        }

        UserInterface.prototype.hideLoading();
    });

    let searchInputWrapper = $('.info-item-wrapper', frmSearchTour);
    let listValue = $('.list-value', frmSearchTour);
    searchInputWrapper.click(function (e) {
        let searchInput = $(this).closest('.info-item');
        
        let allSearchInput = $('.info-item', frmSearchTour);
        allSearchInput.each(function () {
            if (!$(this).is(searchInput)) {
                $('.list-value', $(this)).removeClass('show');
            }
        });

        $('.list-value', searchInput).toggleClass('show');
    });

    $('.item', listValue).click(function () {
        let item = $(this);
        let listValue = item.closest('.list-value');
        if (!item.hasClass('selected')) {
            $('.item', listValue).removeClass('selected');
            item.addClass('selected');
            
            let searchInput = item.closest('.info-item');
            let select = $('select', searchInput);
            let id = item.attr('data-id');
            select.val(id).trigger('change');
        }
        
        listValue.removeClass('show');
    });

    $(document).click(function (e) {
        let searchInputElement = $('*', searchInputWrapper);
        if ($(e.target).is(searchInputWrapper) || $(e.target).is(searchInputElement)) {
            return;
        }

        listValue.removeClass('show');
    });*/

    // Slide sustainability
    runWhenNearViewport('#home .section-3', function () {
        $('#home .section-3 .slide-1 .owl-carousel').owlCarousel({
            loop: false,
            dots: true,
            nav: false,
            navText: [
                '<div class="button"><i class="fa-solid fa-chevron-left"></i></div>',
                '<div class="button"><i class="fa-solid fa-chevron-right"></i></div>'
            ],
            items: 1,
            margin: 0,
            autoplay: false,
            autoplayTimeout: 5000,
            smartSpeed: 400,
            responsiveClass: true,
            responsive: {
                1024: {
                    nav: true,
                    dots: false,
                    items: 2
                },
                1200: {
                    nav: true,
                    dots: false,
                    items: 3
                },
                1400: {
                    nav: true,
                    dots: false,
                    items: 4
                }
            },
            onInitialized: fnListItemCarouselOnInit,
            onResized: fnListItemCarouselOnInit
        });
    }, '0px 0px 300px 0px');

    // Section service
    let sectionService = $('#home .section-7');
    runWhenNearViewport('#home .section-7', function () {
        let btnTabFilterService = $('.tab-filter .list-button .item button', sectionService);
        btnTabFilterService.on('click', function () {
            let btn = $(this);
            if (btn.hasClass('active')) {
                return;
            }

            let section = btn.closest('section');
            let btnFilter = $('.tab-filter .list-button .item button', section);
            btnFilter.removeClass('active');
            btn.addClass('active');

            let idx = btnTabFilterService.index(this);
            $('.list-item .item', section).addClass('d-none');
            $('.list-item .item:eq(' + idx + ')', section).removeClass('d-none');
        });
    }, '0px 0px 400px 0px');

    // Slide exp activity
    runWhenNearViewport('#home .section-9', function () {
        let sectionExpActivity = $('#home .section-9');
        let listExpActivity = $('.list-item', sectionExpActivity);
        let listExpActivityHtml = listExpActivity.html();

        let slideExpActivity = $('.slide-1 .owl-carousel', sectionExpActivity);
        let slideExpActivityConfig = {
            loop: false,
            dots: true,
            nav: true,
            navText: [
                '<div class="button" aria-hidden="true"><i class="fa-solid fa-chevron-left"></i></div>',
                '<div class="button" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></div>'
            ],
            startPosition: 1,
            items: 1,
            margin: 16,
            autoplay: false,
            autoplayTimeout: 5000,
            smartSpeed: 400,
            responsiveClass: true,
            responsive: {
                768: {
                    items: 1,
                    margin: 32,
                    center: true,
                    stagePadding: 150
                },
                992: {
                    items: 1,
                    margin: 32,
                    center: true,
                    stagePadding: 200
                },
                1200: {
                    items: 1,
                    margin: 32,
                    center: true,
                    stagePadding: 330
                },
                1600: {
                    items: 1,
                    margin: 32,
                    center: true,
                    stagePadding: 530
                }
            },
            onInitialized: fnListItemCarouselOnInit,
            onResized: fnListItemCarouselOnInit
        };
        slideExpActivity.owlCarousel(slideExpActivityConfig);

        let btnTabFilterExpActivity = $('.tab-filter .list-button .item button', sectionExpActivity);
        btnTabFilterExpActivity.on('click', function () {
            let btn = $(this);
            if (btn.hasClass('active')) {
                return;
            }

            let section = btn.closest('section');
            let btnFilter = $('.tab-filter .list-button .item button', section);
            btnFilter.removeClass('active');
            btn.addClass('active');

            slideExpActivity.trigger('destroy.owl.carousel');
            let groupId = btn.attr('data-id');
            if (!Validate.prototype.isNullOrWhiteSpace(groupId)) {
                let html = '';
                $(listExpActivityHtml).filter('.item').each(function () {
                    if ($(this).attr('data-group-id') == groupId) {
                        html += this.outerHTML;
                    }
                });
                listExpActivity.html(html);
            } else {
                listExpActivity.html(listExpActivityHtml);
            }
            slideExpActivity.owlCarousel(slideExpActivityConfig);
        });
    }, '0px 0px 400px 0px');
});