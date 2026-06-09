class Validate {
    isInt(value) {
        let x = parseFloat(value);
        return !isNaN(value) && (x | 0) === x && value.toString().indexOf('e') == -1;
    }

    isNumber(value) {
        let x = parseFloat(value);
        return !isNaN(x) && value.toString().indexOf('e') == -1;
    }

    isDate(value) {
        value = value.split('/');
        let date = new Date(value[2] + '/' + value[1] + '/' + value[0]);
        return !!(date && (date.getMonth() + 1) == value[1] && date.getDate() == Number(value[0]));
    }

    isTime(value) {
        let reg = /^([01]?[0-9]|2[0-3])(:[0-5][0-9])$/;
        return reg.test(value);
    }

    isEmail(value) {
        let reg = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return reg.test(String(value).toLowerCase());
    }

    isNullOrWhiteSpace(value) {
        if (value == undefined)
            return true;

        if (value == null)
            return true;

        return value.toString().replace(/\s/g, '').length == 0;
    }
}

class Utilities {
    formatDisplayRate(value) {
        return value + '%';
    }

    formatDisplayCurrency(amount, decimalCount = 0, decimal = ',', thousands = '.') {
        try {
            decimalCount = Math.abs(decimalCount);
            decimalCount = isNaN(decimalCount) ? 0 : decimalCount;

            const negativeSign = amount < 0 ? '-' : '';

            let i = parseInt(amount = Math.abs(Number(amount) || 0).toFixed(decimalCount)).toString();
            let j = (i.length > 3) ? i.length % 3 : 0;

            return negativeSign + (j ? i.substr(0, j) + thousands : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + thousands) + (decimalCount ? decimal + Math.abs(amount - i).toFixed(decimalCount).slice(2) : '') + ' ₫';
        } catch (e) {
            console.error(e)
        }
    }

    formatDisplayDateTime(value, format = 'DD/MM/YYYY HH:mm') {
        return moment(value).format(format)
    }

    formatDisplayDateOnly(value) {
        return moment(value).format('DD/MM/YYYY')
    }

    formatDisplayTimeOnly(value) {
        return moment(value).format('HH:mm')
    }

    guid() {
        function s4() {
            return Math.floor((1 + Math.random()) * 0x10000)
                .toString(16)
                .substring(1);
        }
        return s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
    }

    getQueryString(key) {
        let url = window.location.search.substring(1);
        let list = url.split('&');
        for (let i = 0; i < list.length; i++) {
            let qs = list[i].split('=');
            if (qs[0] == key) {
                return decodeURIComponent(qs[1]);
            }
        }
        return null;
    }

    setQueryString(uri, key, value) {
        let reg = new RegExp('([?&])' + key + '=.*?(&|$)', 'i');
        let separator = uri.indexOf('?') !== -1 ? '&' : '?';
        if (uri.match(reg)) {
            return uri.replace(reg, '$1' + key + '=' + value + '$2');
        } else {
            return uri + separator + key + '=' + value;
        }
    }

    escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }
}

class UserInterface {
    showFlashMessage(message, type) {
        toastr.options = {
            'closeButton': true,
            'preventDuplicates': false,
            'positionClass': 'toast-top-right',
            'showDuration': '400',
            'hideDuration': '1000',
            'timeOut': '3500',
            'extendedTimeOut': '1000',
            'showEasing': 'swing',
            'hideEasing': 'linear',
            'showMethod': 'fadeIn',
            'hideMethod': 'fadeOut'
        };

        if (type == 1) {
            toastr.success(message, 'Thông báo');
        } else {
            toastr.error(message, 'Thông báo');
        }
    }

    showFlashMessageInfo(message) {
        this.showFlashMessage(message, 1);
    }

    showFlashMessageError(message) {
        this.showFlashMessage(message, 0);
    }

    showLoading() {
        $('body').addClass('show-loading');
    }

    hideLoading() {
        $('body').removeClass('show-loading');
    }

    isInView(element) {
        if (element.length == 0) {
            return;
        }

        let win = $(window);
        let winHeight = win.height();
        let winTopPosition = win.scrollTop();
        let winBottomPosition = (winTopPosition + winHeight);

        let elementHeight = element.outerHeight();
        let elementTopPosition = element.offset().top;
        let elementBottomPosition = (elementTopPosition + elementHeight);

        return elementBottomPosition >= winTopPosition && elementTopPosition <= winBottomPosition;
    }
}

Dropzone.autoDiscover = false;
Dropzone.prototype.defaultOptions.dictDefaultMessage = 'Kéo file hoặc Bấm vào đây để thực hiện Tải lên.';
Dropzone.prototype.defaultOptions.dictFallbackMessage = 'Trình duyệt của bạn không hỗ trợ Tải lên.';
Dropzone.prototype.defaultOptions.dictFallbackText = null;
Dropzone.prototype.defaultOptions.dictFileTooBig = 'File tải lên ({{filesize}} MB) vượt quá dung lượng tối đa ({{maxFilesize}} MB).';
Dropzone.prototype.defaultOptions.dictInvalidFileType = 'Định dạng file tải lên không được hõ trợ.';
Dropzone.prototype.defaultOptions.dictResponseError = 'Có lỗi xảy ra ({{statusCode}}).';
Dropzone.prototype.defaultOptions.dictCancelUpload = 'Dừng tải lên';
Dropzone.prototype.defaultOptions.dictCancelUploadConfirmation = 'Bạn có chắc dừng việc tải lên?';
Dropzone.prototype.defaultOptions.dictRemoveFile = 'Loại bỏ';
Dropzone.prototype.defaultOptions.dictRemoveFileConfirmation = 'Bạn có chắc muốn loại bỏ file khỏi danh sách?';
Dropzone.prototype.defaultOptions.dictMaxFilesExceeded = 'Vượt quá giới hạn số lượng file cho phép trong 1 lần tải lên.';

function initializeDateTimePicker(container){
    if(!container) return;
    let id = Utilities.prototype.guid();

    container.attr('data-target', '#' + id);
    container.removeClass('date-time-picker');
    container.addClass('datetimepicker-input');
    container.wrap('<div id="' + id + '" class="date-time-picker" data-target-input="nearest"></div>');

    let option = {
        format: 'DD/MM/YYYY HH:mm',
        locale: 'vi',
        icons: {
            time: 'far fa-clock',
            date: 'far fa-calendar-alt',
            up: 'fas fa-chevron-up',
            down: 'fas fa-chevron-down',
            previous: 'fas fa-chevron-left',
            next: 'fas fa-chevron-right',
            today: 'fas fa-bullseye',
            clear: 'far fa-trash-alt',
            close: 'fas fa-times'
        },
        allowInputToggle: true
    };

    let value = container.val();
    if (value != '') {
        value = moment(value, 'DD/MM/YYYY HH:mm');
        value = moment(value).format('YYYY-MM-DD HH:mm');
        container.val('');
        option.defaultDate = value;
    }

    container.parent().datetimepicker(option);
}

function initializeDatePicker(container){
    if(!container) return;
    let id = Utilities.prototype.guid();

    container.attr('data-target', '#' + id);
    container.removeClass('date-picker');
    container.addClass('datetimepicker-input');
    container.wrap('<div id="' + id + '" class="date-picker" data-target-input="nearest"></div>');

    let option = {
        format: 'DD/MM/YYYY',
        locale: 'vi',
        icons: {
            time: 'far fa-clock',
            date: 'far fa-calendar-alt',
            up: 'fas fa-chevron-up',
            down: 'fas fa-chevron-down',
            previous: 'fas fa-chevron-left',
            next: 'fas fa-chevron-right',
            today: 'fas fa-bullseye',
            clear: 'far fa-trash-alt',
            close: 'fas fa-times'
        },
        allowInputToggle: true
    };

    let value = container.val();
    if (value != '') {
        value = moment(value, 'DD/MM/YYYY');
        value = moment(value).format('YYYY-MM-DD');
        container.val('');
        option.defaultDate = value;
    }

    container.parent().datetimepicker(option);
}

// function initializeYearPicker(container) {
//     if (!container || !container.length) return;
//     let id = Utilities.prototype.guid();
//
//     container.attr('data-target', '#' + id);
//     container.attr('data-toggle', 'datetimepicker');
//     container.removeClass('year-picker');
//     container.addClass('datetimepicker-input');
//     container.wrap('<div id="' + id + '" class="year-picker" data-target-input="nearest"></div>');
//
//     let option = {
//         format: 'YYYY',
//         locale: 'vi',
//         viewMode: 'years',
//         icons: {
//             time: 'far fa-clock',
//             date: 'far fa-calendar-alt',
//             up: 'fas fa-chevron-up',
//             down: 'fas fa-chevron-down',
//             previous: 'fas fa-chevron-left',
//             next: 'fas fa-chevron-right',
//             today: 'fas fa-bullseye',
//             clear: 'far fa-trash-alt',
//             close: 'fas fa-times'
//         },
//         allowInputToggle: true,
//         maxDate: moment(),
//         minDate: moment('1900', 'YYYY'),
//     };
//
//     let value = container.val();
//     if (value !== '') {
//         let parsed = moment(value, 'YYYY', true);
//         if (parsed.isValid()) {
//             option.defaultDate = parsed;
//         }
//         container.val('');
//     }
//
//     container.parent().datetimepicker(option);
//
//     container.parent().on('change.datetimepicker', function(e) {
//         if (e.date) {
//             container.val(e.date.format('YYYY'));
//         }
//     });
// }

function initializeClockPicker(container){
    if(!container) return;
    container.clockpicker({
        autoclose: true,
        doneText: 'Chọn',
    });
}

function initializeSelect2(container){
    if(!container) return;
    container.select2({
        width: '100%',
        // noResults: function(){
        //     return "Trống"
        // }
    });
}
$(document).ready(function () {
    $.ajaxPrefilter(function (options, originalOptions, jqXhr) {
        if (options.type.toUpperCase() === 'POST' || options.type.toUpperCase() === 'PUT' || options.type.toUpperCase() === 'PATCH' || options.type.toUpperCase() === 'DELETE') {
            let token = $('input[name^=_token]').first();
            if (!token.length) return;

            let tokenName = token.attr('name');

            if (options.contentType === false) {
                return;
            }

            if (options.contentType.indexOf('application/json') === 0) {
                options.url += ((options.url.indexOf('?') === -1) ? '?' : '&') + token.serialize();
            } else if (typeof options.data === 'string' && options.data.indexOf(tokenName) === -1) {
                options.data += (options.data ? '&' : '') + token.serialize();
            }
        }
    });

    $('.select2 select').each(function(){
       initializeSelect2($(this));
    });

    $('.date-picker').each(function () {
        initializeDatePicker($(this));
    });

    $('.date-time-picker').each(function () {
       initializeDateTimePicker($(this));
    });

    // $('.year-picker').each(function () {
    //     initializeYearPicker($(this));
    // });

    $('.color-picker').colorpicker({
        format: 'hex'
    });

    $('.image-select').imageSelect();

    function capCabinAreaInput($input) {
        var val = $input.val();
        if (val === '' || val == null) { return; }
        var num = parseFloat(String(val).replace(',', '.')) || 0;
        if (num > 10000) {
            $input.val(10000);
        }
    }
    $(document).on('input change', '#cabin-form input[name="area"]', function () {
        capCabinAreaInput($(this));
    });
    $(document).on('paste', '#cabin-form input[name="area"]', function () {
        var $input = $(this);
        setTimeout(function () { capCabinAreaInput($input); }, 0);
    });

    function capCabinDiscountInput($input) {
        var val = $input.val();
        if (val === '' || val == null) { return; }
        var num = parseFloat(String(val).replace(',', '.'));
        if (isNaN(num)) { return; }
        if (num > 100) {
            $input.val(100);
        } else if (num < 0) {
            $input.val(0);
        }
    }
    $(document).on('input change', '#cabin-form input[name="discount_percent"]', function () {
        capCabinDiscountInput($(this));
    });
    $(document).on('paste', '#cabin-form input[name="discount_percent"]', function () {
        var $input = $(this);
        setTimeout(function () { capCabinDiscountInput($input); }, 0);
    });

    $('.table-data').checkAll();

    $('#btn-update-theme').click(async function (e) {
        e.preventDefault();

        $(this).prop('disabled', true);

        let url = $(this).attr('data-ajax-url');
        let theme = $('body').hasClass('c-dark-theme') ? 0 : 1;

        await $.ajax({
            type: 'POST',
            url: url,
            data: {
                theme: theme
            },
            traditional: true
        });

        if (theme == 1) {
            $('body').addClass('c-dark-theme');
        } else {
            $('body').removeClass('c-dark-theme');
        }

        $(this).prop('disabled', false);
    });

    $('body').on('click', '.btn-delete-one', function (e) {
        e.preventDefault();

        let id = parseInt($(this).attr('data-id'));
        let url = $(this).attr('data-ajax-url');
        let urlGoback = $(this).attr('data-ajax-url-go-back');

        let doDeleteOne = function () {
            $('#loading').show();
            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    id: id
                },
                traditional: true,
                complete: function () {
                    $('#loading').hide();
                    if (!Validate.prototype.isNullOrWhiteSpace(urlGoback)) {
                        window.location.href = urlGoback;
                    } else {
                        window.location.href = window.location.href;
                    }
                }
            });
        };

        if (typeof swalAlert !== 'undefined') {
            swalAlert.confirm('Bạn có chắc muốn xóa?').then(function (result) {
                if (result.isConfirmed) {
                    doDeleteOne();
                }
            });
        } else if (confirm('Bạn có chắc muốn xóa?')) {
            doDeleteOne();
        }
    });

    $('.btn-delete-multi').click(function () {
        let id = [];

        let idx = $('.btn-delete-multi').index(this);
        let tbl = $('.table-data:eq(' + idx + ')');

        let url = $(this).attr('data-ajax-url');
        let urlGoback = $(this).attr('data-ajax-url-go-back');

        $('.chk-item:checked', tbl).each(function () {
            id.push(parseInt($(this).val()));
        });

        if (id.length === 0) {
            if (typeof swalAlert !== 'undefined') {
                swalAlert.warning('Bạn phải chọn dữ liệu cần xóa!');
            } else {
                alert('Bạn phải chọn dữ liệu cần xóa!');
            }
            return;
        }

        let doDeleteMulti = function () {
            $('#loading').show();
            $.ajax({
                type: 'POST',
                url: url,
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    id: id
                }),
                traditional: true,
                complete: function () {
                    $('#loading').hide();
                    if (!Validate.prototype.isNullOrWhiteSpace(urlGoback)) {
                        window.location.href = urlGoback;
                    } else {
                        window.location.href = window.location.href;
                    }
                }
            });
        };

        if (typeof swalAlert !== 'undefined') {
            swalAlert.confirm('Bạn có chắc muốn xóa?').then(function (result) {
                if (result.isConfirmed) {
                    doDeleteMulti();
                }
            });
        } else if (confirm('Bạn có chắc muốn xóa?')) {
            doDeleteMulti();
        }
    });

    $('body').on('click', '.btn-action', async function (e) {
        e.preventDefault();

        let id = parseInt($(this).attr('data-id'));
        let actionName = $(this).attr('data-action-name');
        let url = $(this).attr('data-ajax-url');
        let urlGoback = $(this).attr('data-ajax-url-go-back');

        let doAction = function () {
            $('#loading').show();
            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    id: id
                },
                traditional: true,
                complete: function () {
                    $('#loading').hide();
                    if (!Validate.prototype.isNullOrWhiteSpace(urlGoback)) {
                        window.location.href = urlGoback;
                    } else {
                        window.location.href = window.location.href;
                    }
                }
            });
        };

        let confirmText = 'Bạn có chắc muốn ' + actionName + '?';
        if (typeof swalAlert !== 'undefined') {
            swalAlert.confirm(confirmText).then(function (result) {
                if (result.isConfirmed) {
                    doAction();
                }
            });
        } else if (confirm(confirmText)) {
            doAction();
        }
    });

    $('body').on('blur', 'input.require-int-unsigned', function () {
        let value = $(this).val();

        if ($(this).hasClass('nullable') && Validate.prototype.isNullOrWhiteSpace(value)) {
            $(this).val('');
            return;
        }

        let minValue = $(this).hasClass('gt-zero') ? 1 : 0;
        if (!Validate.prototype.isInt(value) || (Validate.prototype.isInt(value) && value < minValue)) {
            value = minValue;
        }
        value = value.toString().replace(/(\+|-)/g, '');
        $(this).val(value);
    });

    $('body').on('blur', 'input.require-number-unsigned', function () {
        let value = $(this).val();

        if ($(this).hasClass('nullable') && Validate.prototype.isNullOrWhiteSpace(value)) {
            $(this).val('');
            return;
        }

        let minValue = $(this).hasClass('gt-zero') ? 1 : 0;
        if (!Validate.prototype.isNumber(value) || (Validate.prototype.isNumber(value) && value < minValue)) {
            value = minValue;
        }
        value = value.toString().replace(/(\+|-)/g, '');
        $(this).val(value);
    });

    $('.btn-tooltip').tooltip();

    $('.input-clock-picker').each(function(){
        initializeClockPicker($(this));
    })

    // $( ".year-picker" ).datepicker({
    //     changeYear: true,
    //     showButtonPanel: true,
    //     dateFormat: 'yy',
    //     onClose: function(dateText, inst) {
    //         $(this).datepicker('setDate', new Date(inst.selectedYear, 1, 1));
    //     }
    // });
});
