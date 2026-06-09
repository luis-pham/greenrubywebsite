$(document).ready(function () {
    let modal = $('.modal-cabin');
    modal.on('hidden.bs.modal', function() {
        $('.modal-title', modal).text('');
        let slideImage = $('.slide-1 .list-image', modal);
        if (slideImage.data('owl.carousel')) {
            slideImage.trigger('destroy');
        }
        $('.image', modal).html('');
        $('.list-amenity', modal).remove();
    });

    let btnViewDetails = $('.btn-view-cabin-details');
    btnViewDetails.on('click', async function(e) {
        e.preventDefault();

        let btn = $(this);
        let id = btn.data('id');
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

        // UserInterface.prototype.showLoading();
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
                    console.error(response.err);
                }
            },
            complete: function() {
                // UserInterface.prototype.hideLoading();
                titleEllipsis && titleEllipsis.length && titleEllipsis.removeClass('btn-loading-title-local');
                imageWrapper && imageWrapper.length && imageWrapper.removeClass('image-loading-dim');
                btn.removeClass('btn-loading-local');
                btn.removeClass('btn-view-details-disabled');
                btn.removeClass('btn-loading-local');
            }
        });

        if (data != null) {
            $('.modal-title', modal).text(data.name);

            let modalBody = $('.modal-body', modal);

            // Bind image
            let carouselGallery = new CarouselGallery($('.image', modalBody), data.file);
            carouselGallery.init();

            // Bind facilities
            let html = '';
            if (data.amenity.length > 0) {
                html += '<div class="list-amenity row">';
                for (let i = 0; i < data.amenity.length; i++) {
                    html +=
                        `<div class="item col-md-6 col-lg-12">
                            <div class="item-wrapper media">
                                <div class="mr-2">
                                    <div class="image-wrapper position-relative">
                                        <img src="${data.amenity[i].amenity_icon}" alt="${data.amenity[i].amenity_name}" class="position-absolute w-100 h-100" />
                                    </div>
                                </div>
                                <div class="media-body">
                                    <p class="title mb-0">${data.amenity[i].amenity_name}</p>
                                </div>
                            </div>
                        </div>`;
                }
                html += '</div>';
            }
            $('.main-info-title', modalBody).after(html);

            modal.modal('show');
        }
    });
});