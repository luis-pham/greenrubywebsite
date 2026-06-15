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
        if (slideImage.length && slideImage.data('owl.carousel')) {
            slideImage.trigger('destroy.owl.carousel');
        }
        $('.image', modal).empty();
        $('.list-amenity', modal).remove();
    });

    $(document).on('click mousedown', '.btn-view-cabin-details', function(e) {
        e.stopPropagation();
    });

    $(document).on('click', '.btn-view-cabin-details', async function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (typeof apiCabin === 'undefined' || !apiCabin.getById) {
            console.error('Cabin API is not configured.');
            return;
        }

        let btn = $(this);
        let id = btn.attr('data-id');
        if (Validate.prototype.isNullOrWhiteSpace(id)) {
            console.error('Cabin id is missing on view button.');
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
            data = await $.ajax({
                url: apiCabin.getById,
                type: 'GET',
                data: {
                    id: id
                }
            }).then(function(response) {
                if (response && response.msg === 'success') {
                    return response.data;
                }

                throw new Error((response && response.err) || 'Failed to load cabin details.');
            });
        } catch (err) {
            console.error('Cabin details request failed.', err);
            alert('Unable to load cabin details. Please try again.');
        } finally {
            titleEllipsis && titleEllipsis.length && titleEllipsis.removeClass('btn-loading-title-local');
            imageWrapper && imageWrapper.length && imageWrapper.removeClass('image-loading-dim');
            btn.removeClass('btn-loading-local btn-view-details-disabled');
        }

        if (data == null) {
            return;
        }

        $('.modal-title', modal).text(data.name || '');

        let modalBody = $('.modal-body', modal);
        $('.list-amenity', modalBody).remove();
        $('.image', modalBody).empty();

        modal.modal('show');

        let files = Array.isArray(data.file) ? data.file : [];
        if (files.length === 0 && !Validate.prototype.isNullOrWhiteSpace(data.image_link)) {
            files = [{
                link: data.image_link,
                name: data.name || ''
            }];
        }

        try {
            let carouselGallery = new CarouselGallery($('.image', modalBody), files);
            carouselGallery.init();
        } catch (err) {
            console.error('Cabin gallery init failed.', err);
        }

        let amenities = Array.isArray(data.amenity) ? data.amenity : [];
        if (amenities.length > 0) {
            let html = '<div class="list-amenity row">';
            for (let i = 0; i < amenities.length; i++) {
                html +=
                    `<div class="item col-md-6 col-lg-12">
                        <div class="item-wrapper media">
                            <div class="mr-2">
                                <div class="image-wrapper icon-on-light position-relative" style="--icon-mask: url('${amenities[i].amenity_icon}');">
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
    });
});
