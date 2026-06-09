$(document).ready(function () {
    let listFile = $('.list-file');

    $('.item', listFile).click(function (e) {
        e.preventDefault();

        let listFile = $(this).closest('.list-file');
        let chkItem = $('.custom-control input:checked', listFile);
        if (chkItem.length > 0) {
            return;
        }

        let callback = Utilities.prototype.getQueryString('callback');
        let obj = JSON.parse($(this).attr('data-obj'));

        if (window.parent && window.parent[callback]) window.parent[callback](obj);
    });

    $('.btn-select-multi').click(function (e) {
        e.preventDefault();

        let chkItem = $('.chk-item:checked', listFile);
        if (chkItem.length == 0) {
            alert('Bạn phải chọn file!');
            return;
        }

        let callback = Utilities.prototype.getQueryString('callback');
        let listObj = [];
        for (let i = 0; i < chkItem.length; i++) {
            let itemWrapper = $(chkItem[i]).closest('.item-wrapper');
            let item = $('.item', itemWrapper);
            let obj = JSON.parse(item.attr('data-obj'));
            listObj.push(obj);
            chkItem.prop('checked', false);
        }

        if (window.parent && window.parent[callback]) window.parent[callback](listObj);
    });
});