$(document).ready(function () {
    let modal = $('.modal-service');

    modal.on('hidden.bs.modal', function() {
        $('.modal-title', modal).text('');
        $('.main-info-title', modal).text('');
        let slideImage = $('.slide-1 .list-image', modal);
        if (slideImage.data('owl.carousel')) {
            slideImage.trigger('destroy');
        }
        $('.image', modal).html('');
        $('.service-description', modal).text('');
        $('.service-info-price', modal).addClass('d-none');
        $('.service-price', modal).text('');
        $('.service-type', modal).addClass('d-none');
        $('.service-type-label', modal).text('');
    });

    let btnViewDetails = $('.btn-view-service-details');
    btnViewDetails.on('click', async function(e) {
        e.preventDefault();

        let btn = $(this);
        let id = btn.data('id');
        if (Validate.prototype.isNullOrWhiteSpace(id)) {
            return;
        }

        let data = null;


        let titleEllipsis = btn.closest('h3.give-ellipsis');
        let imageWrapper = btn.find('.image-wrapper');

        if (titleEllipsis && titleEllipsis.length) {
            btn.addClass('btn-view-details-disabled');
            titleEllipsis.addClass('btn-loading-title-local');
        } else if (imageWrapper && imageWrapper.length) {
            btn.addClass('btn-view-details-disabled');
            imageWrapper.addClass('image-loading-dim');
        } else {
            btn.addClass('btn-loading-local');
        }

        await $.ajax({
            url: apiService.getById,
            type: 'GET',
            data: {
                id: id
            },
            success: function(response) {
                if (response.msg == 'success') {
                    data = response.data;
                } else {
                    console.error(response.err);
                }
            },
            complete: function() {
                titleEllipsis && titleEllipsis.length && titleEllipsis.removeClass('btn-loading-title-local');
                imageWrapper && imageWrapper.length && imageWrapper.removeClass('image-loading-dim');
                btn.removeClass('btn-loading-local');
                btn.removeClass('btn-view-details-disabled');
            }
        });

        if (data != null) {
            $('.modal-title', modal).text(data.name || '');
            $('.main-info-title', modal).text(data.name || '');

            let modalBody = $('.modal-body', modal);

            let files = Array.isArray(data.file) ? data.file : [];
            if (files.length === 0 && !Validate.prototype.isNullOrWhiteSpace(data.image_link)) {
                files = [{
                    link: data.image_link,
                    name: data.name || ''
                }];
            }

            let carouselGallery = new CarouselGallery($('.image', modalBody), files);
            carouselGallery.init();

            $('.service-description', modalBody).text(data.description || '');

            if (!Validate.prototype.isNullOrWhiteSpace(data.price_formatted)) {
                $('.service-info-price', modalBody).removeClass('d-none');
                $('.service-price', modalBody).html(apiService.priceFormat.replace('__PRICE__', data.price_formatted));
            }

            if (data.group_name) {
                $('.service-type-label', modalBody).text(data.group_name);
                $('.service-type', modalBody).removeClass('d-none');
            }

            modal.modal('show');
        }
    });
});

