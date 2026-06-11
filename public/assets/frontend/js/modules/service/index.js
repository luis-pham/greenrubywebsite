$(document).ready(function () {
    function getServiceFilterGroups($btn) {
        var raw = ($btn.attr('data-groups') || '').trim();
        var groups = [];

        if (raw) {
            try {
                groups = JSON.parse(raw);
            } catch (e) {
                groups = [raw];
            }
        }

        return groups;
    }

    $(document).on('click', '#service .gallery-filter-tab', function () {
        var $btn = $(this);
        var groups = getServiceFilterGroups($btn);

        $('#service .gallery-filter-tab').removeClass('active');
        $btn.addClass('active');

        if (!groups || groups.length === 0) {
            $('#service .service-group').removeClass('is-hidden');
        } else {
            $('#service .service-group').each(function () {
                var groupId = String($(this).data('group'));
                var match = groups.map(String).indexOf(groupId) !== -1;
                $(this).toggleClass('is-hidden', !match);
            });
        }
    });
});
