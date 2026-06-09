$(document).ready(function () {
    $('.btn-select').click(function (e) {
        e.preventDefault();

        let tr = $(this).closest('tr');
        let obj = JSON.parse(tr.attr('data-obj'));

        let callback = Utilities.prototype.getQueryString('callback');
        if (window.parent && window.parent[callback]) window.parent[callback](obj);
    });
});