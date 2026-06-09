/*	=====================================
 *	jQuery Plugin Check All
 *	Version: 1.0.2
 *
 *	@author: Dinh Viet Bao
 *  @email: vietbao273@gmail.com
 *  @created at: 16/08/2016
 *  @updated at: 06/08/2020
 	=====================================*/

(function () {
    $.fn.extend({
        checkAll: function (options) {
            var defaults = {
                checkAll: '.chk-all',
                checkItem: '.chk-item'
            };

            var options = $.extend(defaults, options);

            return this.each(function () {
                var table = $(this);

                $(options.checkAll, table).on('change', function () {
                    var check = this.checked;
                    $(options.checkItem + ':not([disabled])', table).prop('checked', check);
                });

                $(table).on('change', options.checkItem, function () {
                    var checkedItem = $(options.checkItem + ':not([disabled]):checked', table).length;
                    var totalItem = $(options.checkItem + ':not([disabled])', table).length;
                    var check = checkedItem == totalItem;
                    $(options.checkAll, table).prop('checked', check);
                });

                var totalItem = $(options.checkItem + ':not([disabled])', table).length;
                if (totalItem > 0) {
                    var checkedItem = $(options.checkItem + ':not([disabled]):checked', table).length;
                    var check = checkedItem == totalItem;
                    $(options.checkAll, table).prop('checked', check);
                } else {
                    $(options.checkAll, table).prop('checked', false);
                }
            });
        }
    });
})(jQuery);