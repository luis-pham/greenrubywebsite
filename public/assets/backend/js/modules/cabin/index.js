(function () {
    var $cabinForm = $('#cabin-form');
    if (!$cabinForm.length) {
        return;
    }
    var config = window.CabinFormConfig || {};
    var langRoomName = config.langRoomName || 'Tên phòng';
    var langRoomDesc = config.langRoomDesc || 'Mô tả phòng...';
    var langAudienceName = config.langAudienceName || 'Tên đối tượng';
    var langDelete = config.langDelete || 'Xóa';
    var langGuestLabels = config.langGuestLabels || {};
    var oldRoomTitles = config.oldRoomTitles || [];
    var oldRoomDescriptions = config.oldRoomDescriptions || [];
    var oldAmenityIds = config.oldAmenityIds || [];
    var oldAmenityName = config.oldAmenityName || [];
    var oldAmenityDescription = config.oldAmenityDescription || [];
    var oldAmenityIcon = config.oldAmenityIcon || [];
    var oldAudienceName = config.oldAudienceName || [];
    var oldAudienceIcon = config.oldAudienceIcon || [];
    var oldAudienceIds = config.oldAudienceIds || [];
    var listAmenityMap = config.listAmenityMap || {};
    var listAudienceMap = config.listAudienceMap || {};
    var oldPrice = config.oldPrice || {};
    var priceData = config.priceData || [];
    var initialCapacity = config.initialCapacity;
    var isEdit = config.isEdit === true;
    var currentPriceData = {};
    var MAX_PRICE = 10000000000;

    if (priceData && priceData.length > 0) {
        for (var i = 0; i < priceData.length; i++) {
            var p = priceData[i];
            if (!currentPriceData[p.duration]) {
                currentPriceData[p.duration] = {};
            }
            currentPriceData[p.duration][p.guest] = p.price;
        }
    }

    function renderRoom(name, description) {
        var escapedName = name ? $('<div>').text(name).html() : '';
        var escapedDesc = description ? $('<div>').text(description).html() : '';
        var titleClass = name ? '' : ' placeholder';
        var descClass = description ? '' : ' placeholder';
        var html = '<div class="room-pill d-flex align-items-start justify-content-between">' +
            '<div class="flex-grow-1 mr-2">' +
                '<input type="hidden" name="room_title[]" value="' + escapedName + '">' +
                '<input type="hidden" name="room_description[]" value="' + escapedDesc + '">' +
                '<strong class="room-title-display' + titleClass + '" data-placeholder="' + $('<div>').text(langRoomName).html() + '" title="' + (name || '') + '">' + (escapedName || $('<div>').text(langRoomName).html()) + '</strong>' +
                '<span class="room-desc-display' + descClass + '" data-placeholder="' + $('<div>').text(langRoomDesc).html() + '" title="' + (description || '') + '">' + (escapedDesc || $('<div>').text(langRoomDesc).html()) + '</span>' +
            '</div>' +
            '<button type="button" class="btn btn-link text-danger p-0 btn-remove-room-pill"><i class="fas fa-times"></i></button>' +
            '</div>';
        $('#selected-rooms').append(html);
    }

    $cabinForm.on('click', '.btn-open-room-modal', function () {
        renderRoom('', '');
    });

    // Show cabin_class only when cabin type is accommodation (slug: phong-o / accommodation)
    function toggleCabinClassVisibility() {
        var $groupSelect = $cabinForm.find('select[name="group_id"]');
        var $selected = $groupSelect.find('option:selected');
        var slug = ($selected.data('slug') || '').toString().toLowerCase().replace(/\s+/g, '-');
        var isAccommodation = (slug === 'phong-o' || slug === 'phong_o' || slug === 'accommodation');
        if (isAccommodation) {
            $('#cabin-class-wrapper').show();
            $('#view-input-wrapper').removeClass('col-md-12').addClass('col-md-6');
        } else {
            $('#cabin-class-wrapper').hide();
            $('#view-input-wrapper').removeClass('col-md-6').addClass('col-md-12');
            $cabinForm.find('select[name="cabin_class"]').val('');
        }
    }
    $cabinForm.on('change', 'select[name="group_id"]', toggleCabinClassVisibility);
    toggleCabinClassVisibility();

    $cabinForm.on('click', '.btn-remove-room-pill', function (e) {
        e.stopPropagation();
        $(this).closest('.room-pill').remove();
    });

    function startRoomInlineEdit($display, isDesc) {
        var placeholder = $display.data('placeholder');
        var $pill = $display.closest('.room-pill');
        var $hidden = isDesc ? $pill.find('input[name="room_description[]"]') : $pill.find('input[name="room_title[]"]');
        var currentVal = $hidden.val() || '';
        var maxLen = isDesc ? 200 : 50;
        var $input = $('<input type="text" class="form-control form-control-sm ' + (isDesc ? 'room-desc-edit' : 'room-title-edit') + '" maxlength="' + maxLen + '">').val(currentVal);
        $display.after($input).hide();
        $input.focus();
        function commit() {
            var val = $input.val().trim();
            var displayText = val || placeholder;
            var escaped = $('<div>').text(val).html();
            $hidden.val(escaped);
            $display.attr('title', val);
            $display.text(displayText).toggleClass('placeholder', !val).show();
            $input.remove();
        }
        $input.on('blur', commit);
        $input.on('keydown', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                $input.blur();
            }
        });
    }

    $cabinForm.on('click', '#selected-rooms .room-title-display', function () {
        startRoomInlineEdit($(this), false);
    });
    $cabinForm.on('click', '#selected-rooms .room-desc-display', function () {
        startRoomInlineEdit($(this), true);
    });

    function renderExistingAmenity(id, name, icon) {
        if ($('#selected-amenities .amenity-pill[data-id="' + id + '"]').length) {
            return;
        }
        var escapedName = $('<div>').text(name).html();
        var escapedIcon = icon ? $('<div>').text(icon).html() : '';
        var iconHtml = escapedIcon
            ? '<img src="' + escapedIcon + '" alt="" class="cabin-pill-icon mr-2" />'
            : '';
        var html = '<div class="amenity-pill" data-id="' + id + '">' +
            '<input type="hidden" name="amenity_ids[]" value="' + id + '">' +
            '<button type="button" class="btn btn-link text-dark p-0 btn-remove-amenity-pill amenity-pill-remove" title="' + $('<div>').text(langDelete).html() + '"><i class="fas fa-times"></i></button>' +
            '<div class="d-flex align-items-center">' + iconHtml + '<strong class="text-uppercase small mb-0 amenity-name" title="' + (name || '') + '">' + escapedName + '</strong></div>' +
            '</div>';
        $('#selected-amenities').append(html);
    }

    function renderNewAmenity(name, description, icon) {
        var escapedName = $('<div>').text(name).html();
        var escapedDesc = description ? $('<div>').text(description).html() : '';
        var escapedIcon = icon ? $('<div>').text(icon).html() : '';
        var iconHtml = escapedIcon
            ? '<img src="' + escapedIcon + '" alt="" class="cabin-pill-icon mr-2" />'
            : '';
        var html = '<div class="amenity-pill" data-new="1">' +
            '<input type="hidden" name="amenity_name[]" value="' + escapedName + '">' +
            '<input type="hidden" name="amenity_description[]" value="' + escapedDesc + '">' +
            '<input type="hidden" name="amenity_icon[]" value="' + escapedIcon + '">' +
            '<button type="button" class="btn btn-link text-dark p-0 btn-remove-amenity-pill amenity-pill-remove" title="' + $('<div>').text(langDelete).html() + '"><i class="fas fa-times"></i></button>' +
            '<div class="d-flex align-items-center">' + iconHtml + '<strong class="text-uppercase small mb-0 amenity-name" title="' + (name || '') + '">' + escapedName + '</strong></div>' +
            '</div>';
        $('#selected-amenities').append(html);
    }

    function updateAmenitySelectionCount() {
        var n = $('#amenity-modal .amenity-item.selected').length;
        $('#amenity-modal .amenity-selection-count').text(n);
    }

    $cabinForm.on('click', '.btn-open-amenity-modal', function () {
        $('#amenity-search').val('');
        $('#amenity-modal .amenity-card-wrapper').show();
        $('#amenity-modal .amenity-item').removeClass('selected in-list');
        $('#amenity-modal .amenity-item').each(function () {
            var id = $(this).data('id');
            if (id && $('#selected-amenities .amenity-pill[data-id="' + id + '"]').length > 0) {
                $(this).addClass('selected in-list');
            }
        });
        updateAmenitySelectionCount();
        $('#amenity-modal').modal('show');
    });

    $(document).on('keyup', '#amenity-search', function () {
        var q = $(this).val().toLowerCase();
        $('#amenity-modal-list').find('.amenity-card-wrapper').each(function () {
            var name = $(this).find('.amenity-item').data('name');
            if (name && typeof name === 'string') {
                $(this).toggle(name.toLowerCase().indexOf(q) !== -1);
            }
        });
    });

    $(document).on('click', '#amenity-modal .amenity-item', function () {
        var $card = $(this);
        if ($card.hasClass('in-list')) {
            var id = $card.data('id');
            $('#selected-amenities .amenity-pill[data-id="' + id + '"]').closest('.amenity-pill').remove();
            $card.removeClass('selected in-list');
        } else {
            $card.toggleClass('selected');
        }
        updateAmenitySelectionCount();
    });

    $(document).on('click', '.btn-confirm-amenity-selection', function () {
        $('#amenity-modal .amenity-item.selected').each(function () {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var icon = $(this).data('icon');
            if (id && $('#selected-amenities .amenity-pill[data-id="' + id + '"]').length === 0) {
                renderExistingAmenity(id, name, icon);
            }
        });
        $('#amenity-modal .amenity-item').removeClass('selected in-list');
        updateAmenitySelectionCount();
    });

    $cabinForm.on('click', '.btn-remove-amenity-pill', function () {
        $(this).closest('.amenity-pill').remove();
    });

    function renderAudience(name, icon) {
        var escapedName = name ? $('<div>').text(name).html() : '';
        var escapedIcon = icon ? $('<div>').text(icon).html() : '';
        var nameClass = name ? '' : ' placeholder';
        var nameText = name || $('<div>').text(langAudienceName).html();
        var iconHtml = escapedIcon ?
            '<i class="' + escapedIcon + ' text-primary mr-2 d-flex align-items-center justify-content-center cabin-pill-icon"></i>' :
            '<i class="fas fa-tag text-primary mr-2 d-flex align-items-center justify-content-center cabin-pill-icon"></i>';
        var html = '<div class="audience-pill">' +
            '<input type="hidden" name="audience_name[]" value="' + escapedName + '">' +
            '<input type="hidden" name="audience_icon[]" value="' + escapedIcon + '">' +
            '<button type="button" class="btn btn-link text-dark p-0 btn-remove-audience-pill audience-pill-remove" title="' + $('<div>').text(langDelete).html() + '"><i class="fas fa-times"></i></button>' +
            '<div class="d-flex align-items-center flex-grow-1">' +
                iconHtml +
                '<strong class="text-uppercase small mb-0 audience-name-display' + nameClass + '" data-placeholder="' + $('<div>').text(langAudienceName).html() + '">' + (escapedName || nameText) + '</strong>' +
            '</div>' +
            '</div>';
        $('#selected-audiences').append(html);
    }

    function updateAudienceSelectionCount() {
        var n = $('#audience-modal .audience-item.selected').length;
        $('#audience-modal .audience-selection-count').text(n);
    }

    $cabinForm.on('click', '.btn-add-audience', function () {
        $('#audience-search').val('');
        $('#audience-modal .audience-card-wrapper').show();
        $('#audience-modal .audience-item').removeClass('selected in-list');
        $('#audience-modal .audience-item').each(function () {
            var $item = $(this);
            var name = $item.data('name');
            if (name) {
                var found = false;
                $('#selected-audiences .audience-pill .audience-name-display').each(function () {
                    var currentName = $(this).text().trim();
                    if (currentName === name) {
                        found = true;
                        return false;
                    }
                });
                if (found) {
                    $item.addClass('selected in-list');
                }
            }
        });
        updateAudienceSelectionCount();
        $('#audience-modal').modal('show');
    });

    $(document).on('keyup', '#audience-search', function () {
        var q = $(this).val().toLowerCase();
        $('#audience-modal-list').find('.audience-card-wrapper').each(function () {
            var name = $(this).find('.audience-item').data('name');
            if (name && typeof name === 'string') {
                $(this).toggle(name.toLowerCase().indexOf(q) !== -1);
            }
        });
    });

    $(document).on('click', '#audience-modal .audience-item', function () {
        var $card = $(this);
        if ($card.hasClass('in-list')) {
            var name = $card.data('name');
            if (name) {
                $('#selected-audiences .audience-pill .audience-name-display').each(function () {
                    if ($(this).text().trim() === name) {
                        $(this).closest('.audience-pill').remove();
                        return false;
                    }
                });
            }
            $card.removeClass('selected in-list');
        } else {
            $card.toggleClass('selected');
        }
        updateAudienceSelectionCount();
    });

    $(document).on('click', '.btn-confirm-audience-selection', function () {
        $('#audience-modal .audience-item.selected').each(function () {
            var id = $(this).data('id');
            var name = $(this).data('name') || '';
            var icon = $(this).data('icon') || '';
            if (!id || !name) { return; }

            var exists = false;
            $('#selected-audiences .audience-pill').each(function () {
                if (parseInt($(this).data('id')) === parseInt(id)) {
                    exists = true;
                    return false;
                }
            });
            if (!exists) {
                renderAudience(name, icon);
                var $pill = $('#selected-audiences .audience-pill').last();
                $pill.attr('data-id', id);
                $pill.append('<input type="hidden" name="audience_group_ids[]" value="' + id + '">');
            }
        });
        $('#audience-modal .audience-item').removeClass('selected in-list');
        updateAudienceSelectionCount();
    });

    $cabinForm.on('click', '.btn-remove-audience-pill', function () {
        $(this).closest('.audience-pill').remove();
    });

    function getGuestLabel(guestCount) {
        if (langGuestLabels && langGuestLabels[guestCount]) {
            return langGuestLabels[guestCount];
        }
        var labels = {
            1: '1 Khách (Single)',
            2: '2 Khách (Double)',
            3: '3 Khách (Triple)',
            4: '4 Khách (Quad)',
            5: '5 Khách',
            6: '6 Khách',
            7: '7 Khách',
            8: '8 Khách',
            9: '9 Khách',
            10: '10 Khách'
        };
        return labels[guestCount] || guestCount + ' Khách';
    }

    function updatePriceTable() {
        var capacityInput = $('#capacity-input');
        if (capacityInput.length === 0) {
            capacityInput = $('input[name="capacity"]');
        }
        var capacity = parseInt(capacityInput.val(), 10) || 0;
        if (capacity <= 0 && typeof initialCapacity !== 'undefined') {
            capacity = initialCapacity;
        }
        if (capacity <= 0) {
            capacity = 1;
        }
        var $header = $('#price-table-header');
        var $tbody = $('#price-table-body');

        if ($header.length === 0 || $tbody.length === 0) {
            return;
        }

        $tbody.find('tr').each(function () {
            var $row = $(this);
            var duration = $row.data('duration');
            if (!currentPriceData[duration]) {
                currentPriceData[duration] = {};
            }
            $row.find('input[data-guest]').each(function () {
                var $cellInput = $(this);
                var guest = $cellInput.data('guest');
                var val = $cellInput.val();
                if (val) {
                    val = (typeof AppJs !== 'undefined' && AppJs.normalizePriceForSubmit) ? AppJs.normalizePriceForSubmit(val) : val;
                    if (val) {
                        var num = parseFloat(String(val).replace(/[^\d.-]/g, '')) || 0;
                        if (num > MAX_PRICE) {
                            num = MAX_PRICE;
                            val = num;
                            var displayVal = (typeof AppJs !== 'undefined' && AppJs.formatPriceDisplay) ? AppJs.formatPriceDisplay(String(num)) : num;
                            $cellInput.val(displayVal);
                        }
                        currentPriceData[duration][guest] = val;
                    }
                }
            });
        });

        $header.find('th:not(:first)').remove();

        if (capacity > 0) {
            for (var i = 1; i <= capacity; i++) {
                $header.append('<th>' + getGuestLabel(i) + '</th>');
            }
        }

        $tbody.find('tr').each(function () {
            var $row = $(this);
            var duration = $row.data('duration');

            $row.find('td:first').addClass('td-duration');
            $row.find('td:not(:first)').remove();

            if (capacity > 0) {
                for (var i = 1; i <= capacity; i++) {
                    var existingValue = '';
                    if (currentPriceData[duration] && currentPriceData[duration][i]) {
                        existingValue = currentPriceData[duration][i];
                    } else if (priceData && priceData.length > 0) {
                        for (var j = 0; j < priceData.length; j++) {
                            if (priceData[j].duration == duration && priceData[j].guest == i) {
                                existingValue = priceData[j].price;
                                break;
                            }
                        }
                    } else if (oldPrice && oldPrice[duration] && oldPrice[duration][i] !== undefined && oldPrice[duration][i] !== '') {
                        existingValue = oldPrice[duration][i];
                    }
                    var displayValue = existingValue && (typeof AppJs !== 'undefined' && AppJs.formatPriceDisplay) ? AppJs.formatPriceDisplay(existingValue.toString()) : (existingValue || '');
                    var inputName = 'price[' + duration + '][' + i + ']';
                    var $input = $('<input>')
                        .attr('type', 'text')
                        .attr('name', inputName)
                        .attr('data-duration', duration)
                        .attr('data-guest', i)
                        .addClass('form-control form-control-sm text-right price-input')
                        .attr('placeholder', '0')
                        .val(displayValue);
                    var $cell = $('<td>').append($input);
                    $row.append($cell);
                }
            }
        });
    }

    $cabinForm.on('input change', '#capacity-input, input[name="capacity"]', function () {
        var $input = $(this);
        var value = parseInt($input.val()) || 0;
        if (value > 10) {
            $input.val(10);
        }
        updatePriceTable();
        enforceOverCapacityMax();
    });

    // Over capacity: Adult + 6-12y + 2-5y + Infant must not exceed capacity
    var overCapacityFields = ['over_capacity_adult', 'over_capacity_child_6_12', 'over_capacity_child_2_5', 'over_capacity_infant'];

    function getCapacityForOverCapacity() {
        var $cap = $('#capacity-input');
        if (!$cap.length) {
            $cap = $cabinForm.find('input[name="capacity"]');
        }
        var cap = parseInt($cap.val(), 10) || 0;
        return cap <= 0 ? 1 : Math.min(10, cap);
    }

    function getOverCapacityValues() {
        var out = {};
        overCapacityFields.forEach(function (name) {
            var $field = $cabinForm.find('input[name="' + name + '"]');
            var v = $field.length ? parseInt($field.val(), 10) : 0;
            out[name] = isNaN(v) || v < 0 ? 0 : v;
        });
        return out;
    }

    function enforceOverCapacityMax() {
        var capacity = getCapacityForOverCapacity();
        var vals = getOverCapacityValues();
        var total = vals.over_capacity_adult + vals.over_capacity_child_6_12 + vals.over_capacity_child_2_5 + vals.over_capacity_infant;
        if (total <= capacity) {
            $cabinForm.find('.over-capacity-feedback').remove();
            return;
        }
        var order = ['over_capacity_infant', 'over_capacity_child_2_5', 'over_capacity_child_6_12', 'over_capacity_adult'];
        var remaining = capacity;
        for (var i = order.length - 1; i >= 0; i--) {
            var name = order[i];
            var current = vals[name];
            var allow = Math.min(current, remaining);
            remaining -= allow;
            var $field = $cabinForm.find('input[name="' + name + '"]');
            if ($field.length && parseInt($field.val(), 10) !== allow) {
                $field.val(allow);
            }
        }
        showOverCapacityFeedback();
    }

    function capSingleOverCapacityField($input) {
        var name = $input.attr('name');
        if (!name || overCapacityFields.indexOf(name) === -1) { return; }
        var capacity = getCapacityForOverCapacity();
        var vals = getOverCapacityValues();
        var otherSum = 0;
        overCapacityFields.forEach(function (n) {
            if (n !== name) { otherSum += vals[n]; }
        });
        var maxThis = Math.max(0, capacity - otherSum);
        var current = parseInt($input.val(), 10);
        if (isNaN(current) || current < 0) {
            $input.val(0);
            return;
        }
        if (current > maxThis) {
            $input.val(maxThis);
            showOverCapacityFeedback();
        } else {
            $cabinForm.find('.over-capacity-feedback').remove();
        }
    }

    function showOverCapacityFeedback() {
        if ($cabinForm.find('.over-capacity-feedback').length) { return; }
        var capacity = getCapacityForOverCapacity();
        var msgTemplate = (config.langOverCapacityTotal || 'Tổng số khách không được vượt quá sức chứa tối đa (__CAP__).');
        var msg = String(msgTemplate).replace('__CAP__', capacity);
        var $wrap = $cabinForm.find('input[name="over_capacity_infant"]').closest('.form-group').closest('.row');
        if ($wrap.length) {
            $wrap.after('<div class="over-capacity-feedback text-danger small mt-1">' + $('<div>').text(msg).html() + '</div>');
        }
    }

    overCapacityFields.forEach(function (name) {
        var selector = 'input[name="' + name + '"]';
        $cabinForm.on('input change blur', selector, function () {
            capSingleOverCapacityField($(this));
        });
        $cabinForm.on('paste', selector, function () {
            var $input = $(this);
            setTimeout(function () { capSingleOverCapacityField($input); }, 0);
        });
    });

    function capAreaInput($input) {
        var val = $input.val();
        if (val === '' || val == null) { return; }
        var num = parseFloat(String(val).replace(',', '.')) || 0;
        if (num > 10000) {
            $input.val(10000);
        }
    }
    $cabinForm.on('input change', 'input[name="area"]', function () {
        capAreaInput($(this));
    });
    $cabinForm.on('paste', 'input[name="area"]', function () {
        var $input = $(this);
        setTimeout(function () { capAreaInput($input); }, 0);
    });

    var MAX_PRICE_STR = '10000000000';

    function capPriceInput($input) {
        var val = $input.val();
        if (!val) { return; }
        var digitsOnly = String(val).replace(/\D/g, '');
        if (digitsOnly === '') { return; }
        var capped = false;
        if (digitsOnly.length > 11) {
            capped = true;
        } else if (digitsOnly.length === 11 && digitsOnly > MAX_PRICE_STR) {
            capped = true;
        }
        if (capped) {
            var displayVal = (typeof AppJs !== 'undefined' && AppJs.formatPriceDisplay) ? AppJs.formatPriceDisplay(MAX_PRICE_STR) : MAX_PRICE_STR;
            $input.val(displayVal);
        }
    }

    $cabinForm.on('input', '.price-input', function () {
        capPriceInput($(this));
    });

    $cabinForm.on('paste', '.price-input', function () {
        var $input = $(this);
        setTimeout(function () { capPriceInput($input); }, 0);
    });

    $cabinForm.on('blur', '.price-input', function () {
        var $input = $(this);
        capPriceInput($input);
        var duration = $input.data('duration');
        var guest = $input.data('guest');
        var val = $input.val();
        if (val) {
            val = (typeof AppJs !== 'undefined' && AppJs.normalizePriceForSubmit) ? AppJs.normalizePriceForSubmit(val) : val;
            if (val) {
                var num = parseFloat(String(val).replace(/[^\d.-]/g, '')) || 0;
                if (num > MAX_PRICE) {
                    num = MAX_PRICE;
                    val = num;
                    var displayVal = (typeof AppJs !== 'undefined' && AppJs.formatPriceDisplay) ? AppJs.formatPriceDisplay(String(num)) : num;
                    $input.val(displayVal);
                }
                if (!currentPriceData[duration]) {
                    currentPriceData[duration] = {};
                }
                currentPriceData[duration][guest] = val;
            }
        }
    });

    function repopulateFromOld() {
        var i;
        if (oldRoomTitles && oldRoomTitles.length > 0) {
            $('#selected-rooms').empty();
            for (i = 0; i < oldRoomTitles.length; i++) {
                renderRoom(
                    typeof oldRoomTitles[i] === 'string' ? oldRoomTitles[i] : '',
                    (oldRoomDescriptions && oldRoomDescriptions[i] !== undefined) ? oldRoomDescriptions[i] : ''
                );
            }
        }
        if (oldAmenityIds && oldAmenityIds.length > 0 && listAmenityMap) {
            $('#selected-amenities').empty();
            for (i = 0; i < oldAmenityIds.length; i++) {
                var id = oldAmenityIds[i];
                var info = listAmenityMap[id];
                if (info) {
                    renderExistingAmenity(id, info.name || '', info.icon || '');
                }
            }
        }
        if (oldAmenityName && oldAmenityName.length > 0) {
            if (!oldAmenityIds || oldAmenityIds.length === 0) {
                $('#selected-amenities').empty();
            }
            for (i = 0; i < oldAmenityName.length; i++) {
                var n = oldAmenityName[i];
                if (!n) { continue; }
                renderNewAmenity(
                    n,
                    (oldAmenityDescription && oldAmenityDescription[i] !== undefined) ? oldAmenityDescription[i] : '',
                    (oldAmenityIcon && oldAmenityIcon[i] !== undefined) ? oldAmenityIcon[i] : ''
                );
            }
        }
        if (oldAudienceName && oldAudienceName.length > 0) {
            $('#selected-audiences').empty();
            for (i = 0; i < oldAudienceName.length; i++) {
                var an = oldAudienceName[i];
                if (!an) { continue; }
                renderAudience(an, (oldAudienceIcon && oldAudienceIcon[i] !== undefined) ? oldAudienceIcon[i] : '');
                if (oldAudienceIds && oldAudienceIds[i] !== undefined && oldAudienceIds[i]) {
                    var $pill = $('#selected-audiences .audience-pill').last();
                    $pill.attr('data-id', oldAudienceIds[i]);
                    $pill.append('<input type="hidden" name="audience_group_ids[]" value="' + oldAudienceIds[i] + '">');
                }
            }
        }
    }

    function syncPriceDataFromTable() {
        $('#price-table-body').find('tr').each(function () {
            var $row = $(this);
            var duration = $row.data('duration');
            if (!currentPriceData[duration]) {
                currentPriceData[duration] = {};
            }
            $row.find('input[data-guest]').each(function () {
                var $cellInput = $(this);
                var guest = $cellInput.data('guest');
                var val = $cellInput.val();
                if (val) {
                    val = (typeof AppJs !== 'undefined' && AppJs.normalizePriceForSubmit) ? AppJs.normalizePriceForSubmit(val) : val;
                    if (val) {
                        var num = parseFloat(String(val).replace(/[^\d.-]/g, '')) || 0;
                        if (num > MAX_PRICE) {
                            num = MAX_PRICE;
                            val = num;
                            var displayVal = (typeof AppJs !== 'undefined' && AppJs.formatPriceDisplay) ? AppJs.formatPriceDisplay(String(num)) : num;
                            $cellInput.val(displayVal);
                        }
                        currentPriceData[duration][guest] = val;
                    }
                }
            });
        });
    }

    $(document).ready(function () {
        repopulateFromOld();
        if (isEdit) {
            syncPriceDataFromTable();
        } else {
            setTimeout(function () {
                updatePriceTable();
            }, 100);
        }
        enforceOverCapacityMax();
        if (typeof AppJs !== 'undefined' && AppJs.bindPriceInputs) {
            AppJs.bindPriceInputs('#price-table', '#cabin-form');
        }
    });
})();
