(function () {
    'use strict';

    if (typeof Swal === 'undefined') {
        console.warn('SweetAlert2 chưa được load. Vui lòng thêm script trước sweetalert.js');
        return;
    }

    var swalAlert = {
        alert: function (options) {
            if (typeof options === 'string') {
                options = { title: options, text: '' };
            }
            return Swal.fire(options);
        },
        success: function (title, text) {
            var opts = { icon: 'success' };
            if (typeof title === 'string' && (text === undefined || text === null)) {
                var full = title.trim();
                var dot = full.indexOf('. ');
                if (dot > 0 && dot < full.length - 1) {
                    opts.title = full.slice(0, dot + 1).trim();
                    opts.html = full.slice(dot + 1).trim();
                } else {
                    opts.title = full || 'Thành công';
                    opts.html = '';
                }
            } else {
                opts.title = title || 'Thành công';
                opts.html = text || '';
            }
            return Swal.fire(opts);
        },
        error: function (title, text) {
            var opts = { icon: 'error' };
            if (typeof title === 'string' && (text === undefined || text === null)) {
                var full = title.trim();
                var dot = full.indexOf('. ');
                if (dot > 0 && dot < full.length - 1) {
                    opts.title = full.slice(0, dot + 1).trim();
                    opts.html = full.slice(dot + 1).trim();
                } else {
                    opts.title = full || 'Lỗi';
                    opts.html = '';
                }
            } else {
                opts.title = title || 'Lỗi';
                opts.html = text || '';
            }
            return Swal.fire(opts);
        },
        warning: function (title, text) {
            var opts = { icon: 'warning' };
            if (typeof title === 'string' && (text === undefined || text === null)) {
                var full = title.trim();
                var dot = full.indexOf('. ');
                if (dot > 0 && dot < full.length - 1) {
                    opts.title = full.slice(0, dot + 1).trim();
                    opts.html = full.slice(dot + 1).trim();
                } else {
                    opts.title = full || 'Cảnh báo';
                    opts.html = '';
                }
            } else {
                opts.title = title || 'Cảnh báo';
                opts.html = text || '';
            }
            return Swal.fire(opts);
        },
        info: function (title, text) {
            return Swal.fire({
                icon: 'info',
                title: title || 'Thông tin',
                text: text || ''
            });
        },
        confirm: function (options) {
            if (typeof options === 'string') {
                options = {
                    title: options,
                    showCancelButton: true,
                    confirmButtonText: 'Xác nhận',
                    cancelButtonText: 'Hủy'
                };
            }
            return Swal.fire(options);
        },
        toast: function (options) {
            if (typeof options === 'string') {
                options = { title: options, toast: true, position: 'top-end', timer: 3000 };
            } else {
                options.toast = options.toast !== false;
                options.position = options.position || 'top-end';
                options.timer = options.timer !== undefined ? options.timer : 3000;
            }
            return Swal.fire(options);
        }
    };

    window.swalAlert = swalAlert;
})();
