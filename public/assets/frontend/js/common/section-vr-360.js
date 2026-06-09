$(document).ready(function(){
    $('.vr-container-outer').find('[id^="vr-player-"]').each(function(){
        const id = $(this).attr('id');
        const vrPlayer = videojs(id,{
            controls: true
        });

        vrPlayer.vr({
            projection: '360'
        })
    })

   $('.vr-carousel').owlCarousel({
        items: 1,                // Show only 1 item at a time
        nav: true,               // Enable arrows
        margin: 24,
        dots: false,
        navText: [
            '<i class="fa fa-chevron-left"></i>',   // Left arrow
            '<i class="fa fa-chevron-right"></i>'   // Right arrow
        ],

        smartSpeed: 800,         // Smooth transition speed
        mouseDrag: false,
        touchDrag: false,
   });
})
