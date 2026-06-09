

function adjustServicePriceLayout() {
    var $grid = $('#service .section-2 .list-itinerary-cruise.row');
    if ($grid.length) {
        var $cols = $grid.find('> [class*="col-"]');

        // Reset
        $cols.find('.item-footer-content').removeClass('is-column');
        $cols.find('.price').css('min-height', '');

        if ($(window).width() >= 992) {
            // Nhóm col theo hàng grid
            var rowGroups = {};
            $cols.each(function () {
                var top = Math.round($(this).offset().top);
                if (!rowGroups[top]) rowGroups[top] = [];
                rowGroups[top].push(this);
            });

            $.each(rowGroups, function (top, colItems) {
                var hasWrapped = false;

                $(colItems).each(function () {
                    var $price = $(this).find('.price');
                    var $value = $price.find('.value');
                    if (!$price.length || !$value.length) return;
                    if ($price.height() > $value.outerHeight() + 4) {
                        hasWrapped = true;
                        return false;
                    }
                });

                if (!hasWrapped) return;

                // Cả hàng → column layout + equalize
                $(colItems).find('.item-footer-content').addClass('is-column');

                var maxH = 0;
                $(colItems).each(function () {
                    var h = $(this).find('.price').height();
                    if (h > maxH) maxH = h;
                });
                if (maxH > 0) {
                    $(colItems).find('.price').css('min-height', maxH + 'px');
                }
            });
        }
    }

    // ── CAROUSEL (mobile < lg) ───────────────────────────────────────
    var $carousel = $('#service .section-2 .slide-1 .list-itinerary-cruise.owl-carousel');
    if ($carousel.length) {
        var $items = $carousel.find('.owl-item:not(.cloned) .item');

        $items.find('.item-footer-content').removeClass('is-column');
        $items.find('.price').css('min-height', '');

        var anyWrapped = false;
        $items.each(function () {
            var $price = $(this).find('.price');
            var $value = $price.find('.value');
            if (!$price.length || !$value.length) return;
            if ($price.height() > $value.outerHeight() + 4) {
                anyWrapped = true;
                return false;
            }
        });

        if (anyWrapped) {
            // Chuyển tất cả sang column layout
            $items.find('.item-footer-content').addClass('is-column');

            // Equalize price height cho toàn carousel — vì carousel hiển thị
            // 2 items cùng lúc (540–991px), cần các price bằng nhau để không lệch
            var maxH = 0;
            $items.each(function () {
                var h = $(this).find('.price').height();
                if (h > maxH) maxH = h;
            });
            if (maxH > 0) {
                $items.find('.price').css('min-height', maxH + 'px');
            }
        }
    }
}

$(document).ready(function () {
    $('#service .section-2 .slide-1 .owl-carousel').owlCarousel({
        loop: false,
        dots: false,
        nav: true,
        navText: [
            '<div class="button"><i class="fa-solid fa-chevron-left"></i></div>',
            '<div class="button"><i class="fa-solid fa-chevron-right"></i></div>'
        ],
        items: 1,
        margin: 12,
        autoplay: false,
        smartSpeed: 400,
        responsiveClass: true,
        responsive: {
            540: { dots: false, items: 2 },
            992: { dots: false, items: 3 },
            1200: { dots: false, items: 3 }
        },
    });

    $('#service .section-2 .slide-1 .owl-carousel').on(
        'initialized.owl.carousel resized.owl.carousel',
        function () {
            requestAnimationFrame(function () {
                requestAnimationFrame(adjustServicePriceLayout);
            });
        }
    );

    var $cabinCarousel = $('#service .section-3 .cabin-carousel');

    if ($cabinCarousel.length && $.fn.owlCarousel) {
        $cabinCarousel.owlCarousel({
            items: 1,
            loop: false,
            nav: false,
            dots: false,
            autoplay: false,
            autoplayTimeout: 5000
        });

        function updateCabinInfo($slide) {
            if (!$slide.length) return;

            $('#cabin-name').text($slide.data('name') || '');
            $('#cabin-summary').text($slide.data('summary') || '');

            var capacity = $slide.data('capacity');
            if (capacity) {
                var $capEl = $('#cabin-capacity');
                var tpl = $capEl.data('template') || '';
                $capEl.text(tpl ? tpl.replace(':capacity', capacity).replace(':cabin_name', $slide.data('name') || '') : capacity);
            }
        }

        $cabinCarousel.on('changed.owl.carousel', function (e) {
            var index = (e.item && typeof e.item.index !== 'undefined') ? e.item.index : 0;
            var $slide = $(this).find('.owl-item').eq(index).find('.cabin-slide');
            updateCabinInfo($slide);
        });
    }

    var $sustainabilityCarousel = $('#service .section-3 .section-content-sustainability .slide-1 .list-item-sustainability.owl-carousel');
    if ($sustainabilityCarousel.length && $.fn.owlCarousel) {
        if ($sustainabilityCarousel.hasClass('owl-loaded')) {
            $sustainabilityCarousel.trigger('destroy.owl.carousel');
            $sustainabilityCarousel.removeClass('owl-loaded');
            $sustainabilityCarousel.find('.owl-stage-outer').children().unwrap();
        }

        $sustainabilityCarousel.owlCarousel({
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
            autoWidth: false,
            stagePadding: 0,
            responsive: {
                540: { dots: false, items: 2, autoWidth: false, stagePadding: 0 },
                992: { dots: false, items: 3, autoWidth: false, stagePadding: 0 },
                1200: { dots: false, items: 3, autoWidth: false, stagePadding: 0 }
            },
            onInitialized: function (event) {
                fnListItemCarouselOnInit(event);
                $(event.target).trigger('refresh.owl.carousel');
            },
            onResized: fnListItemCarouselOnInit
        });
    }

    let sectionService = $('.section-3');
    let btnFilterService = $('.filter .list-item .item button', sectionService);

    btnFilterService.on('click', function () {
        let btn = $(this);
        let section = btn.closest('section');
        btnFilterService.removeClass('active');
        btn.addClass('active');
        let idx = btnFilterService.index(this);
        $('.list-service .item', section).addClass('d-none');
        $('.list-service .item:eq(' + idx + ')', section).removeClass('d-none');
    });

    var quoteMessages = window.quoteMessages || {};

    function clearQuoteErrors($form) {
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.quote-error').text('');
    }

    function setQuoteError($field, errorSpanId, message) {
        $field.addClass('is-invalid');
        $('#' + errorSpanId).text(message);
    }

    function validateQuoteForm($form) {
        var valid = true;
        var msgs = quoteMessages;
        var contactName = $.trim($form.find('[name="contact_name"]').val());
        var phone       = $.trim($form.find('[name="phone"]').val());
        var eventType   = $form.find('[name="event_type"]').val();
        var number      = $.trim($form.find('[name="number"]').val());

        if (!contactName) {
            setQuoteError($form.find('[name="contact_name"]'), 'error-contact_name', msgs.contact_name_required);
            valid = false;
        } else if (contactName.length > 100) {
            setQuoteError($form.find('[name="contact_name"]'), 'error-contact_name', msgs.contact_name_max);
            valid = false;
        }

        if (!phone) {
            setQuoteError($form.find('[name="phone"]'), 'error-phone', msgs.phone_required);
            valid = false;
        } else if (phone.length > 20) {
            setQuoteError($form.find('[name="phone"]'), 'error-phone', msgs.phone_max);
            valid = false;
        } else if (!/^[0-9+\s\-()]{7,20}$/.test(phone)) {
            setQuoteError($form.find('[name="phone"]'), 'error-phone', msgs.phone_invalid);
            valid = false;
        }

        if (!eventType) {
            setQuoteError($form.find('[name="event_type"]'), 'error-event_type', msgs.event_type_required);
            valid = false;
        }

        if (number === '') {
            setQuoteError($form.find('[name="number"]'), 'error-number', msgs.number_required);
            valid = false;
        } else if (!/^\d+$/.test(number)) {
            // Not a non-negative integer
            setQuoteError($form.find('[name="number"]'), 'error-number', msgs.number_invalid);
            valid = false;
        }

        return valid;
    }

    // Run after fonts/images are fully loaded for accurate height measurement
    $(window).on('load', function () {
        adjustServicePriceLayout();
    });

    // Re-equalize on resize (debounced)
    var _equalizeTimer;
    $(window).on('resize', function () {
        clearTimeout(_equalizeTimer);
        _equalizeTimer = setTimeout(function () {
            adjustServicePriceLayout();
            setTimeout(adjustServicePriceLayout, 300);
        }, 150);
    });

    $('#form-quote-request').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        var url = $form.data('api-url');
        var msgs = quoteMessages;
        var $btn = $('#btn-quote-submit');
        var $msg = $('#quote-form-message');

        clearQuoteErrors($form);
        $msg.addClass('d-none').removeClass('alert-success alert-danger');
        $('#quote-form-message-text').empty();

        if (!validateQuoteForm($form)) return;

        $btn.prop('disabled', true);

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                contact_name: $form.find('[name="contact_name"]').val(),
                phone: $form.find('[name="phone"]').val(),
                event_type: $form.find('[name="event_type"]').val() || null,
                number: $form.find('[name="number"]').val() ? parseInt($form.find('[name="number"]').val(), 10) : null,
                note: $form.find('[name="note"]').val() || null
            },
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).done(function (res) {
            $form[0].reset();
            $('#quoteSuccessModal').modal('show');
        }).fail(function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                var errors = xhr.responseJSON.errors;
                var fieldMap = {
                    contact_name: { field: $form.find('[name="contact_name"]'), span: 'error-contact_name' },
                    phone:        { field: $form.find('[name="phone"]'),         span: 'error-phone' },
                    event_type:   { field: $form.find('[name="event_type"]'),    span: 'error-event_type' },
                    number:       { field: $form.find('[name="number"]'),        span: 'error-number' }
                };
                $.each(errors, function (key, messages) {
                    if (fieldMap[key]) {
                        setQuoteError(fieldMap[key].field, fieldMap[key].span, messages[0]);
                    }
                });
            } else {
                var errMsg = msgs.error_generic;
                if (xhr.responseJSON && xhr.responseJSON.message) errMsg = xhr.responseJSON.message;
                $msg.removeClass('d-none').addClass('alert-danger').removeClass('alert-success');
                $('#quote-form-message-text').text(errMsg);
            }
        }).always(function () {
            $btn.prop('disabled', false);
        });
    });
});
