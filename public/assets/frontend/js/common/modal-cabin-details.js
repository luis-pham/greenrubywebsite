$(document).ready(function () {
    let modal = $('.modal-cabin').first();
    if (!modal.length) {
        return;
    }

    if (!modal.parent().is('body')) {
        modal.appendTo(document.body);
    }

    modal.on('hidden.bs.modal', function() {
        $('.modal-title', modal).text('');
        let slideImage = $('.slide-1 .list-image', modal);
        if (slideImage.data('owl.carousel')) {
            slideImage.trigger('destroy.owl.carousel');
        }
        $('.image', modal).html('');
        $('.list-amenity', modal).remove();
    });

    $(document).on('click', '.btn-view-cabin-details', async function(e) {
        e.preventDefault();

        let btn = $(this);
        let id = btn.attr('data-id');
        if (Validate.prototype.isNullOrWhiteSpace(id)) {
            return;
        }

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

        let data = null;

        try {
            await $.ajax({
                url: apiCabin.getById,
                type: 'GET',
                data: {
                    id: id
                },
                success: function(response) {
                    if (response.msg == 'success') {
                        data = response.data;
                    } else {
                        console.error(response.err || 'Failed to load cabin details.');
                    }
                },
                error: function(xhr) {
                    console.error('Cabin details request failed.', xhr.status, xhr.responseText);
                },
                complete: function() {
                    titleEllipsis && titleEllipsis.length && titleEllipsis.removeClass('btn-loading-title-local');
                    imageWrapper && imageWrapper.length && imageWrapper.removeClass('image-loading-dim');
                    btn.removeClass('btn-loading-local btn-view-details-disabled');
                }
            });
        } catch (err) {
            console.error('Cabin details request failed.', err);
            btn.removeClass('btn-loading-local btn-view-details-disabled');
            titleEllipsis && titleEllipsis.length && titleEllipsis.removeClass('btn-loading-title-local');
            imageWrapper && imageWrapper.length && imageWrapper.removeClass('image-loading-dim');
            return;
        }

        if (data == null) {
            return;
        }

        $('.modal-title', modal).text(data.name || '');

        let modalBody = $('.modal-body', modal);
        $('.list-amenity', modalBody).remove();

        let files = Array.isArray(data.file) ? data.file : [];
        let carouselGallery = new CarouselGallery($('.image', modalBody), files);
        carouselGallery.init();

        let amenities = Array.isArray(data.amenity) ? data.amenity : [];
        if (amenities.length > 0) {
            let html = '<div class="list-amenity row">';
            for (let i = 0; i < amenities.length; i++) {
                html +=
                    `<div class="item col-md-6 col-lg-12">
                        <div class="item-wrapper media">
                            <div class="mr-2">
                                <div class="image-wrapper position-relative">
                                    <img src="${amenities[i].amenity_icon}" alt="${amenities[i].amenity_name}" class="position-absolute w-100 h-100" />
                                </div>
                            </div>
                            <div class="media-body">
                                <p class="title mb-0">${amenities[i].amenity_name}</p>
                            </div>
                        </div>
                    </div>`;
            }
            html += '</div>';
            $('.main-info-title', modalBody).after(html);
        }

        modal.modal('show');
    });
});
