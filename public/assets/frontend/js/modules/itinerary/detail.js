$(document).ready(function(){
    $('.service-important-note-container').owlCarousel({
        loop: false,
        margin: 24,
        nav: true,

        responsive: {
            0: {
                items: 1,
                dots: true
            },
            768: {
                items: 2,
            },
            1200: {
                items: 3,
                nav: false
            }
        }
    })

    const AirDatePickerLocale = {
        days: ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
        daysShort: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
        daysMin: ['Su','Mo','Tu','We','Th','Fr','Sa'],
        months: ['January','February','March','April','May','June',
                 'July','August','September','October','November','December'],
        monthsShort: ['Jan','Feb','Mar','Apr','May','Jun',
                      'Jul','Aug','Sep','Oct','Nov','Dec'],
        today: 'Today',
        clear: 'Clear',
        dateFormat: 'MM/dd/yyyy',
        timeFormat: 'hh:mm aa',
        firstDay: 0
    };

    const AirDatePickerLocaleVi = {
        days: ['Chủ Nhật','Thứ Hai','Thứ Ba','Thứ Tư','Thứ Năm','Thứ Sáu','Thứ Bảy'],
        daysShort: ['CN','T2','T3','T4','T5','T6','T7'],
        daysMin: ['CN','T2','T3','T4','T5','T6','T7'],
        months: ['Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6',
            'Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'],
        monthsShort: ['Th1','Th2','Th3','Th4','Th5','Th6',
            'Th7','Th8','Th9','Th10','Th11','Th12'],
        today: 'Hôm nay',
        clear: 'Xóa',
        dateFormat: 'dd/MM/yyyy',
        timeFormat: 'HH:mm',
        firstDay: 1 // ✅ Monday first (Vietnamese standard)
    };

    function initAirDatePicker() {
        let activePicker = null;
        $('.btn-check-availability').each(function() {
            const $btn = $(this);
            const $input = $('<input type="text" />').css({
                position: 'absolute',
                visibility: 'hidden',
                left: '50%',
                transform: 'translateX(-50%)',
                width: 0,
                height: 0
            });
            $btn.after($input);

            const $container = $input.parent();
            $container.addClass('position-relative');
            const parentPaddingBottom = parseInt($container.css('padding-bottom')) || 0;
            $input.css('top',`calc(100% - ${parentPaddingBottom}px)`)

            const code = window.languageCode;
            const isVi = code === 'vi' || code === 'vn';
            const picker = new AirDatepicker($input[0], {
                locale: isVi ? AirDatePickerLocaleVi : AirDatePickerLocale,
                container: document.body,
                position: 'bottom center',
                onBeforeSelect: function(){return false},
                onShow() {
                    activePicker = picker;

                    const $dp = $(picker.$datepicker);
                    if ($dp.find('.adp-legend').length === 0) {
                        $dp.append(`
                            <div class="adp-legend">
                                <span class="adp-legend__dot"></span>
                                <span class="adp-legend__text">${commonLabel.departureDate}</span>
                            </div>
                        `);
                    }

                    $dp.off('click.highlighted').on('click.highlighted', '.highlighted', function () {
                        const dateStr = $(this).data('date');
                        const route = window.departureRoutes && window.departureRoutes[dateStr];
                        if (route) {
                            window.location.href = route;
                        }
                    });
                },
                onHide() {
                    activePicker = null;  // ✅ clear active when closed
                },
                onRenderCell({ date, cellType }) {
                    if (cellType === 'day') {
                        const year  = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day   = String(date.getDate()).padStart(2, '0');
                        const dateStr = `${year}-${month}-${day}`;
                        if (window.departureDate && window.departureDate.includes(dateStr)) {
                            return {
                                classes: 'highlighted',
                                attrs: {
                                    'data-date': dateStr
                                }
                            };
                        }
                    }
                }
            });

            $btn.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                picker.show();
            });

            let isInsidePicker = false;
            $(document).off('click.airpicker').off('mousedown.airpicker');
            $(document).on('mousedown.airpicker', function(e) {
                if (!activePicker) return;
                const dp = activePicker.$datepicker;
                isInsidePicker = dp.contains(e.target);
            });
            $(document).on('click.airpicker', function(e) {
                if (!activePicker) return;
                if (isInsidePicker) {
                    isInsidePicker = false;
                    return;
                }
                activePicker.hide();
            });
        });
    }

    initAirDatePicker();

    // Start Section Detail
    const $sectionDetail = $('.section-detail');
    function initSectionDetail(){
        const $listItem = $sectionDetail.find('.item-detail');
        $listItem.each(function(index){
            const $item = $(this);
            $item.toggleClass('expand', index === 0);
            $item.on('click', '.header', function(){
                $(this).closest('.item-detail').toggleClass('expand');
            });
        });
    }
    initSectionDetail();
    // End Section Detail
})
