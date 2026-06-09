$(document).ready(function(){
    $('.section-itinerary .list-filter .item').on('click',function(){
        const selected = $(this).data('duration');
        if($(this).hasClass('active')) return;

        $(this).addClass('active');
        $(this).siblings().removeClass('active');

        const $itineraryDetail = $('.section-itinerary').find('.itinerary-detail');
        $itineraryDetail.each(function(){
            const duration = $(this).data('duration');
            if(duration === selected){
                $(this).removeClass('d-none');
            }
            else{
                $(this).addClass('d-none');
            }
        });
    })

    const toggleGalleryLoading = ($container, isLoading) => {
        if (!$container || !$container.length) return;

        $container.each(function(){
            const $current = $(this);
            if (isLoading) {
                $current.addClass('is-loading');
                if (!$current.find('.gallery-loading').length) {
                    $current.append('<div class="gallery-loading"><div class="spinner"></div></div>');
                }
            } else {
                $current.removeClass('is-loading');
                $current.find('.gallery-loading').remove();
            }
        });
    };

    const fetchData = async (id,url,$container) => {
        let data = null;

        toggleGalleryLoading($container,true);
        await $.ajax({
            url: url,
            type: 'GET',
            data: {
                id: id
            },
            success: function(response) {
                if (response.msg === 'success') {
                    data = response.data;
                } else {
                    console.error(response.err);
                }
            },
            complete: function() {
                toggleGalleryLoading($container,false);
            }
        })

        return data;
    }

    async function bindDataCarouselHtml($carouselContainer,id,url){
        if(!$carouselContainer) return;
        const data = await fetchData(id,url,$carouselContainer);
        if(data){
            const carousel = new CarouselGallery($carouselContainer,data.file);
            carousel.init();

            $carouselContainer.find('.owl-carousel .owl-item .item').each(function(){
                $(this).append(`
                    <div class="description give-ellipsis after-2-lines mt-2 text-center">${data?.description ?? data?.content ?? ""}</div>
                `)
            })

            initEllipsisTooltip($carouselContainer)
        }
    }

    async function onItemClick(container,item,url){
        if(item.hasClass('active-1 active-2')) return;

        $('.section-cabin-and-service .accordion-item .item-grid .item').removeClass('active-2');
        item.siblings().removeClass('active-1');
        item.addClass('active-1 active-2');

        const id = item.data('id');
        await bindDataCarouselHtml(container,id,url);
    }

    $('.section-cabin-and-service .accordion-item .header .icon-wrapper').on('click', function () {
        const breakpoint = 992;
        const $item = $(this).closest('.accordion-item');

        if (window.innerWidth > breakpoint) {
            if (!$item.hasClass('item-collapse')) return;
        }

        $item.removeClass('item-collapse');
        $item.siblings().addClass('item-collapse');

        if (window.innerWidth < breakpoint) {
            if (!$item.hasClass('item-collapse') && $item.offset().top < $(window).scrollTop()) {
                $(window).scrollTop($item.offset().top);
            }
        }
    });
    const $cabinContainer = $('.section-cabin-and-service .block-1 .cabin-container');
    const $serviceContainer = $('.section-cabin-and-service .block-1 .service-container');
    const $cabinCarouselContainer = $('.section-cabin-and-service .cabin-gallery-container');
    const $serviceCarouselContainer =  $('.section-cabin-and-service .service-gallery-container')
    const $serviceCarouselBlock1Container =  $('.section-cabin-and-service .block-1 .service-gallery-container');

    if($serviceContainer){
        $serviceContainer.on('click','.item-grid .item',async function(){
           await onItemClick($serviceCarouselContainer,$(this),apiAppService.getById);
        })

        const $defaultAppService = $serviceContainer.find('.item').first();
        if($defaultAppService.length){
            (async () => {
                await bindDataCarouselHtml($serviceCarouselBlock1Container,$defaultAppService.data('id'),apiAppService.getById);
            })();
        }
    }

    if($cabinContainer){
        $cabinContainer.on('click','.item-grid .item',async function(){
             await onItemClick($cabinCarouselContainer,$(this),apiAppCabin.getById);
        })

        const $defaultCabin = $cabinContainer.find('.item').first();
        if($defaultCabin.length){
            (async () => {
                await bindDataCarouselHtml($cabinCarouselContainer,$defaultCabin.data('id'),apiAppCabin.getById);
            })();
        }
    }
})
