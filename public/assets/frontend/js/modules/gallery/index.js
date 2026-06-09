$(document).ready(function(){
    $('#gallery .gallery-video-grid').find('[id^="vr-player-"]').each(function(){
        const id = $(this).attr('id');
        if (!id || typeof videojs === 'undefined') {
            return;
        }

        const vrPlayer = videojs(id, {
            controls: true
        });

        if (typeof vrPlayer.vr === 'function') {
            vrPlayer.vr({
                projection: '360'
            });
        }
    });
});
