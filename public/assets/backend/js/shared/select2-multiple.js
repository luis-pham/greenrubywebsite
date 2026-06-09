$(document).ready(function() {
    $('.select2-multiple').select2({
        placeholder: "Chọn...",
        allowClear: true,
        width: '100%',
        closeOnSelect: false,       // better UX for multiple
        templateSelection: function (data, container) {
            if (!data.element) return data.text;

            const $iconClass = $(data.element).data('icon');
            const $imageLink = $(data.element).data('image');
            const $icon = $iconClass ? $('<i>', { class: $iconClass + ' mr-2' }) : null;
            const $image = $imageLink ? $('<img>', { src: $imageLink, style: "width:25px;height:25px;object-fit:cover", class: 'mr-2'}) : $icon;

            const $text = $('<span>', {
                text: data.text
            });

            return $(container).append($image, $text);
        },

        templateResult: function (data) {
            if (!data.element) return data.text;
            const $iconClass = $(data.element).data('icon');
            const $imageLink = $(data.element).data('image');
            const $icon = $iconClass ? $('<i>', { class: $iconClass + ' mr-2' }) : null;
            const $image = $imageLink ? $('<img>', { src: $imageLink, style: "width:40px", class: 'mr-2'}) : $icon;

            if (!$image) return data.text;

            return $('<span>').append(
                $image,
                data.text
            );
        }
    });
});
