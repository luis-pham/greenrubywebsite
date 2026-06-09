<script type="text/javascript">
    window.AppJs = window.AppJs || {};

    (function (ns) {
        ns.formatPriceDisplay = function (value) {
            if (!value) return '';
            var v = value.toString().replace(/[^\d,\.]/g, '');
            v = v.replace(/\./g, '');
            var parts = v.split(',');
            var intPart = parts[0] || '';
            var decPart = parts[1] || '';
            intPart = intPart.replace(/^0+(?=\d)/, '');
            intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            if (decPart.length > 2) {
                decPart = decPart.substring(0, 2);
            }
            return decPart ? intPart + ',' + decPart : intPart;
        };

        ns.normalizePriceForSubmit = function (value) {
            if (!value) return '';
            var v = value.toString().replace(/\./g, '').replace(',', '.');
            return v;
        };

        var MAX_PRICE = 10000000000; // 10 tỉ - giới hạn giá trị ô giá
        function capPriceVal(val) {
            var digits = String(val).replace(/\D/g, '');
            if (digits.length > 11 || (digits.length === 11 && digits > '10000000000')) {
                return ns.formatPriceDisplay ? ns.formatPriceDisplay('10000000000') : '10000000000';
            }
            return val;
        }
        ns.bindPriceInputs = function (containerSelector, formSelector) {
            var $root = containerSelector ? $(containerSelector) : $(document);

            $root.on('input', '.price-input', function () {
                var $el = $(this);
                var v = $el.val().replace(/[^0-9\.,]/g, '');
                v = capPriceVal(v);
                $el.val(v);
            });

            $root.on('blur', '.price-input', function () {
                var v = $(this).val();
                $(this).val(ns.formatPriceDisplay(v));
            });

            if (formSelector) {
                $(formSelector).on('submit', function () {
                    $(this).find('.price-input').each(function () {
                        var v = $(this).val();
                        $(this).val(ns.normalizePriceForSubmit(v));
                    });
                });
            }
        };
    })(window.AppJs);
</script>

