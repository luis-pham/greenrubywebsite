window.__updateEstimatedTotal = null;

document.addEventListener('DOMContentLoaded', function () {
    function clearBookingStep1Errors() {
        ['date', 'voyage', 'cabin'].forEach(function (f) {
            var el = document.getElementById('booking-error-' + f);
            if (el) el.textContent = '';
        });
    }
    function clearBookingStep2Errors() {
        ['fullName', 'phone', 'email'].forEach(function (f) {
            var el = document.getElementById('booking-error-' + f);
            if (el) el.textContent = '';
        });
    }
    function showStep1InlineErrors(fieldErrors) {
        clearBookingStep1Errors();
        if (!fieldErrors) return;
        var cabinParts = [];
        for (var k in fieldErrors) {
            if (fieldErrors.hasOwnProperty(k)) {
                if (k === 'voyage') {
                    var ev = document.getElementById('booking-error-voyage');
                    if (ev) ev.textContent = fieldErrors[k];
                } else if (k === 'cabin') {
                    cabinParts.push(fieldErrors[k]);
                } else if (k.indexOf('cabin_') === 0) {
                    cabinParts.push(fieldErrors[k]);
                }
            }
        }
        if (cabinParts.length) {
            var cv = document.getElementById('booking-error-cabin');
            if (cv) cv.textContent = cabinParts.join(' ');
        }
    }
    function showStep2InlineErrors(fieldErrors) {
        clearBookingStep2Errors();
        if (!fieldErrors) return;
        ['fullName', 'phone', 'email'].forEach(function (f) {
            if (fieldErrors[f]) {
                var el = document.getElementById('booking-error-' + f);
                if (el) el.textContent = fieldErrors[f];
            }
        });
    }
    function showDateError(msg) {
        var el = document.getElementById('booking-error-date');
        if (el && msg) el.textContent = msg;
    }
    function clearDateError() {
        var el = document.getElementById('booking-error-date');
        if (el) el.textContent = '';
    }

    (function initBookingLocaleAndCurrency() {
        var page = document.getElementById('booking-page');
        var loc = (page && page.getAttribute('data-lang')) ? String(page.getAttribute('data-lang')).toLowerCase() : 'en';
        window.__bookingLocale = (loc === 'vi') ? 'vi' : 'en';
        window.__bookingIsVnd = (window.__bookingLocale === 'vi');
        window.__bookingFormatAmount = function (value) {
            var num = Number(value) || 0;
            if (window.__bookingIsVnd) {
                var rounded = Math.round(num);
                return rounded.toLocaleString('vi-VN') + ' VND';
            }
            var rounded = Math.round(num * 100) / 100;
            return '$' + rounded.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };
    })();

    (function initDepartureDatePicker() {
        var page = document.getElementById('booking-page');
        var dateInputEl = document.getElementById('booking-departure-date');
        var $dateInput = $('#booking-departure-date');
        var dateDisplay = document.getElementById('booking-date-display');
        var summaryDateDisplay = document.getElementById('summary-date-display');

        function formatDateForDisplay(dateStr) {
            if (!dateStr) return null;
            var parts = dateStr.split('/');
            if (parts.length === 3) {
                return dateStr;
            }
            var d = new Date(dateStr + 'T00:00:00');
            if (isNaN(d.getTime())) return null;
            var m = (d.getMonth() + 1).toString().padStart(2, '0');
            var day = d.getDate().toString().padStart(2, '0');
            var y = d.getFullYear();
            return m + '/' + day + '/' + y;
        }

        var lastDispatchedDate = null;
        function updateDisplaysFromPicker(val) {
            if (val && typeof clearDateError === 'function') clearDateError();
            var formatted = formatDateForDisplay(val);
            if (dateDisplay) {
                var placeholder = dateDisplay.getAttribute('data-placeholder') || 'Departure Date';
                dateDisplay.textContent = formatted || placeholder;
            }
            if (summaryDateDisplay) summaryDateDisplay.textContent = formatted || '—';
            if (page) {
                var ymdDate = convertDdMmYyyyToYmd(val) || val;
                page.setAttribute('data-departure-date', ymdDate);
            }
            var normVal = (val && typeof val === 'string') ? val.trim() : '';
            if (lastDispatchedDate === normVal) return;
            lastDispatchedDate = normVal;
            try {
                var ev = new CustomEvent('booking:dateSelected', { detail: { date: val } });
                document.dispatchEvent(ev);
            } catch (e) {
            }
        }

        async function fetchDepartureDatesAndInit() {
            if (!$dateInput.length || !page) return;
            var apiUrl = page.getAttribute('data-api-departure-dates') || '/api/booking/departure-dates';
            var lang = page.getAttribute('data-lang') || 'vi';
            var cruiseId = page.getAttribute('data-init-cruise-id') || '';
            var itineraryId = page.getAttribute('data-init-itinerary-id') || '';
            var selectedDateFromQuery = null;

            if (typeof window !== 'undefined' && window.location && window.location.search) {
                var search = window.location.search.slice(1);
                if (search) {
                    search.split('&').forEach(function (p) {
                        if (!p) return;
                        var i = p.indexOf('=');
                        if (i > 0) {
                            var key = decodeURIComponent(p.slice(0, i));
                            if (key === 'start_at' || key === 'date') {
                                var val = decodeURIComponent(p.slice(i + 1));
                                selectedDateFromQuery = val || null;
                            }
                        }
                    });
                }
            }

            var params = 'lang=' + encodeURIComponent(lang);
            if (cruiseId) params += '&cruise_id=' + encodeURIComponent(cruiseId);
            if (itineraryId) params += '&itinerary_id=' + encodeURIComponent(itineraryId);

            var fullUrl = params ? (apiUrl + '?' + params) : apiUrl;

            try {
                var r = await fetch(fullUrl, { headers: { 'Accept': 'application/json' } });
                var data = await r.json();
                var items = (data && data.success && Array.isArray(data.items)) ? data.items : [];
                var listDateEnabled = items
                    .map(function (it) { return it.start_at; })
                    .filter(function (d) { return !!d; });

                initializeDatePicker($dateInput, listDateEnabled);

                var currentVal = ($dateInput.val() || '').trim();
                var targetYmd = null;

                if (selectedDateFromQuery) {
                    var norm = selectedDateFromQuery.trim();
                    if (/^\d{2}-\d{2}-\d{4}$/.test(norm)) {
                        var parts = norm.split('-');
                        targetYmd = parts[2] + '-' + parts[1] + '-' + parts[0];
                    } else if (/^\d{2}\/\d{2}\/\d{4}$/.test(norm)) {
                        targetYmd = convertDdMmYyyyToYmd(norm) || norm;
                    } else {
                        targetYmd = norm;
                    }
                    if (targetYmd && listDateEnabled.indexOf(targetYmd) === -1) {
                        targetYmd = null;
                    }
                }

                if (!targetYmd && (cruiseId || itineraryId) && items.length > 0) {
                    for (var i = 0; i < items.length; i++) {
                        if (items[i] && items[i].start_at) {
                            targetYmd = items[i].start_at;
                            break;
                        }
                    }
                }

                if (!currentVal && targetYmd) {
                    var displayVal = convertYmdToDdMmYyyy(targetYmd) || targetYmd;
                    $dateInput.val(displayVal);
                    updateDisplaysFromPicker(displayVal);
                }

                var wrapper = $dateInput.parent();
                var dateWrap = document.getElementById('booking-date-input-wrap');
                if (dateWrap) {
                    dateWrap.addEventListener('click', function () {
                        try {
                            wrapper.datetimepicker('show');
                        } catch (e) {
                            console.warn('datetimepicker show error', e);
                        }
                    });
                }

                wrapper.on('change.datetimepicker', function (e) {
                    var val = $dateInput.val();
                    if (e && e.date && e.date.format) {
                        val = e.date.format('DD/MM/YYYY');
                        $dateInput.val(val);
                    }
                    updateDisplaysFromPicker(val);
                });

                $dateInput.on('change blur', function () {
                    updateDisplaysFromPicker(this.value);
                });
            } catch (e) {
                initializeDatePicker($dateInput, []);

                var wrapper = $dateInput.parent();
                var dateWrap = document.getElementById('booking-date-input-wrap');
                if (dateWrap) {
                    dateWrap.addEventListener('click', function () {
                        try {
                            wrapper.datetimepicker('show');
                        } catch (err) {
                            console.warn('datetimepicker show error', err);
                        }
                    });
                }

                wrapper.on('change.datetimepicker', function (e) {
                    var val = $dateInput.val();
                    if (e && e.date && e.date.format) {
                        val = e.date.format('DD/MM/YYYY');
                        $dateInput.val(val);
                    }
                    updateDisplaysFromPicker(val);
                });

                $dateInput.on('change blur', function () {
                    updateDisplaysFromPicker(this.value);
                });
            }
        }

        fetchDepartureDatesAndInit();
    })();

    (function initVoyageSelect() {
        var page = document.getElementById('booking-page');
        var dateInput = document.getElementById('booking-departure-date');
        var wrap = document.getElementById('booking-voyage-select-wrap');
        var display = document.getElementById('voyage-select-display');
        var panel = document.getElementById('voyage-dropdown-panel');
        var list = document.getElementById('voyage-dropdown-list');
        var loading = document.getElementById('voyage-dropdown-loading');
        var apiUrl = (page && page.getAttribute('data-api-itineraries')) || '/api/booking/itineraries';
        var apiItineraryDetailUrl = (page && page.getAttribute('data-api-itinerary-detail')) || '/api/booking/itinerary';
        var lang = page ? (page.getAttribute('data-lang') || 'vi') : 'vi';
        var msgDateRequired = page ? (page.getAttribute('data-msg-date-required') || '') : '';
        var itineraryItems = [];
        var selectedVoyage = null;
        var hasAppliedInitVoyage = false;

        function formatDurationLabel(d) {
            if (!d) return '';
            return d === 2 ? '2N1D' : d + 'N' + (d - 1) + 'D';
        }

        function renderInclusions(activities) {
            var container = document.getElementById('itinerary-card-inclusions-list');
            var parent = container && container.parentNode ? container.parentNode : null;
            if (!container) return;
            if (!Array.isArray(activities) || activities.length === 0) {
                container.innerHTML = '';
                if (parent) parent.style.display = 'none';
                return;
            }
            if (parent) parent.style.display = '';
            container.innerHTML = activities.map(function (a) {
                var name = (a && a.name) ? String(a.name).replace(/</g, '&lt;').replace(/>/g, '&gt;') : '';
                return '<div class="check-parent"><i class="fa-solid fa-check"></i><div class="inclusion-item-text">' + name + '</div></div>';
            }).join('');
        }

        function renderTimeline(destinations) {
            var timelineWrap = document.querySelector('#booking-page .itinerary-timeline-wrap');
            var timelineEl = document.getElementById('itinerary-card-timeline');
            var lineRow = document.getElementById('itinerary-card-timeline-line');
            var labelsRow = document.getElementById('itinerary-card-timeline-labels');
            if (!lineRow || !labelsRow) return;
            if (!Array.isArray(destinations) || destinations.length === 0) {
                lineRow.innerHTML = '';
                labelsRow.innerHTML = '';
                if (timelineEl) timelineEl.style.gridTemplateColumns = '';
                if (timelineWrap) timelineWrap.style.display = 'none';
                return;
            }
            if (timelineWrap) timelineWrap.style.display = '';

            var N = destinations.length;
            var isMobileTimeline = window.matchMedia && window.matchMedia('(max-width: 900px)').matches;

            if (isMobileTimeline) {
                lineRow.innerHTML = '';
                var verticalRows = [];
                for (var i = 0; i < N; i++) {
                    var rawName = destinations[i];
                    var safeName = (rawName != null ? String(rawName) : '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    var nameCls = i === 0 ? 'itinerary-stop-primary' : 'itinerary-stop-secondary';
                    var connectorHtml = i < N - 1
                        ? '<div class="itinerary-timeline-vertical-connector"></div>'
                        : '';
                    verticalRows.push(
                        '<div class="itinerary-timeline-vertical-row">' +
                            '<div class="itinerary-timeline-vertical-icon">' +
                                '<i class="fa-solid fa-sailboat"></i>' +
                                connectorHtml +
                            '</div>' +
                            '<div class="itinerary-timeline-vertical-label">' +
                                '<b class="' + nameCls + '">' + safeName + '</b>' +
                            '</div>' +
                        '</div>'
                    );
                }
                labelsRow.innerHTML = verticalRows.join('');
                if (timelineEl) {
                    timelineEl.style.gridTemplateColumns = '';
                }
                return;
            }

            var cols = ['auto'];
            for (var c = 0; c < N - 1; c++) { cols.push('1fr'); cols.push('auto'); }
            if (timelineEl) timelineEl.style.gridTemplateColumns = cols.join(' ');
            var lineParts = [];
            for (var i2 = 0; i2 < N; i2++) {
                if (i2 > 0) {
                    lineParts.push('<div class="group-14-row-0-inner"><div class="itinerary-route-line"></div></div>');
                }
                lineParts.push('<div class="sailboat' + (i2 === 0 ? '' : (i2 === 1 ? '3' : '4')) + '"><i class="fa-solid fa-sailboat"></i></div>');
            }
            lineRow.innerHTML = lineParts.join('');
            var labelParts = [];
            destinations.forEach(function (name, i3) {
                if (i3 > 0) {
                    labelParts.push('<div class="itinerary-timeline-label-spacer"></div>');
                }
                var safe = (name != null ? String(name) : '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                var cls = i3 === 0 ? 'itinerary-stop-primary' : 'itinerary-stop-secondary';
                labelParts.push('<div class="itinerary-timeline-label-cell"><b class="' + cls + '">' + safe + '</b></div>');
            });
            labelsRow.innerHTML = labelParts.join('');
        }

        function updateCardAndSummary(item) {
            var cardEl = document.getElementById('booking-itinerary-card');
            var titleEl = document.getElementById('itinerary-card-title');
            var cruiseEl = document.getElementById('itinerary-card-cruise');
            var durationEl = document.getElementById('itinerary-card-duration');
            var destEl = document.getElementById('itinerary-card-destination');
            var summaryTitle = document.getElementById('summary-voyage-title');
            var summaryCruise = document.getElementById('summary-voyage-cruise');
            if (cardEl) cardEl.style.display = '';
            if (titleEl) titleEl.textContent = item.name;
            if (cruiseEl) cruiseEl.textContent = item.cruise_name;
            if (durationEl) durationEl.textContent = formatDurationLabel(item.duration);
            if (destEl) {
                var fullDestination = item.destination || '';
                var firstDestination = fullDestination.split(',')[0].trim();
                destEl.textContent = firstDestination || '-';
            }
            if (summaryTitle) summaryTitle.textContent = item.name;
            if (summaryCruise) summaryCruise.textContent = item.cruise_name;
            renderInclusions([]);
            renderTimeline([]);
            var itineraryId = item.itinerary_id != null ? item.itinerary_id : item.id;

            var detailLink = document.getElementById('booking-itinerary-detail-link');
            if (detailLink && item && itineraryId && item.cruise_id && page) {
                var template = page.getAttribute('data-itinerary-url-template') || '';
                if (template) {
                    var slugSource = item.name || '';
                    var slug = String(slugSource);
                    try {
                        if (slug.normalize) {
                            slug = slug.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        }
                    } catch (e) {}
                    slug = slug
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/-+/g, '-')
                        .replace(/^-|-$/g, '');

                    var href = template
                        .replace('__SLUG__', slug)
                        .replace('__CRUISE__', String(item.cruise_id))
                        .replace('__ITINERARY__', String(itineraryId));

                    detailLink.href = href;
                    detailLink.target = '_blank';
                }
            }

            if (apiItineraryDetailUrl && itineraryId) {
                fetch(apiItineraryDetailUrl + '/' + encodeURIComponent(itineraryId), { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data && data.success) {
                            if (Array.isArray(data.activities)) renderInclusions(data.activities);
                            if (Array.isArray(data.destinations)) renderTimeline(data.destinations);
                        }
                    })
                    .catch(function () {
                        renderInclusions([]);
                        renderTimeline([]);
                    });
            }
        }

        function setDisplayText(text) {
            if (display) display.textContent = text;
        }

        function closeDropdown() {
            if (panel) panel.classList.remove('is-open');
            document.removeEventListener('click', closeOnClickOutside);
        }

        function closeOnClickOutside(e) {
            if (wrap && !wrap.contains(e.target)) closeDropdown();
        }

        function toggleDropdown() {
            if (!panel) return;
            if (!dateInput || !dateInput.value) {
                if (msgDateRequired && typeof showDateError === 'function') {
                    showDateError(msgDateRequired);
                }
                return;
            }
            if (typeof clearDateError === 'function') clearDateError();
            if (panel.classList.contains('is-open')) {
                closeDropdown();
            } else {
                panel.classList.add('is-open');
                if (itineraryItems.length === 0) fetchItineraries();
                setTimeout(function () {
                    document.addEventListener('click', closeOnClickOutside);
                }, 0);
            }
        }

        function fetchItineraries() {
            if (!apiUrl) return;
            if (loading) loading.classList.add('is-visible');
            if (list) list.innerHTML = '';
            var params = 'lang=' + encodeURIComponent(lang) + '&unique=itinerary';
            if (dateInput && dateInput.value) {
                var dateValue = convertDdMmYyyyToYmd(dateInput.value) || dateInput.value;
                params += '&date=' + encodeURIComponent(dateValue);
            }
            fetch(apiUrl + '?' + params, { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (loading) loading.classList.remove('is-visible');
                    if (!data || !data.success) {
                        var failedMessage = lang === 'vi' ? 'Không tải được dữ liệu' : 'Failed to load';
                        setDisplayText(failedMessage);
                        return;
                    }
                    itineraryItems = Array.isArray(data.items) ? data.items : [];
                    if (list) {
                        if (itineraryItems.length === 0) {
                            var emptyMessage = lang === 'vi' 
                                ? 'Không có hành trình tham quan nào vào ngày khởi hành bạn đã chọn. Vui lòng chọn ngày khác.'
                                : 'There is no sightseeing itinerary on your selected departure date. Please choose another date.';
                            list.innerHTML = '<div class="voyage-dropdown-empty">' + emptyMessage + '</div>';
                            return;
                        }
                        list.innerHTML = itineraryItems.map(function (it) {
                            var label = it.name + (it.cruise_name ? ' - ' + it.cruise_name : '') + (it.duration_label ? ' (' + it.duration_label + ')' : '');
                            return '<div class="voyage-dropdown-item" data-id="' + (it.id || '') + '">' + (label || '').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</div>';
                        }).join('');
                        list.querySelectorAll('.voyage-dropdown-item').forEach(function (el) {
                            el.addEventListener('click', function (e) {
                                e.stopPropagation();
                                var id = el.getAttribute('data-id');
                                var item = itineraryItems.find(function (i) { return String(i.id) === String(id); });
                                if (item) {
                                    selectedVoyage = item;
                                    setDisplayText(item.name + (item.cruise_name ? ' - ' + item.cruise_name : ''));
                                    updateCardAndSummary(item);
                                    if (page) {
                                        page.setAttribute('data-itinerary-id', item.id != null ? String(item.id) : '');
                                        page.setAttribute('data-cruise-id', item.cruise_id != null ? String(item.cruise_id) : '');
                                    }
                                    try {
                                        var ev = new CustomEvent('booking:itinerarySelected', { detail: item });
                                        document.dispatchEvent(ev);
                                    } catch (e) {
                                    }
                                    closeDropdown();
                                }
                            });
                        });
                        if (!hasAppliedInitVoyage && page) {
                            var initItineraryId = page.getAttribute('data-init-itinerary-id') || '';
                            var initCruiseId = page.getAttribute('data-init-cruise-id') || '';
                            if (initItineraryId) {
                                var autoItem = itineraryItems.find(function (it) {
                                    if (String(it.itinerary_id) !== String(initItineraryId)) return false;
                                    if (initCruiseId && String(it.cruise_id) !== String(initCruiseId)) return false;
                                    return true;
                                });
                                if (autoItem) {
                                    selectedVoyage = autoItem;
                                    setDisplayText(autoItem.name + (autoItem.cruise_name ? ' - ' + autoItem.cruise_name : ''));
                                    updateCardAndSummary(autoItem);
                                    page.setAttribute('data-itinerary-id', autoItem.id != null ? String(autoItem.id) : '');
                                    page.setAttribute('data-cruise-id', autoItem.cruise_id != null ? String(autoItem.cruise_id) : '');
                                    try {
                                        var ev2 = new CustomEvent('booking:itinerarySelected', { detail: autoItem });
                                        document.dispatchEvent(ev2);
                                    } catch (e2) {
                                    }
                                    hasAppliedInitVoyage = true;
                                }
                            }
                        }
                    }
                })
                .catch(function (err) {
                    if (loading) loading.classList.remove('is-visible');
                    var failedMessage = lang === 'vi' ? 'Không tải được dữ liệu' : 'Failed to load';
                    var connectionError = lang === 'vi' ? 'Lỗi kết nối' : 'Connection error';
                    setDisplayText(failedMessage);
                    if (list) list.innerHTML = '<div class="voyage-dropdown-empty">' + connectionError + '</div>';
                });
        }

        if (wrap) {
            wrap.style.position = 'relative';
            wrap.addEventListener('click', function (e) {
                if (e.target.closest('.voyage-dropdown-panel')) return;
                toggleDropdown();
            });
        }

        var lastDateSelected = null;
        document.addEventListener('booking:dateSelected', function (ev) {
            var newDate = (ev && ev.detail && ev.detail.date) ? String(ev.detail.date) : '';
            if (lastDateSelected === newDate) return;
            lastDateSelected = newDate;

            selectedVoyage = null;
            itineraryItems = [];
            if (page) {
                page.removeAttribute('data-itinerary-id');
                page.removeAttribute('data-cruise-id');
            }
            var selectVoyageText = lang === 'vi' ? 'Chọn hành trình' : 'Select voyage';
            setDisplayText(selectVoyageText);
            if (panel) {
                panel.classList.remove('is-open');
            }
        });

        document.addEventListener('booking:initVoyageFromCabin', function () {
            if (!apiUrl) return;
            if (!dateInput || !dateInput.value) return;
            fetchItineraries();
        });

        if (page && (page.getAttribute('data-init-itinerary-id') || page.getAttribute('data-init-cruise-id'))) {
            if (apiUrl) fetchItineraries();
        }
    })();

    (function initAmenities() {
        var page = document.getElementById('booking-page');
        var apiUrl = (page && page.getAttribute('data-api-amenities')) || '/api/booking/amenities';
        var lang = page ? (page.getAttribute('data-lang') || 'vi') : 'vi';
        var section = document.querySelector('#booking-page .amenities-step-section');

        if (!page || !section || !apiUrl) {
            return;
        }

        var hasLoaded = false;
        var lastItineraryId = null;
        var services = [];

        function setVisible(visible) {
            section.style.display = visible ? '' : 'none';
        }

        function formatPrice(price) {
            if (price === null || price === undefined) return window.__bookingFormatAmount ? window.__bookingFormatAmount(0) : '0';
            return window.__bookingFormatAmount ? window.__bookingFormatAmount(price) : (Number(price) || 0).toString();
        }

        function render() {
            var cards = section.querySelectorAll('.amenity-card-transfer, .amenity-card-spa, .amenity-card-welcome, .amenity-card-photo, .amenity-card-drone');
            if (!cards.length || !services.length) {
                setVisible(false);
                return;
            }

            cards.forEach(function (card, idx) {
                var svc = services[idx];
                if (!svc) {
                    card.style.display = 'none';
                    return;
                }
                card.style.display = '';
                card.setAttribute('data-amenity-name', svc.name || '');
                var unitPrice = svc.price;
                if (unitPrice === null || unitPrice === undefined) {
                    unitPrice = 0;
                }
                card.setAttribute('data-amenity-price', String(unitPrice));
                var titleEl = card.querySelector('.amenity-title, .amenity-title-wide');
                var priceEl = card.querySelector('.label-check');
                if (titleEl) {
                    titleEl.textContent = svc.name || '';
                    var titleBox = titleEl;
                    setTimeout(function () {
                        if (!titleBox) return;
                        var container = titleBox.parentElement;
                        if (!container) return;
                        var containerWidth = container.clientWidth;
                        if (containerWidth <= 0) return;
                        var origMax = titleBox.style.maxWidth;
                        titleBox.style.maxWidth = 'none';
                        var fullWidth = titleBox.scrollWidth;
                        titleBox.style.maxWidth = origMax || '';
                        if (fullWidth > containerWidth) {
                            var scrollDistance = fullWidth - containerWidth;
                            var distPx = '-' + scrollDistance + 'px';
                            container.style.setProperty('--scroll-distance', distPx);
                            titleBox.style.setProperty('--scroll-distance', distPx);
                            container.classList.add('amenity-title--scrollable');
                        } else {
                            container.classList.remove('amenity-title--scrollable');
                            container.style.removeProperty('--scroll-distance');
                            titleBox.style.removeProperty('--scroll-distance');
                        }
                    }, 50);
                }
                if (priceEl) {
                    priceEl.textContent = formatPrice(svc.price);
                }

                var iconWrap = card.querySelector('.amenity-icon-wrap');
                if (iconWrap) {
                    if (svc.image_url) {
                        iconWrap.style.backgroundImage = 'url(' + svc.image_url + ')';
                        iconWrap.style.backgroundSize = 'cover';
                        iconWrap.style.backgroundPosition = 'center';
                        iconWrap.style.backgroundRepeat = 'no-repeat';
                        var iconEl = iconWrap.querySelector('i');
                        if (iconEl) {
                            iconEl.style.display = 'none';
                        }
                    } else {
                        iconWrap.style.backgroundImage = '';
                    }
                }
            });

            setVisible(true);
            initAmenityQuantityHandlers();
        }

        function findAmenityQuantityValueEl(card) {
            var candidates = card.querySelectorAll('.quantity-parent .mmddyyyy, .quantity-group .mmddyyyy, .plus-parent .mmddyyyy, .plus-group .mmddyyyy, .plus-container .mmddyyyy');
            for (var i = 0; i < candidates.length; i++) {
                var t = (candidates[i].textContent || '').trim();
                if (/^\d+$/.test(t)) {
                    return candidates[i];
                }
            }
            return null;
        }

        function updateSummaryAmenities() {
            var summaryList = document.querySelector('#booking-page .summary-amenities-list');
            var summaryBlock = document.getElementById('summary-amenities-block');
            if (!summaryList || !summaryBlock) {
                return;
            }

            summaryList.innerHTML = '';

            var cards = section.querySelectorAll('.amenity-card-transfer, .amenity-card-spa, .amenity-card-welcome, .amenity-card-photo, .amenity-card-drone');
            var hasAny = false;

            cards.forEach(function (card) {
                var valueEl = findAmenityQuantityValueEl(card);
                if (!valueEl) {
                    return;
                }
                var t = (valueEl.textContent || '').trim();
                var qty = parseInt(t, 10);
                if (isNaN(qty) || qty <= 0) {
                    return;
                }

                hasAny = true;

                var name = '';
                var titleEl = card.querySelector('.amenity-title, .amenity-title-wide');
                if (titleEl && titleEl.textContent) {
                    name = titleEl.textContent.trim();
                } else {
                    var dataName = card.getAttribute('data-amenity-name');
                    if (dataName) {
                        name = dataName;
                    }
                }

                var pricePerUnit = 0;
                var priceAttr = card.getAttribute('data-amenity-price');
                if (priceAttr !== null && priceAttr !== undefined) {
                    pricePerUnit = Number(priceAttr) || 0;
                } else {
                    var priceEl = card.querySelector('.label-check');
                    if (priceEl && priceEl.textContent) {
                        var raw = priceEl.textContent.replace(/[^0-9.]/g, '');
                        pricePerUnit = Number(raw) || 0;
                    }
                }

                var totalPrice = pricePerUnit * qty;

                var line = document.createElement('div');
                line.className = 'summary-amenity-line';

                var nameDiv = document.createElement('div');
                nameDiv.className = 'summary-amenity-name';
                nameDiv.textContent = name;

                var qtyWrap = document.createElement('div');
                qtyWrap.className = 'quantity-2';
                var qtyText = document.createElement('div');
                qtyText.className = 'mmddyyyy';
                var qtyLabel = (window.__bookingLocale === 'vi') ? 'Số lượng: ' : 'Quantity: ';
                qtyText.textContent = qtyLabel + qty;
                qtyWrap.appendChild(qtyText);

                var totalDiv = document.createElement('div');
                totalDiv.className = 'summary-price-value';
                totalDiv.textContent = formatPrice(totalPrice);

                line.appendChild(nameDiv);
                line.appendChild(qtyWrap);
                line.appendChild(totalDiv);

                summaryList.appendChild(line);
            });

            if (!hasAny) {
                summaryBlock.style.display = 'none';
            } else {
                summaryBlock.style.display = '';
            }

            updateEstimatedTotalFromSummary();
        }

        var amenityDelegationBound = false;
        function initAmenityQuantityHandlers() {
            if (amenityDelegationBound) {
                updateSummaryAmenities();
                return;
            }
            amenityDelegationBound = true;
            section.addEventListener('click', function (e) {
                var icon = e.target && e.target.closest ? e.target.closest('.plus-icon') : null;
                if (!icon) return;
                var iconContainer = icon.closest('.plus-parent, .plus-group, .plus-container');
                if (!iconContainer) return;
                var card = iconContainer.closest('.amenity-card-transfer, .amenity-card-spa, .amenity-card-welcome, .amenity-card-photo, .amenity-card-drone');
                if (!card) return;
                var valueEl = findAmenityQuantityValueEl(card);
                if (!valueEl) return;
                var icons = iconContainer.querySelectorAll('.plus-icon');
                if (icons.length < 2) return;
                var minus = icons[0];
                var plus = icons[1];
                var isPlus = (icon === plus);
                e.stopPropagation();
                e.preventDefault();
                var t = (valueEl.textContent || '').trim();
                var n = parseInt(t, 10);
                if (isNaN(n) || n < 0) n = 0;
                if (isPlus) {
                    n = n + 1;
                } else {
                    n = Math.max(0, n - 1);
                }
                valueEl.textContent = String(n);
                if (n > 0) {
                    card.classList.add('amenity-selected');
                } else {
                    card.classList.remove('amenity-selected');
                }
                updateSummaryAmenities();
            });
            var cards = section.querySelectorAll('.amenity-card-transfer, .amenity-card-spa, .amenity-card-welcome, .amenity-card-photo, .amenity-card-drone');
            cards.forEach(function (card) {
                var valueEl = findAmenityQuantityValueEl(card);
                if (!valueEl) return;
                var t = (valueEl.textContent || '').trim();
                var n = parseInt(t, 10);
                if (isNaN(n) || n < 0) n = 0;
                if (n > 0) {
                    card.classList.add('amenity-selected');
                } else {
                    card.classList.remove('amenity-selected');
                }
            });
            updateSummaryAmenities();
        }

        function loadIfNeeded(itineraryId) {
            var itineraryIdNum = itineraryId != null && itineraryId !== '' ? String(itineraryId) : null;
            if (hasLoaded && lastItineraryId === itineraryIdNum) {
                setVisible(services.length > 0);
                return;
            }
            hasLoaded = true;
            lastItineraryId = itineraryIdNum;

            var params = 'lang=' + encodeURIComponent(lang);
            if (itineraryIdNum) {
                params += '&itinerary_id=' + encodeURIComponent(itineraryIdNum);
            }
            fetch(apiUrl + '?' + params, {
                headers: { 'Accept': 'application/json' }
            }).then(function (r) { return r.json(); }).then(function (data) {
                if (!data || !data.success || !Array.isArray(data.items)) {
                    setVisible(false);
                    return;
                }
                services = data.items || [];
                render();
            }).catch(function () {
                setVisible(false);
            });
        }

        setVisible(false);

        document.addEventListener('booking:itinerarySelected', function (ev) {
            var itineraryId = ev && ev.detail && (ev.detail.itinerary_id != null) ? ev.detail.itinerary_id : (page.getAttribute('data-itinerary-id') || '').split('-')[0] || null;
            loadIfNeeded(itineraryId);
        });
    })();

    (function initPrefillFromCabinOrCruise() {
        var page = document.getElementById('booking-page');
        if (!page) return;

        var lang = page.getAttribute('data-lang') || 'vi';
        var cabinId = page.getAttribute('data-init-cabin-id') || '';
        var cruiseId = page.getAttribute('data-init-cruise-id') || '';
        var url = null;

        if (cabinId) {
            url = '/api/booking/suggest-by-cabin?cabin_id=' + encodeURIComponent(cabinId) + '&lang=' + encodeURIComponent(lang);
        } else if (cruiseId) {
            url = '/api/booking/suggest-by-cruise?cruise_id=' + encodeURIComponent(cruiseId) + '&lang=' + encodeURIComponent(lang);
        }

        if (!url) return;

        fetch(url, {
            headers: { 'Accept': 'application/json' }
        }).then(function (r) {
            return r.json();
        }).then(function (data) {
            if (!data || !data.success || !data.date) {
                return;
            }

            var dateInput = document.getElementById('booking-departure-date');
            if (dateInput) {
                dateInput.value = convertYmdToDdMmYyyy(data.date) || data.date;
                try {
                    var ev = new Event('change', { bubbles: true });
                    dateInput.dispatchEvent(ev);
                } catch (e) {
                    var evLegacy = document.createEvent('Event');
                    evLegacy.initEvent('change', true, true);
                    dateInput.dispatchEvent(evLegacy);
                }
            }

            if (data.itinerary_id) {
                page.setAttribute('data-init-itinerary-id', String(data.itinerary_id));
            }
            if (data.cruise_id) {
                page.setAttribute('data-init-cruise-id', String(data.cruise_id));
            }

            try {
                var ev2 = new CustomEvent('booking:initVoyageFromCabin');
                document.dispatchEvent(ev2);
            } catch (e3) {
                var evLegacy2 = document.createEvent('Event');
                evLegacy2.initEvent('booking:initVoyageFromCabin', true, true);
                document.dispatchEvent(evLegacy2);
            }
        }).catch(function () {
        });
    })();

    (function initCabinCards() {
        var page = document.getElementById('booking-page');
        var apiUrl = (page && page.getAttribute('data-api-cabins')) || '/api/booking/cabins';
        var lang = page ? (page.getAttribute('data-lang') || 'vi') : 'vi';

        var cabinStepSection = document.querySelector('#booking-page .cabin-step-section');
        var leftCard = document.querySelector('#booking-page .cabin-card-left .cabin-card-content');
        var rightCard = document.querySelector('#booking-page .cabin-card-right .cabin-card-content');
        var leftWrapper = document.querySelector('#booking-page .cabin-card-left');
        var rightWrapper = document.querySelector('#booking-page .cabin-card-right');
        var leftImage = document.querySelector('#booking-page .cabin-card-left .cabin-card-image-left');
        var rightImage = document.querySelector('#booking-page .cabin-card-right .cabin-card-image-right');
        var prevArrow = document.querySelector('#booking-page .cabin-card-left .chevron-left');
        var nextArrows = document.querySelectorAll('#booking-page .cabin-card-right .chevron-left-icon, #booking-page .cabin-card-left .cabin-nav-next-mobile');

        if (!apiUrl || !leftCard) {
            return;
        }

        var cabins = [];
        var currentIndex = 0;
        var currentCruiseId = null;
        var initCabinId = page ? (page.getAttribute('data-init-cabin-id') || '') : '';

        function formatDescription(cabin) {
            var parts = [];
            if (cabin.capacity && cabin.capacity > 0) {
                var guestLabel = (window.__bookingLocale === 'vi') ? 'Tối đa ' + cabin.capacity + ' khách' : 'Max ' + cabin.capacity + ' Guests';
                parts.push(guestLabel);
            }
            if (cabin.view) {
                parts.push(cabin.view);
            }
            if (cabin.area && cabin.area > 0) {
                var areaLabel = (window.__bookingLocale === 'vi') 
                    ? 'Diện tích phòng ' + cabin.area + 'm²'
                    : 'room size ' + cabin.area + 'm²';
                parts.push(areaLabel);
            }
            if (parts.length === 0 && cabin.summary) {
                return cabin.summary;
            }
            return parts.join(', ');
        }

        function formatCabinPrice(value) {
            return window.__bookingFormatAmount ? window.__bookingFormatAmount(value) : ('$' + (Number(value) || 0).toLocaleString(undefined, { maximumFractionDigits: 0 }));
        }

        function renderSlot(cardEl, imageEl, cabin) {
            if (!cardEl || !cabin) return;

            var titleEl = cardEl.querySelector('.cabin-card-title');
            var titleTextEl = titleEl ? titleEl.querySelector('.cabin-card-title-text') : null;
            var descEl = cardEl.querySelector('.max-2-guests-01-queen-sized');
            var priceEl = cardEl.querySelector('.price-amount');
            var addBtn = cardEl.querySelector('.add-cabin-btn');
            var viewBtn = cardEl.querySelector('.btn-view-cabin-details');

            if (titleTextEl) {
                titleTextEl.textContent = cabin.name || 'Cabin';
                var titleBox = titleEl;
                setTimeout(function() {
                    if (titleBox && titleBox.scrollWidth > titleBox.clientWidth) {
                        var scrollDistance = titleBox.scrollWidth - titleBox.clientWidth;
                        titleBox.style.setProperty('--scroll-distance', '-' + scrollDistance + 'px');
                        titleBox.classList.add('cabin-card-title--scrollable');
                    } else {
                        titleBox.classList.remove('cabin-card-title--scrollable');
                        titleBox.style.removeProperty('--scroll-distance');
                    }
                }, 50);
            }
            if (descEl) {
                var desc = formatDescription(cabin);
                var descTextEl = descEl.querySelector('.max-2-guests-01-queen-sized-text');
                var descOneLine = (desc || '').replace(/\n/g, ' ').trim();
                if (descTextEl) {
                    descTextEl.textContent = descOneLine;
                    function applyDescScrollable() {
                        if (!descTextEl || !descEl) return;
                        var containerWidth = descEl.clientWidth;
                        if (containerWidth <= 0) return;
                        var origMax = descTextEl.style.maxWidth;
                        descTextEl.style.maxWidth = 'none';
                        descTextEl.style.display = 'inline-block';
                        var fullWidth = descTextEl.offsetWidth;
                        descTextEl.style.maxWidth = origMax;
                        descTextEl.style.display = '';
                        if (fullWidth > containerWidth) {
                            var scrollDistance = fullWidth - containerWidth;
                            var distPx = '-' + scrollDistance + 'px';
                            descEl.style.setProperty('--scroll-distance', distPx);
                            descTextEl.style.setProperty('--scroll-distance', distPx);
                            descEl.classList.add('max-2-guests-01-queen-sized--scrollable');
                        } else {
                            descEl.classList.remove('max-2-guests-01-queen-sized--scrollable');
                            descEl.style.removeProperty('--scroll-distance');
                            descTextEl.style.removeProperty('--scroll-distance');
                        }
                    }
                    setTimeout(applyDescScrollable, 100);
                    if (typeof requestAnimationFrame !== 'undefined') {
                        requestAnimationFrame(function() { requestAnimationFrame(applyDescScrollable); });
                    }
                } else {
                    descEl.innerHTML = (desc || '').replace(/\n/g, '<br>');
                }
            }
            if (priceEl) {
                if (cabin.min_price && cabin.min_price > 0) {
                    priceEl.textContent = formatCabinPrice(cabin.min_price);
                } else {
                    priceEl.textContent = formatCabinPrice(0);
                }
            }
            if (addBtn) {
                addBtn.setAttribute('data-cabin-name', cabin.name || 'Cabin');
                var priceVal = cabin.min_price && cabin.min_price > 0 ? cabin.min_price : 0;
                addBtn.setAttribute('data-cabin-price', priceVal);
                addBtn.setAttribute('data-cabin-description', formatDescription(cabin));
                addBtn.setAttribute('data-cabin-image', cabin.image_url || '');
                addBtn.setAttribute('data-cabin-capacity', cabin.capacity || 2);
                if (cabin.id != null && cabin.id !== '') {
                    addBtn.setAttribute('data-cabin-id', cabin.id);
                } else {
                    addBtn.removeAttribute('data-cabin-id');
                }
            }
            if (viewBtn) {
                if (cabin.id != null) {
                    viewBtn.setAttribute('data-id', cabin.id);
                } else {
                    viewBtn.removeAttribute('data-id');
                }
            }
            if (imageEl) {
                if (cabin.image_url) {
                    try {
                        imageEl.style.backgroundImage = 'url(' + cabin.image_url + ')';
                        imageEl.style.backgroundSize = 'cover';
                        imageEl.style.backgroundPosition = 'center center';
                        imageEl.style.backgroundRepeat = 'no-repeat';
                    } catch (e) {
                    }
                } else {
                    imageEl.style.backgroundImage = '';
                }
            }
        }

        function syncCabinCardHeights() {
            if (!leftCard) return;

            leftCard.style.minHeight = '';
            if (rightCard) rightCard.style.minHeight = '';

            var maxH = leftCard.offsetHeight || 0;
            if (rightCard && rightWrapper && rightWrapper.style.display !== 'none') {
                var rh = rightCard.offsetHeight || 0;
                if (rh > maxH) maxH = rh;
            }

            if (maxH > 0) {
                leftCard.style.minHeight = maxH + 'px';
                if (rightCard) rightCard.style.minHeight = maxH + 'px';
            }
        }

        function updateCabinNavState() {
            if (!leftWrapper || !rightWrapper || !cabins.length) return;
            var atFirst = currentIndex === 0;
            var atLast = cabins.length <= 1 || currentIndex === cabins.length - 1;
            if (atFirst) {
                leftWrapper.classList.add('cabin-nav-at-start');
            } else {
                leftWrapper.classList.remove('cabin-nav-at-start');
            }
            if (atLast) {
                rightWrapper.classList.add('cabin-nav-at-end');
                leftWrapper.classList.add('cabin-nav-at-end');
            } else {
                rightWrapper.classList.remove('cabin-nav-at-end');
                leftWrapper.classList.remove('cabin-nav-at-end');
            }
        }

        function renderCabins() {
            if (!cabins.length) return;

            var leftCabin = cabins[currentIndex];
            var rightCabin = null;

            if (cabins.length > 1) {
                var rightIndex = (currentIndex + 1) % cabins.length;
                rightCabin = cabins[rightIndex];
            }

            if (leftWrapper) {
                leftWrapper.style.display = '';
            }
            renderSlot(leftCard, leftImage, leftCabin);

            if (rightWrapper) {
                if (rightCabin) {
                    rightWrapper.style.display = '';
                    renderSlot(rightCard, rightImage, rightCabin);
                } else {
                    rightWrapper.style.display = 'none';
                }
            }

            updateCabinNavState();
            syncCabinCardHeights();
            setTimeout(refreshCabinDescScrollable, 350);
        }

        function refreshCabinDescScrollable() {
            var page = document.getElementById('booking-page');
            if (!page) return;
            var descs = page.querySelectorAll('.max-2-guests-01-queen-sized .max-2-guests-01-queen-sized-text');
            descs.forEach(function (descTextEl) {
                var descEl = descTextEl && descTextEl.parentElement;
                if (!descEl || descEl.className.indexOf('max-2-guests-01-queen-sized') === -1) return;
                var containerWidth = descEl.clientWidth;
                if (containerWidth <= 0) return;
                var origMax = descTextEl.style.maxWidth;
                var origDisplay = descTextEl.style.display;
                descTextEl.style.maxWidth = 'none';
                descTextEl.style.display = 'inline-block';
                var fullWidth = descTextEl.offsetWidth;
                descTextEl.style.maxWidth = origMax;
                descTextEl.style.display = origDisplay || '';
                if (fullWidth > containerWidth) {
                    var scrollDistance = fullWidth - containerWidth;
                    var distPx = '-' + scrollDistance + 'px';
                    descEl.style.setProperty('--scroll-distance', distPx);
                    descTextEl.style.setProperty('--scroll-distance', distPx);
                    descEl.classList.add('max-2-guests-01-queen-sized--scrollable');
                } else {
                    descEl.classList.remove('max-2-guests-01-queen-sized--scrollable');
                    descEl.style.removeProperty('--scroll-distance');
                    descTextEl.style.removeProperty('--scroll-distance');
                }
            });
        }

        function goNext() {
            if (cabins.length <= 1) return;
            currentIndex = (currentIndex + 1) % cabins.length;
            renderCabins();
        }

        function goPrev() {
            if (cabins.length <= 1) return;
            currentIndex = (currentIndex - 1 + cabins.length) % cabins.length;
            renderCabins();
        }
        function resetCabins() {
            cabins = [];
            currentIndex = 0;
            currentCruiseId = null;
            if (leftWrapper) leftWrapper.style.display = 'none';
            if (rightWrapper) rightWrapper.style.display = 'none';
            if (cabinStepSection) cabinStepSection.style.display = 'none';
        }

        function loadCabinsForCruise(cruiseId, duration) {
            if (!apiUrl || !cruiseId) {
                resetCabins();
                return;
            }
            currentCruiseId = String(cruiseId);
            var params = 'lang=' + encodeURIComponent(lang) + '&cruise_id=' + encodeURIComponent(cruiseId);
            if (duration && duration > 0) {
                params += '&duration=' + encodeURIComponent(duration);
            }
            fetch(apiUrl + '?' + params, {
                headers: { 'Accept': 'application/json' }
            }).then(function (r) { return r.json(); }).then(function (data) {
                if (!data || !data.success || !Array.isArray(data.items)) {
                    resetCabins();
                    return;
                }
                cabins = data.items || [];
                if (!cabins.length) {
                    resetCabins();
                    return;
                }

                currentIndex = 0;
                if (initCabinId) {
                    for (var i = 0; i < cabins.length; i++) {
                        if (String(cabins[i].id) === String(initCabinId)) {
                            currentIndex = i;
                            break;
                        }
                    }
                }
                if (cabinStepSection) cabinStepSection.style.display = '';
                renderCabins();

                if (initCabinId && cabins.length) {
                    var targetCabin = cabins.find(function (c) { return String(c.id) === String(initCabinId); }) || cabins[0];
                    if (targetCabin) {
                        addedCabinIdCounter += 1;
                        addedCabinsList.push({
                            id: addedCabinIdCounter,
                            cabinId: targetCabin.id != null && targetCabin.id !== '' ? targetCabin.id : null,
                            name: targetCabin.name || 'Cabin',
                            price: targetCabin.min_price || 0,
                            description: formatDescription(targetCabin),
                            imageUrl: targetCabin.image_url || ''
                        });
                        renderInVoyageCabins();
                    }
                }

                if (prevArrow && cabins.length >= 2) {
                    prevArrow.addEventListener('click', function (e) {
                        e.stopPropagation();
                        if (currentIndex === 0) return;
                        goPrev();
                    });
                }
                if (nextArrows && nextArrows.length && cabins.length >= 2) {
                    nextArrows.forEach(function (nextBtn) {
                        nextBtn.addEventListener('click', function (e) {
                            e.stopPropagation();
                            if (currentIndex === cabins.length - 1) return;
                            goNext();
                        });
                    });
                }
            }).catch(function () {
                resetCabins();
            });
        }

        resetCabins();

        document.addEventListener('booking:itinerarySelected', function (e) {
            var detail = e && e.detail ? e.detail : null;
            var cruiseId = detail && detail.cruise_id ? detail.cruise_id : null;
            var duration = detail && detail.duration ? detail.duration : null;
            if (!cruiseId) {
                resetCabins();
                return;
            }
            loadCabinsForCruise(cruiseId, duration);
        });

        document.addEventListener('booking:dateSelected', function () {
            resetCabins();
        });
    })();

    var addCabinButtons = document.querySelectorAll('.add-cabin-btn');
    var inVoyageTitle = document.getElementById('in-voyage-title');
    var inVoyageEmptyState = document.getElementById('in-voyage-empty-state');
    var inVoyageCabinsList = document.getElementById('in-voyage-cabins-list');
    var inVoyageSection = document.getElementById('in-voyage-section');
    var summaryCabinsBlock = document.getElementById('summary-cabins-block');
    var summaryCabinsList = document.getElementById('summary-cabins-list');
    var addedCabinsList = [];
    var addedCabinIdCounter = 0;

    function formatCabinPriceGlobal(value) {
        return window.__bookingFormatAmount ? window.__bookingFormatAmount(value) : ('$' + (Number(value) || 0).toLocaleString(undefined, { maximumFractionDigits: 0 }));
    }

    function formatDateDisplayFromYmd(dateStr) {
        var defaultFormat = 'dd/MM/yyyy';
        if (!dateStr) return defaultFormat;
        var d = new Date(dateStr + 'T00:00:00');
        if (isNaN(d.getTime())) return defaultFormat;
        var m = (d.getMonth() + 1).toString().padStart(2, '0');
        var day = d.getDate().toString().padStart(2, '0');
        var y = d.getFullYear();
        return day + '/' + m + '/' + y;
    }

    function convertDdMmYyyyToYmd(dateStr) {
        if (!dateStr || typeof dateStr !== 'string') return null;
        var parts = dateStr.split('/');
        if (parts.length === 3) {
            return parts[2] + '-' + parts[1] + '-' + parts[0];
        }
        return dateStr;
    }

    function convertYmdToDdMmYyyy(dateStr) {
        if (!dateStr || typeof dateStr !== 'string') return null;
        if (dateStr.indexOf('/') !== -1) return dateStr;
        var parts = dateStr.split('-');
        if (parts.length === 3) {
            return parts[2] + '/' + parts[1] + '/' + parts[0];
        }
        return dateStr;
    }

    function updateEstimatedTotalFromSummary() {
        var totalEl = document.querySelector('#booking-page .total-amount');
        if (!totalEl) return;

        var page = document.getElementById('booking-page');
        var lang = page ? (page.getAttribute('data-lang') || 'en') : 'en';
        var total = 0;

        document.querySelectorAll('#summary-cabins-list .summary-price-value').forEach(function (priceEl) {
            if (!priceEl || !priceEl.textContent) return;
            var text = priceEl.textContent.trim();
            var raw;
            var v;
            if ((lang || '').toLowerCase() === 'vi') {
                raw = text.replace(/[^\d]/g, '');
                if (!raw) return;
                v = parseInt(raw, 10);
            } else {
                raw = text.replace(/[^\d.]/g, '');
                if (!raw) return;
                v = parseFloat(raw);
            }
            if (!isNaN(v) && v > 0) {
                total += v;
            }
        });

        document.querySelectorAll('#booking-page .summary-amenities-list .summary-price-value').forEach(function (priceEl) {
            if (!priceEl || !priceEl.textContent) return;
            var text = priceEl.textContent.trim();
            var raw;
            var v;
            if ((lang || '').toLowerCase() === 'vi') {
                raw = text.replace(/[^\d]/g, '');
                if (!raw) return;
                v = parseInt(raw, 10);
            } else {
                raw = text.replace(/[^\d.]/g, '');
                if (!raw) return;
                v = parseFloat(raw);
            }
            if (!isNaN(v) && v > 0) {
                total += v;
            }
        });

        if (typeof window !== 'undefined') {
            window.__bookingRawTotal = total;
        }

        totalEl.textContent = formatCabinPriceGlobal(total);
    }


    updateEstimatedTotalFromSummary();
    window.__updateEstimatedTotal = updateEstimatedTotalFromSummary;

    function updateSummaryGuestsFromCabins() {
        var guestsInput = document.querySelector('#booking-page .summary-guests-input');
        if (!guestsInput) return;
        var cards = document.querySelectorAll('#in-voyage-cabins-list .in-voyage-cabin-card');
        var total = 0;
        cards.forEach(function (card) {
            card.querySelectorAll('.in-voyage-qty-control input').forEach(function (input) {
                total += parseInt(input.value, 10) || 0;
            });
        });
        var page = document.getElementById('booking-page');
        var lang = page ? (page.getAttribute('data-lang') || 'en') : 'en';
        if (total === 0) {
            guestsInput.value = '';
        } else {
            if (window.__bookingLocale === 'vi') {
                guestsInput.value = total + ' khách';
            } else {
                guestsInput.value = total + ' ' + (total === 1 ? 'person' : 'people');
            }
        }
    }

    function renderInVoyageCabins() {
        if (!inVoyageTitle || !inVoyageEmptyState || !inVoyageCabinsList) return;

        var n = addedCabinsList.length;
        var titleText = (window.__bookingLocale === 'vi') 
            ? 'Cabin trong hành trình (' + n + ')'
            : 'In-Voyage Cabins (' + n + ')';
        inVoyageTitle.textContent = titleText;

        if (summaryCabinsBlock) {
            summaryCabinsBlock.style.display = n > 0 ? '' : 'none';
        }

        if (n === 0) {
            inVoyageEmptyState.style.display = 'flex';
            inVoyageCabinsList.style.display = 'none';
            inVoyageCabinsList.innerHTML = '';
            if (summaryCabinsList) {
                summaryCabinsList.innerHTML = '';
            }
            updateSummaryGuestsFromCabins();
            updateEstimatedTotalFromSummary();
            return;
        }

        var existingGuestsData = {};
        var existingCards = inVoyageCabinsList.querySelectorAll('.in-voyage-cabin-card');
        existingCards.forEach(function (card) {
            var cabinId = card.getAttribute('data-cabin-id');
            if (!cabinId) return;
            var guestFields = card.querySelectorAll('.in-voyage-guest-field');
            var guests = [];
            guestFields.forEach(function (field) {
                var input = field.querySelector('input');
                if (input) {
                    guests.push(parseInt(input.value, 10) || 0);
                }
            });
            existingGuestsData[cabinId] = guests;
        });

        inVoyageEmptyState.style.display = 'none';
        inVoyageCabinsList.style.display = 'flex';
        inVoyageCabinsList.innerHTML = '';
        if (summaryCabinsList) {
            summaryCabinsList.innerHTML = '';
        }

        addedCabinsList.forEach(function (item) {
            var card = document.createElement('div');
            card.className = 'in-voyage-cabin-card';
            if (item.cabinId != null && item.cabinId !== '') {
                card.setAttribute('data-cabin-id', item.cabinId);
            }

            var thumbStyle = item.imageUrl ? 'background-image:url(' + item.imageUrl + ')' : '';
            var descText = (item.description || '').replace(/<br\s*\/?>/gi, ', ');
            
            var guestsData = item.cabinId && existingGuestsData[item.cabinId] ? existingGuestsData[item.cabinId] : [0, 0, 0, 0];

            card.innerHTML =
                '<div class="in-voyage-cabin-card-inner">' +
                '  <div class="in-voyage-cabin-info">' +
                '    <div class="in-voyage-cabin-thumb" style="' + thumbStyle + '"></div>' +
                '    <div class="in-voyage-cabin-details">' +
                '      <h4 class="in-voyage-cabin-name">' + (item.name || 'Cabin').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</h4>' +
                '      <p class="in-voyage-cabin-desc">' + (descText || '-').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</p>' +
                '      <p class="in-voyage-cabin-fare">' + (window.__bookingLocale === 'vi' ? 'Giá đã điều chỉnh: ' : 'Adjusted Fare: ') + '<strong>' + formatCabinPriceGlobal(item.price) + '</strong></p>' +
                '      <button type="button" class="in-voyage-remove-btn" data-remove-id="' + item.id + '">' + (window.__bookingLocale === 'vi' ? 'Xóa' : 'Remove') + ' <i class="fa-solid fa-trash"></i></button>' +
                '    </div>' +
                '  </div>' +
                '  <div class="in-voyage-guests">' +
                '    <div class="in-voyage-guest-field"><label>' + (window.__bookingLocale === 'vi' ? 'Người lớn' : 'Adults') + '</label><div class="in-voyage-qty-control"><button type="button" class="qty-minus">−</button><input type="text" value="' + guestsData[0] + '" readonly><button type="button" class="qty-plus">+</button></div></div>' +
                '    <div class="in-voyage-guest-field"><label>' + (window.__bookingLocale === 'vi' ? '6-12 tuổi' : '6-12y') + '</label><div class="in-voyage-qty-control"><button type="button" class="qty-minus">−</button><input type="text" value="' + guestsData[1] + '" readonly><button type="button" class="qty-plus">+</button></div></div>' +
                '    <div class="in-voyage-guest-field"><label>' + (window.__bookingLocale === 'vi' ? '2-5 tuổi' : '2-5y') + '</label><div class="in-voyage-qty-control"><button type="button" class="qty-minus">−</button><input type="text" value="' + guestsData[2] + '" readonly><button type="button" class="qty-plus">+</button></div></div>' +
                '    <div class="in-voyage-guest-field"><label>' + (window.__bookingLocale === 'vi' ? 'Em bé' : 'Infant') + '</label><div class="in-voyage-qty-control"><button type="button" class="qty-minus">−</button><input type="text" value="' + guestsData[3] + '" readonly><button type="button" class="qty-plus">+</button></div></div>' +
                '  </div>' +
                '</div>';

            inVoyageCabinsList.appendChild(card);

            var removeBtn = card.querySelector('.in-voyage-remove-btn');
            if (removeBtn) {
                removeBtn.addEventListener('click', function () {
                    var id = removeBtn.getAttribute('data-remove-id');
                    if (!id) return;
                    for (var i = 0; i < addedCabinsList.length; i++) {
                        if (String(addedCabinsList[i].id) === String(id)) {
                            addedCabinsList.splice(i, 1);
                            break;
                        }
                    }
                    renderInVoyageCabins();
                });
            }

            card.querySelectorAll('.in-voyage-qty-control').forEach(function (ctrl) {
                var input = ctrl.querySelector('input');
                var minus = ctrl.querySelector('.qty-minus');
                var plus = ctrl.querySelector('.qty-plus');
                if (!input || !minus || !plus) return;
                function notifyQtyChange() {
                    try {
                        document.dispatchEvent(new CustomEvent('booking:cabinQuantityChanged'));
                    } catch (e) {}
                }
                minus.addEventListener('click', function () {
                    var v = parseInt(input.value, 10) || 0;
                    if (v > 0) input.value = String(v - 1);
                    updateSummaryGuestsFromCabins();
                    notifyQtyChange();
                });
                plus.addEventListener('click', function () {
                    var v = parseInt(input.value, 10) || 0;
                    input.value = String(v + 1);
                    updateSummaryGuestsFromCabins();
                    notifyQtyChange();
                });
            });
        });

        updateSummaryGuestsFromCabins();
        try {
            document.dispatchEvent(new CustomEvent('booking:cabinsUpdated'));
        } catch (e) {}
        
        setTimeout(function() {
            if (typeof window.__cabinPriceRecalculate === 'function') {
                window.__cabinPriceRecalculate();
            }
        }, 100);
    }

    function resetBookingSelections() {
        addedCabinsList.length = 0;
        renderInVoyageCabins();

        var amenityCards = document.querySelectorAll('#booking-page .amenity-card-transfer, #booking-page .amenity-card-spa, #booking-page .amenity-card-welcome, #booking-page .amenity-card-photo, #booking-page .amenity-card-drone');
        amenityCards.forEach(function (card) {
            var valueEl = card.querySelector('.plus-parent .mmddyyyy, .plus-group .mmddyyyy, .plus-container .mmddyyyy');
            if (valueEl) valueEl.textContent = '0';
            card.classList.remove('amenity-selected');
        });

        var summaryAmenitiesList = document.querySelector('#booking-page .summary-amenities-list');
        var summaryAmenitiesBlock = document.getElementById('summary-amenities-block');
        if (summaryAmenitiesList) summaryAmenitiesList.innerHTML = '';
        if (summaryAmenitiesBlock) summaryAmenitiesBlock.style.display = 'none';

        var amenitiesStepSection = document.querySelector('#booking-page .amenities-step-section');
        if (amenitiesStepSection) amenitiesStepSection.style.display = 'none';

        var itineraryCard = document.getElementById('booking-itinerary-card');
        var itineraryCardTitle = document.getElementById('itinerary-card-title');
        var itineraryCardCruise = document.getElementById('itinerary-card-cruise');
        var itineraryCardDuration = document.getElementById('itinerary-card-duration');
        var itineraryCardDestination = document.getElementById('itinerary-card-destination');
        var summaryVoyageTitle = document.getElementById('summary-voyage-title');
        var summaryVoyageCruise = document.getElementById('summary-voyage-cruise');
        var placeholder = '—';
        if (itineraryCard) itineraryCard.style.display = 'none';
        if (itineraryCardTitle) itineraryCardTitle.textContent = placeholder;
        if (itineraryCardCruise) itineraryCardCruise.textContent = placeholder;
        if (itineraryCardDuration) itineraryCardDuration.textContent = placeholder;
        if (itineraryCardDestination) itineraryCardDestination.textContent = placeholder;
        if (summaryVoyageTitle) summaryVoyageTitle.textContent = placeholder;
        if (summaryVoyageCruise) summaryVoyageCruise.textContent = placeholder;
        var inclusionsList = document.getElementById('itinerary-card-inclusions-list');
        if (inclusionsList) inclusionsList.innerHTML = '';
        var timelineLine = document.getElementById('itinerary-card-timeline-line');
        var timelineLabels = document.getElementById('itinerary-card-timeline-labels');
        if (timelineLine) timelineLine.innerHTML = '';
        if (timelineLabels) timelineLabels.innerHTML = '';

        updateEstimatedTotalFromSummary();
    }

    function resetSelectionsForItineraryChange() {
        addedCabinsList.length = 0;
        renderInVoyageCabins();

        var amenityCards = document.querySelectorAll('#booking-page .amenity-card-transfer, #booking-page .amenity-card-spa, #booking-page .amenity-card-welcome, #booking-page .amenity-card-photo, #booking-page .amenity-card-drone');
        amenityCards.forEach(function (card) {
            var valueEl = card.querySelector('.plus-parent .mmddyyyy, .plus-group .mmddyyyy, .plus-container .mmddyyyy');
            if (valueEl) valueEl.textContent = '0';
            card.classList.remove('amenity-selected');
        });

        var summaryAmenitiesList = document.querySelector('#booking-page .summary-amenities-list');
        var summaryAmenitiesBlock = document.getElementById('summary-amenities-block');
        if (summaryAmenitiesList) summaryAmenitiesList.innerHTML = '';
        if (summaryAmenitiesBlock) summaryAmenitiesBlock.style.display = 'none';

        updateEstimatedTotalFromSummary();
    }

    document.addEventListener('booking:dateSelected', resetBookingSelections);
    document.addEventListener('booking:itinerarySelected', resetSelectionsForItineraryChange);

    addCabinButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var cabinName = btn.getAttribute('data-cabin-name') || 'Cabin';
            var cabinPrice = btn.getAttribute('data-cabin-price') || '0';
            var cabinDescription = btn.getAttribute('data-cabin-description') || '';
            var cabinImage = btn.getAttribute('data-cabin-image') || '';
            var realCabinId = btn.getAttribute('data-cabin-id');
            if (realCabinId) realCabinId = parseInt(realCabinId, 10) || null;

            addedCabinIdCounter += 1;
            addedCabinsList.push({
                id: addedCabinIdCounter,
                cabinId: realCabinId,
                name: cabinName,
                price: cabinPrice,
                description: cabinDescription,
                imageUrl: cabinImage
            });

            renderInVoyageCabins();

            if (inVoyageSection) {
                inVoyageSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else if (summaryCabinsBlock) {
                summaryCabinsBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    var button4 = document.getElementById('button4');
    var bookingStepsPanel = document.getElementById('booking-steps-panel');
    var guestDetailsPanel = document.getElementById('guest-details-panel');
    var paymentPanel = document.getElementById('payment-panel');
    var completePanel = document.getElementById('complete-panel');
    var guestBackButton = document.getElementById('guest-back-button');
    var guestContinueButton = document.getElementById('guest-continue-button');
    var paymentBackButton = document.getElementById('payment-back-button');
    var progressGuestDetails = document.getElementById('progress-step-guest-details');
    var progressPayment = document.getElementById('progress-step-payment');
    var progressDivider1 = document.getElementById('progress-divider-1');
    var progressDividerMid = document.getElementById('progress-divider-mid');
    var progressDivider2 = document.getElementById('progress-divider-2');
    var progressComplete = document.getElementById('progress-step-complete');
    var progressSection = document.querySelector('#booking-page .booking-progress-section');
    var progressMobileTitle = document.getElementById('progress-mobile-title');
    var progressMobileIcon = document.getElementById('progress-mobile-icon');

    var mobileStepIcons = ['fas fa-pen', 'fa-solid fa-user', 'fa-solid fa-credit-card', 'fa-solid fa-check-double'];

    function updateMobileProgressStep(stepNum) {
        if (!progressSection || !progressMobileTitle || !progressMobileIcon) return;
        var label = progressSection.getAttribute('data-step-' + stepNum);
        if (label) progressMobileTitle.textContent = label;
        if (mobileStepIcons[stepNum - 1]) {
            progressMobileIcon.className = mobileStepIcons[stepNum - 1];
        }
    }

    updateMobileProgressStep(1);

    var paymentMethodButtons = document.querySelectorAll('#booking-page .payment-body-methods .payment-option');
    var estimatedTotalButton = document.querySelector('#booking-page .estimated-total-button');
    var paymentContinueButton = document.getElementById('payment-continue-button');
    var paymentLoadingOverlay = document.getElementById('payment-loading');
    var selectedPaymentMethod = null;
    var isInquiryMode = false;
    var paymentBodyMethods = document.getElementById('payment-body-methods');
    var paymentInquiryMessage = document.getElementById('payment-inquiry-message');
    var confirmationTypeButtons = document.querySelectorAll('#payment-panel .payment-row-top .payment-option');

    function getQuery() {
        var o = {};
        var q = window.location.search.slice(1);
        if (!q) return o;
        q.split('&').forEach(function (p) {
            if (!p) return;
            var i = p.indexOf('=');
            if (i > 0) {
                o[decodeURIComponent(p.slice(0, i))] = decodeURIComponent(p.slice(i + 1));
            }
        });
        return o;
    }

    function showCompletePanel(statusType) {
        if (paymentLoadingOverlay) {
            paymentLoadingOverlay.classList.remove('is-visible');
        }
        if (bookingStepsPanel) {
            bookingStepsPanel.style.display = 'none';
        }
        if (guestDetailsPanel) {
            guestDetailsPanel.classList.remove('is-visible');
        }
        if (paymentPanel) {
            paymentPanel.classList.remove('is-visible');
        }
        if (completePanel) {
            completePanel.classList.add('is-visible');
            completePanel.classList.remove('complete-column--pending', 'complete-column--error');
            if (statusType === 'pending') {
                completePanel.classList.add('complete-column--pending');
            } else if (statusType === 'error') {
                completePanel.classList.add('complete-column--error');
            }
        }

        if (progressGuestDetails) {
            progressGuestDetails.classList.add('booking-progress-active');
        }
        if (progressPayment) {
            progressPayment.classList.add('booking-progress-active');
        }
        if (progressDivider1) {
            progressDivider1.classList.add('booking-progress-active');
        }
        if (progressDividerMid) {
            progressDividerMid.classList.add('booking-progress-active');
        }
        if (progressDivider2) {
            progressDivider2.classList.add('booking-progress-active');
        }
        if (progressComplete) {
            progressComplete.classList.add('booking-progress-active');
        }
        updateMobileProgressStep(4);

        var titleEl = document.getElementById('complete-title');
        var messageEl = document.getElementById('complete-message');
        var iconWrapper = document.getElementById('complete-icon');
        var iconEl = iconWrapper ? iconWrapper.querySelector('i') : null;
        var page = document.getElementById('booking-page');
        var getMsg = function (key) { return (page && page.getAttribute(key)) || ''; };

        if (statusType === 'success') {
            if (titleEl) titleEl.textContent = getMsg('data-complete-success-title');
            if (messageEl) messageEl.textContent = getMsg('data-complete-success-message');
            if (iconEl) iconEl.className = 'fa-solid fa-check';
        } else if (statusType === 'pending') {
            if (titleEl) titleEl.textContent = getMsg('data-complete-pending-title');
            if (messageEl) messageEl.textContent = getMsg('data-complete-pending-message');
            if (iconEl) iconEl.className = 'fa-solid fa-hourglass-half';
        } else {
            if (titleEl) titleEl.textContent = getMsg('data-complete-failed-title');
            if (messageEl) messageEl.textContent = getMsg('data-complete-failed-message');
            if (iconEl) iconEl.className = 'fa-solid fa-circle-exclamation';
        }
    }

    function pollPaymentStatus(internalTxId) {
        var maxAttempts = 30;
        var interval = 2000;
        var attempts = 0;

        function poll() {
            fetch('/api/payment/status?internal_tx_id=' + encodeURIComponent(internalTxId), {
                headers: { 'Accept': 'application/json' }
            }).then(function (r) { return r.json(); }).then(function (data) {
                attempts++;
                if (!data || !data.success || !data.status) {
                    if (attempts >= maxAttempts) {
                        showCompletePanel('error');
                        return;
                    }
                    setTimeout(poll, interval);
                    return;
                }

                var status = data.status;
                if (data.booking) {
                    applyBookingSummaryFromBooking(data.booking);
                }
                if (status === 'succeeded') {
                    showCompletePanel('success');
                    return;
                }
                if (status === 'failed' || status === 'canceled') {
                    showCompletePanel('error');
                    return;
                }

                if (attempts >= maxAttempts) {
                    showCompletePanel('pending');
                    return;
                }
                setTimeout(poll, interval);
            }).catch(function () {
                attempts++;
                if (attempts >= maxAttempts) {
                    showCompletePanel('error');
                    return;
                }
                setTimeout(poll, interval);
            });
        }

        poll();
    }

    (function initPaymentResultFromQuery() {
        var q = getQuery();
        if (!q || !q.result || !q.tx) {
            return;
        }

        // Clear any stored payment session when we return from gateway with an explicit result
        if (typeof window !== 'undefined' && window.sessionStorage) {
            try {
                window.sessionStorage.removeItem('bookingCurrentPaymentTx');
            } catch (e) { }
        }

        showCompletePanel('pending');

        var url;
        if (q.result === 'success') {
            pollPaymentStatus(q.tx);
            return;
        }

        url = '/api/payment/status?tx=' + encodeURIComponent(q.tx) + '&result=' + encodeURIComponent(q.result);

        fetch(url, {
            headers: { 'Accept': 'application/json' }
        }).then(function (r) { return r.json(); }).then(function (data) {
            if (!data || !data.success) {
                showCompletePanel('error');
                return;
            }
            var status = data.status || 'pending';
            if (data.booking) {
                applyBookingSummaryFromBooking(data.booking);
            }
            if (status === 'pending') {
                showCompletePanel('pending');
            } else if (status === 'succeeded') {
                showCompletePanel('success');
            } else {
                showCompletePanel('error');
            }
        }).catch(function () {
            showCompletePanel('error');
        });
    })();

    // If user navigates back from payment gateway (Back button / history),
    // treat it as a cancelled/failed payment and close the previous session.
    function cancelStalePaymentWhenBackFromGateway() {
        if (typeof window === 'undefined' || !window.sessionStorage) return;

        var storedTx = null;
        try {
            storedTx = window.sessionStorage.getItem('bookingCurrentPaymentTx');
        } catch (e) {
            storedTx = null;
        }
        if (!storedTx) return;

        // If URL already has a payment result, let the normal handler above take care of it
        var q = getQuery();
        if (q && q.result) {
            try {
                window.sessionStorage.removeItem('bookingCurrentPaymentTx');
            } catch (e) { }
            return;
        }

        fetch('/api/payment/status?internal_tx_id=' + encodeURIComponent(storedTx) + '&result=cancel', {
            headers: { 'Accept': 'application/json' }
        }).then(function () {
            showCompletePanel('error');
        }).catch(function () {
            showCompletePanel('error');
        }).then(function () {
            if (paymentLoadingOverlay) {
                paymentLoadingOverlay.classList.remove('is-visible');
            }
            try {
                window.sessionStorage.removeItem('bookingCurrentPaymentTx');
            } catch (e) { }
        });
    }

    // Chạy khi trang load mới
    cancelStalePaymentWhenBackFromGateway();
    // Và cả khi trang được restore từ bfcache (Back/Forward)
    if (typeof window !== 'undefined') {
        window.addEventListener('pageshow', function (event) {
            // persisted = true nghĩa là trang được restore từ cache khi bấm Back/Forward
            if (event.persisted) {
                cancelStalePaymentWhenBackFromGateway();
            }
        });
    }

    if (button4 && bookingStepsPanel && guestDetailsPanel) {
        button4.addEventListener('click', function () {
            if (typeof validateStep1BookingInfo === 'function') {
                var result = validateStep1BookingInfo();
                if (!result.valid) {
                    showStep1InlineErrors(result.fieldErrors);
                    var firstErr = document.querySelector('#booking-page .booking-field-error:not(:empty)');
                    if (firstErr) {
                        var scrollTarget = firstErr.closest('.label-check-in-parent, .label-check-in-group') || firstErr.closest('section') || firstErr;
                        scrollTarget.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return;
                }
            }
            clearBookingStep1Errors();
            bookingStepsPanel.style.display = 'none';
            guestDetailsPanel.classList.add('is-visible');

            if (progressGuestDetails) {
                progressGuestDetails.classList.add('booking-progress-active');
            }
            if (progressDivider1) {
                progressDivider1.classList.add('booking-progress-active');
            }
            updateMobileProgressStep(2);

            var guestFullNameInput = document.getElementById('guest-full-name');
            if (guestFullNameInput) {
                guestDetailsPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                guestFullNameInput.focus();
            }
        });
    }

    document.addEventListener('booking:itinerarySelected', function () {
        var el = document.getElementById('booking-error-voyage');
        if (el) el.textContent = '';
    });
    document.addEventListener('booking:cabinsUpdated', function () {
        var list = document.getElementById('in-voyage-cabins-list');
        var cards = list ? list.querySelectorAll('.in-voyage-cabin-card') : [];
        if (cards.length > 0) {
            var el = document.getElementById('booking-error-cabin');
            if (el) el.textContent = '';
        }
    });
    document.addEventListener('booking:cabinQuantityChanged', function () {
        var el = document.getElementById('booking-error-cabin');
        if (el) el.textContent = '';
    });
    var guestNameEl = document.getElementById('guest-full-name');
    var guestPhoneEl = document.getElementById('guest-phone');
    var guestEmailEl = document.getElementById('guest-email');
    if (guestNameEl) {
        guestNameEl.addEventListener('input', function () {
            var el = document.getElementById('booking-error-fullName');
            if (el) el.textContent = '';
        });
    }
    if (guestPhoneEl) {
        guestPhoneEl.addEventListener('input', function () {
            var el = document.getElementById('booking-error-phone');
            if (el) el.textContent = '';
        });
    }
    if (guestEmailEl) {
        guestEmailEl.addEventListener('input', function () {
            var el = document.getElementById('booking-error-email');
            if (el) el.textContent = '';
        });
    }

    if (guestBackButton && bookingStepsPanel && guestDetailsPanel) {
        guestBackButton.addEventListener('click', function () {
            bookingStepsPanel.style.display = '';
            guestDetailsPanel.classList.remove('is-visible');

            if (progressGuestDetails) {
                progressGuestDetails.classList.remove('booking-progress-active');
            }
            if (progressDivider1) {
                progressDivider1.classList.remove('booking-progress-active');
            }
            if (progressPayment) {
                progressPayment.classList.remove('booking-progress-active');
            }
            if (progressDividerMid) {
                progressDividerMid.classList.remove('booking-progress-active');
            }
            updateMobileProgressStep(1);
        });
    }

    if (guestContinueButton && guestDetailsPanel && paymentPanel) {
        guestContinueButton.addEventListener('click', function () {
            if (typeof validateStep2GuestDetails === 'function') {
                var result = validateStep2GuestDetails();
                if (!result.valid) {
                    showStep2InlineErrors(result.fieldErrors);
                    var firstErr = document.querySelector('#guest-details-panel .booking-field-error:not(:empty)');
                    if (firstErr) {
                        var scrollTarget = firstErr.closest('.guest-details-field') || firstErr;
                        scrollTarget.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return;
                }
            }
            clearBookingStep2Errors();
            guestDetailsPanel.classList.remove('is-visible');
            paymentPanel.classList.add('is-visible');

            if (progressPayment) {
                progressPayment.classList.add('booking-progress-active');
            }
            if (progressDividerMid) {
                progressDividerMid.classList.add('booking-progress-active');
            }
            updateMobileProgressStep(3);
        });
    }

    if (paymentBackButton && guestDetailsPanel && paymentPanel) {
        paymentBackButton.addEventListener('click', function () {
            paymentPanel.classList.remove('is-visible');
            guestDetailsPanel.classList.add('is-visible');

            if (progressPayment) {
                progressPayment.classList.remove('booking-progress-active');
            }
            if (progressDividerMid) {
                progressDividerMid.classList.remove('booking-progress-active');
            }
            updateMobileProgressStep(2);
        });
    }

    if (confirmationTypeButtons && confirmationTypeButtons.length) {
        confirmationTypeButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var type = btn.getAttribute('data-confirmation') || 'direct';
                confirmationTypeButtons.forEach(function (b) {
                    b.classList.remove('payment-option-primary');
                });
                btn.classList.add('payment-option-primary');
                if (type === 'inquiry') {
                    isInquiryMode = true;
                    selectedPaymentMethod = null;
                    if (paymentBodyMethods) paymentBodyMethods.style.display = 'none';
                    if (paymentInquiryMessage) paymentInquiryMessage.style.display = '';
                } else {
                    isInquiryMode = false;
                    if (paymentBodyMethods) paymentBodyMethods.style.display = '';
                    if (paymentInquiryMessage) paymentInquiryMessage.style.display = 'none';
                    if (paymentMethodButtons && paymentMethodButtons.length) {
                        var primary = document.querySelector('#booking-page .payment-body-methods .payment-option-primary');
                        selectedPaymentMethod = primary ? primary.getAttribute('data-method') : (paymentMethodButtons[0] ? paymentMethodButtons[0].getAttribute('data-method') : null);
                    }
                }
            });
        });
    }

    if (paymentMethodButtons && paymentMethodButtons.length) {
        paymentMethodButtons.forEach(function (btn) {
            if (btn.classList.contains('payment-option-primary')) {
                selectedPaymentMethod = btn.getAttribute('data-method') || null;
            }
        });

        paymentMethodButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (isInquiryMode) return;
                selectedPaymentMethod = btn.getAttribute('data-method') || null;
                paymentMethodButtons.forEach(function (b) {
                    b.classList.remove('payment-option-primary');
                });
                btn.classList.add('payment-option-primary');
            });
        });
    }

    function collectBookingPayload() {
        var page = document.getElementById('booking-page');
        var langAttr = page && page.getAttribute('data-lang') ? String(page.getAttribute('data-lang')).toLowerCase() : 'en';
        var isVndLocale = (langAttr === 'vi');
        var dateInput = document.getElementById('booking-departure-date');
        var itineraryId = page && page.getAttribute('data-itinerary-id') ? page.getAttribute('data-itinerary-id') : null;
        var itineraryName = '';
        var cruiseName = '';
        var durationLabel = '';
        var destination = '';
        var titleEl = document.getElementById('itinerary-card-title');
        var cruiseEl = document.getElementById('itinerary-card-cruise');
        var durationEl = document.getElementById('itinerary-card-duration');
        var destEl = document.getElementById('itinerary-card-destination');
        if (titleEl) itineraryName = titleEl.textContent.trim();
        if (cruiseEl) cruiseName = cruiseEl.textContent.trim();
        if (durationEl) durationLabel = durationEl.textContent.trim();
        if (destEl) destination = destEl.textContent.trim();
        var fullNameEl = document.getElementById('guest-full-name');
        var phoneEl = document.getElementById('guest-phone');
        var emailEl = document.getElementById('guest-email');
        var fullName = fullNameEl ? fullNameEl.value.trim() : '';
        var phone = phoneEl ? phoneEl.value.trim() : '';
        var email = emailEl ? emailEl.value.trim() : '';
        var cabins = [];
        document.querySelectorAll('#in-voyage-cabins-list .in-voyage-cabin-card').forEach(function (card) {
            var cabinId = card.getAttribute('data-cabin-id') || null;
            if (cabinId) cabinId = parseInt(cabinId, 10) || null;
            var nameEl = card.querySelector('.in-voyage-cabin-name');
            var descEl = card.querySelector('.in-voyage-cabin-desc');
            var fareEl = card.querySelector('.in-voyage-cabin-fare strong');
            var unitPrice = 0;
            if (fareEl && fareEl.textContent) {
                var text = fareEl.textContent || '';
                if (isVndLocale) {
                    var rawVnd = text.replace(/[^0-9]/g, '');
                    unitPrice = parseInt(rawVnd, 10) || 0;
                } else {
                    var cleanedUsd = text.replace(/[^0-9.]/g, '');
                    var parsedUsd = parseFloat(cleanedUsd);
                    unitPrice = (!isNaN(parsedUsd) && isFinite(parsedUsd)) ? parsedUsd : 0;
                }
            }
            var adults = 0, c612 = 0, c25 = 0, inf = 0;
            card.querySelectorAll('.in-voyage-qty-control').forEach(function (ctrl, idx) {
                var input = ctrl.querySelector('input');
                var v = input ? (parseInt(input.value, 10) || 0) : 0;
                if (idx === 0) adults = v;
                else if (idx === 1) c612 = v;
                else if (idx === 2) c25 = v;
                else if (idx === 3) inf = v;
            });
            var cabinRow = {
                cabin_name: nameEl ? nameEl.textContent.trim() : '',
                cabin_description: descEl ? descEl.textContent.trim() : '',
                unit_price: unitPrice,
                quantity: 1,
                adults: adults,
                children_6_12: c612,
                children_2_5: c25,
                infants: inf,
                total_price: unitPrice
            };
            if (cabinId) cabinRow.cabin_id = cabinId;
            cabins.push(cabinRow);
        });
        var amenities = [];
        var subtotalAmenities = 0;
        document.querySelectorAll('#booking-page .summary-amenities-list .summary-amenity-line').forEach(function (line) {
            var nameEl = line.querySelector('.summary-amenity-name');
            var priceEl = line.querySelector('.summary-price-value');
            var qtyEl = line.querySelector('.mmddyyyy');
            var qty = 0;
            if (qtyEl && qtyEl.textContent) {
                var m = qtyEl.textContent.match(/(?:Quantity|Số lượng):\s*(\d+)/i);
                if (m) qty = parseInt(m[1], 10) || 0;
            }
            var lineDisplayTotal = 0;
            if (priceEl && priceEl.textContent) {
                var priceText = priceEl.textContent || '';
                if (isVndLocale) {
                    var rawAmenityVnd = priceText.replace(/[^0-9]/g, '');
                    lineDisplayTotal = parseInt(rawAmenityVnd, 10) || 0;
                } else {
                    var cleanedAmenityUsd = priceText.replace(/[^0-9.]/g, '');
                    var parsedAmenityUsd = parseFloat(cleanedAmenityUsd);
                    lineDisplayTotal = (!isNaN(parsedAmenityUsd) && isFinite(parsedAmenityUsd)) ? parsedAmenityUsd : 0;
                }
            }
            var unitPrice = 0;
            if (qty > 0 && lineDisplayTotal > 0) {
                unitPrice = lineDisplayTotal / qty;
            } else if (qty === 0) {
                unitPrice = lineDisplayTotal;
            }
            subtotalAmenities += unitPrice * qty;
            amenities.push({
                amenity_name: nameEl ? nameEl.textContent.trim() : '',
                unit_price: unitPrice,
                quantity: qty,
                total_price: unitPrice
            });
        });
        var subtotalCabins = 0;
        cabins.forEach(function (c) { subtotalCabins += c.total_price * (c.quantity || 1); });

        var totalAmount = 0;
        if (typeof window !== 'undefined' && typeof window.__bookingRawTotal === 'number' && window.__bookingRawTotal > 0) {
            totalAmount = window.__bookingRawTotal;
        } else {
            var totalAmountEl = document.querySelector('#booking-page .total-amount');
            if (totalAmountEl && totalAmountEl.textContent) {
                var totalText = totalAmountEl.textContent || '';
                if (isVndLocale) {
                    var rawTotalVnd = totalText.replace(/[^0-9]/g, '');
                    totalAmount = parseInt(rawTotalVnd, 10) || 0;
                } else {
                    var cleanedTotalUsd = totalText.replace(/[^0-9.]/g, '');
                    var parsedTotalUsd = parseFloat(cleanedTotalUsd);
                    totalAmount = (!isNaN(parsedTotalUsd) && isFinite(parsedTotalUsd)) ? parsedTotalUsd : 0;
                }
            }
        }
        var guestsTotal = 0;
        cabins.forEach(function (c) {
            guestsTotal += (c.adults || 0) + (c.children_6_12 || 0) + (c.children_2_5 || 0) + (c.infants || 0);
        });
        var departureDateValue = dateInput && dateInput.value ? dateInput.value : null;
        if (departureDateValue) {
            departureDateValue = convertDdMmYyyyToYmd(departureDateValue) || departureDateValue;
        }
        return {
            departure_date: departureDateValue,
            itinerary_id: itineraryId,
            itinerary_name: itineraryName,
            cruise_name: cruiseName,
            itinerary_duration_label: durationLabel,
            destination: destination,
            guests_total: guestsTotal,
            full_name: fullName,
            email: email,
            phone: phone,
            subtotal_cabins: subtotalCabins,
            subtotal_amenities: subtotalAmenities,
            total_amount: totalAmount,
            cabins: cabins,
            amenities: amenities
        };
    }

    function applyBookingSummaryFromBooking(booking) {
        if (!booking) return;

        var summaryTitle = document.getElementById('summary-voyage-title');
        var summaryCruise = document.getElementById('summary-voyage-cruise');
        var summaryDateDisplay = document.getElementById('summary-date-display');
        var bookingDateDisplay = document.getElementById('booking-date-display');
        var dateInput = document.getElementById('booking-departure-date');
        var guestsInput = document.querySelector('#booking-page .summary-guests-input');
        var totalAmountEl = document.querySelector('#booking-page .total-amount');
        var bookingCurrency = (booking.currency || '').toLowerCase();

        if (summaryTitle && booking.itinerary_name) {
            summaryTitle.textContent = booking.itinerary_name;
        }
        if (summaryCruise && booking.cruise_name) {
            summaryCruise.textContent = booking.cruise_name;
        }

        if (booking.departure_date) {
            var displayDate = formatDateDisplayFromYmd(booking.departure_date);
            if (summaryDateDisplay) summaryDateDisplay.textContent = displayDate;
            if (bookingDateDisplay) bookingDateDisplay.textContent = displayDate;
            if (dateInput) dateInput.value = convertYmdToDdMmYyyy(booking.departure_date) || booking.departure_date;
        }

        if (guestsInput && typeof booking.guests_total === 'number' && booking.guests_total > 0) {
            var langAttr = document.getElementById('booking-page');
            var lang = langAttr ? (langAttr.getAttribute('data-lang') || 'all') : 'all';
            if (lang === 'vi') {
                guestsInput.value = booking.guests_total + ' khách';
            } else if (lang === 'en') {
                guestsInput.value = booking.guests_total + ' people';
            } else {
                guestsInput.value = String(booking.guests_total);
            }
        }

        if (typeof booking.total_amount === 'number' && totalAmountEl) {
            var displayTotal = booking.total_amount;
            totalAmountEl.textContent = formatCabinPriceGlobal(displayTotal);
        }

        var summaryCabinsBlock = document.getElementById('summary-cabins-block');
        var summaryCabinsList = document.getElementById('summary-cabins-list');
        if (summaryCabinsList) summaryCabinsList.innerHTML = '';
        if (booking.cabins && booking.cabins.length && summaryCabinsList) {
            var grouped = {};
            booking.cabins.forEach(function (c) {
                var key = c.cabin_name || 'Cabin';
                if (!grouped[key]) {
                    grouped[key] = { count: 0, total: 0, guestsText: '' };
                }
                var qty = c.quantity != null ? c.quantity : 1;
                grouped[key].count += qty;
                var lineTotal = Number(c.total_price) || 0;
                grouped[key].total += lineTotal;
                
                var guestParts = [];
                var adults = parseInt(c.adults) || 0;
                var children_6_12 = parseInt(c.children_6_12) || 0;
                var children_2_5 = parseInt(c.children_2_5) || 0;
                var infants = parseInt(c.infants) || 0;
                
                if (window.__bookingLocale === 'vi') {
                    if (adults > 0) guestParts.push(adults + ' Người lớn');
                    if (children_6_12 > 0) guestParts.push(children_6_12 + ' Trẻ 6-12 tuổi');
                    if (children_2_5 > 0) guestParts.push(children_2_5 + ' Trẻ 2-5 tuổi');
                    if (infants > 0) guestParts.push(infants + ' Em bé');
                } else {
                    if (adults > 0) guestParts.push(adults + ' Adult' + (adults > 1 ? 's' : ''));
                    if (children_6_12 > 0) guestParts.push(children_6_12 + ' Child 6-12');
                    if (children_2_5 > 0) guestParts.push(children_2_5 + ' Child 2-5');
                    if (infants > 0) guestParts.push(infants + ' Infant' + (infants > 1 ? 's' : ''));
                }
                
                var guestSummary = guestParts.join(', ');
                if (guestSummary) {
                    if (grouped[key].guestsText) {
                        grouped[key].guestsText += ' | ' + guestSummary;
                    } else {
                        grouped[key].guestsText = guestSummary;
                    }
                }
            });
            Object.keys(grouped).forEach(function (name) {
                var row = document.createElement('div');
                row.className = 'summary-cabin-item-wrap';
                var inner = document.createElement('div');
                inner.className = 'presidential-suite-parent';

                var nameDiv = document.createElement('div');
                nameDiv.className = 'mmddyyyy';
                var label = name;
                if (grouped[name].count > 1) {
                    label += ' (x' + grouped[name].count + ')';
                }
                nameDiv.textContent = label;
                nameDiv.setAttribute('data-full-text', label);
                nameDiv.title = label;

                var guestsDiv = document.createElement('div');
                guestsDiv.className = 'summary-guests-info';
                var guestsText = grouped[name].guestsText || '';
                guestsDiv.textContent = guestsText;
                guestsDiv.setAttribute('data-full-text', guestsText);
                guestsDiv.title = guestsText;

                var priceDiv = document.createElement('div');
                priceDiv.className = 'summary-price-value';
                priceDiv.textContent = formatCabinPriceGlobal(grouped[name].total);

                inner.appendChild(nameDiv);
                inner.appendChild(guestsDiv);
                inner.appendChild(priceDiv);
                row.appendChild(inner);
                summaryCabinsList.appendChild(row);
            });
            if (summaryCabinsBlock) summaryCabinsBlock.style.display = '';
        } else if (summaryCabinsBlock) {
            summaryCabinsBlock.style.display = 'none';
        }

        var summaryAmenitiesBlock = document.getElementById('summary-amenities-block');
        var summaryAmenitiesList = document.querySelector('#booking-page .summary-amenities-list');
        if (summaryAmenitiesList) summaryAmenitiesList.innerHTML = '';
        if (booking.amenities && booking.amenities.length && summaryAmenitiesList) {
            booking.amenities.forEach(function (a) {
                var line = document.createElement('div');
                line.className = 'summary-amenity-line';

                var nameDiv = document.createElement('div');
                nameDiv.className = 'summary-amenity-name';
                nameDiv.textContent = a.amenity_name || '';

                var qtyWrap = document.createElement('div');
                qtyWrap.className = 'quantity-2';
                var qtyText = document.createElement('div');
                qtyText.className = 'mmddyyyy';
                var qtyLabel = (window.__bookingLocale === 'vi') ? 'Số lượng: ' : 'Quantity: ';
                qtyText.textContent = qtyLabel + (a.quantity != null ? a.quantity : 0);
                qtyWrap.appendChild(qtyText);

                var totalDiv = document.createElement('div');
                totalDiv.className = 'summary-price-value';
                var amenityTotal = a.total_price || 0;
                totalDiv.textContent = formatCabinPriceGlobal(amenityTotal);

                line.appendChild(nameDiv);
                line.appendChild(qtyWrap);
                line.appendChild(totalDiv);

                summaryAmenitiesList.appendChild(line);
            });
            if (summaryAmenitiesBlock) summaryAmenitiesBlock.style.display = '';
        } else if (summaryAmenitiesBlock) {
            summaryAmenitiesBlock.style.display = 'none';
        }
    }

    function initPayment() {
        if (isInquiryMode) {
            var bookingData = collectBookingPayload();
            if (!bookingData || !bookingData.full_name || !bookingData.full_name.trim()) {
                if (typeof swalAlert !== 'undefined') swalAlert.warning('Vui lòng nhập họ tên.'); else alert('Vui lòng nhập họ tên.');
                return;
            }
            if (paymentLoadingOverlay) {
                paymentLoadingOverlay.classList.add('is-visible');
            }
            var inquiryPage = document.getElementById('booking-page');
            var inquiryLocale = (inquiryPage && inquiryPage.getAttribute('data-lang')) ? String(inquiryPage.getAttribute('data-lang')).toLowerCase() : 'en';
            var inquiryCurrency = (inquiryLocale === 'vi') ? 'vnd' : 'usd';
            if (!bookingData.currency) {
                bookingData.currency = inquiryCurrency;
            }
            fetch('/api/booking/inquiry', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ booking: bookingData })
            }).then(function (res) {
                return res.json().then(function (data) {
                    return { res: res, data: data };
                });
            }).then(function (_) {
                if (paymentLoadingOverlay) {
                    paymentLoadingOverlay.classList.remove('is-visible');
                }
                var res = _.res;
                var data = _.data;
                if (res.ok && data && data.success) {
                    showCompletePanel('success');
                } else {
                    var msg = data && data.message ? data.message : 'Không thể gửi yêu cầu. Vui lòng thử lại.';
                    if (typeof swalAlert !== 'undefined') swalAlert.error(msg); else alert(msg);
                }
            }).catch(function () {
                if (paymentLoadingOverlay) {
                    paymentLoadingOverlay.classList.remove('is-visible');
                }
                if (typeof swalAlert !== 'undefined') swalAlert.error('Không thể kết nối. Vui lòng thử lại.'); else alert('Không thể kết nối. Vui lòng thử lại.');
            });
            return;
        }
        if (!selectedPaymentMethod) {
            if (typeof swalAlert !== 'undefined') swalAlert.warning('Vui lòng chọn phương thức thanh toán.'); else alert('Vui lòng chọn phương thức thanh toán.');
            return;
        }

        var bookingDataCheck = collectBookingPayload();
        if (!bookingDataCheck || !bookingDataCheck.full_name || !bookingDataCheck.full_name.trim()) {
            if (typeof swalAlert !== 'undefined') swalAlert.warning('Vui lòng nhập họ tên.'); else alert('Vui lòng nhập họ tên.');
            return;
        }
        if (!bookingDataCheck.email || !bookingDataCheck.email.trim()) {
            var pageEl = document.getElementById('booking-page');
            var msgEmail = (pageEl && pageEl.getAttribute('data-lang') === 'vi') ? 'Vui lòng nhập địa chỉ email để nhận xác nhận đặt phòng.' : 'Please enter your email address to receive booking confirmation.';
            if (typeof swalAlert !== 'undefined') swalAlert.warning(msgEmail); else alert(msgEmail);
            return;
        }

        var bookingPage = document.getElementById('booking-page');
        var locale = (bookingPage && bookingPage.getAttribute('data-lang')) ? String(bookingPage.getAttribute('data-lang')).toLowerCase() : 'en';
        var isVndLocale = (locale === 'vi');

        var amount = 0;
        if (typeof window !== 'undefined' && typeof window.__bookingRawTotal === 'number' && window.__bookingRawTotal > 0) {
            amount = window.__bookingRawTotal;
        } else {
            var totalAmountEl = document.querySelector('#booking-page .total-amount');
            if (totalAmountEl) {
                var text = totalAmountEl.textContent || '';
                if (isVndLocale) {
                    var numTextVnd = text.replace(/[^0-9]/g, '');
                    amount = parseInt(numTextVnd, 10) || 0;
                } else {
                    var cleaned = text.replace(/[^0-9.]/g, '');
                    var parsed = parseFloat(cleaned);
                    if (!isNaN(parsed) && isFinite(parsed)) {
                        amount = Math.round(parsed);
                    }
                }
            }
        }

        var defaultCurrency = isVndLocale ? 'vnd' : 'usd';

        var payload = {
            method: selectedPaymentMethod,
            amount: amount,
            return_base: window.location.origin + window.location.pathname
        };
        var bookingData = collectBookingPayload();
        if (bookingData) {
            payload.booking = bookingData;
            if (bookingData.cabins && bookingData.cabins.length) {
                var firstHasCabinId = bookingData.cabins.some(function (c) { return c.cabin_id; });
                if (!firstHasCabinId) {
                    payload.currency = defaultCurrency;
                }
            } else {
                payload.currency = defaultCurrency;
            }
        } else {
            payload.currency = defaultCurrency;
        }

        if (paymentLoadingOverlay) {
            paymentLoadingOverlay.classList.add('is-visible');
        }

        fetch('/api/payment/init', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        }).then(function (res) {
            return res.json().then(function (data) {
                return { res: res, data: data };
            });
        }).then(function (_) {
            var res = _.res;
            var data = _.data;
            if (res.ok && data && data.success && data.payment_url) {
                if (typeof window !== 'undefined' && window.sessionStorage && data.internal_tx_id) {
                    try {
                        window.sessionStorage.setItem('bookingCurrentPaymentTx', data.internal_tx_id);
                    } catch (e) { }
                }
                window.location.href = data.payment_url;
                return;
            }
            if (paymentLoadingOverlay) {
                paymentLoadingOverlay.classList.remove('is-visible');
            }
            var errMsg = 'Khởi tạo thanh toán thất bại. Vui lòng thử lại.';
            var bookingPage = document.getElementById('booking-page');
            if (data && data.code === 'currency_method_mismatch' && data.reason && bookingPage) {
                var locale = (bookingPage.getAttribute('data-lang') || 'en').toLowerCase();
                if (locale !== 'vi') locale = 'en';
                var reasonKey = (data.reason === 'usd_only') ? 'usd' : (data.reason === 'vnd_only') ? 'vnd' : data.reason;
                var key = 'data-currency-mismatch-' + reasonKey + '-' + locale;
                var msgFromLang = bookingPage.getAttribute(key);
                if (msgFromLang) errMsg = msgFromLang;
            } else if (data && data.message) {
                errMsg = data.message;
            }
            if (typeof swalAlert !== 'undefined') swalAlert.error(errMsg); else alert(errMsg);
        }).catch(function () {
            if (paymentLoadingOverlay) {
                paymentLoadingOverlay.classList.remove('is-visible');
            }
            if (typeof swalAlert !== 'undefined') swalAlert.error('Không thể kết nối tới cổng thanh toán. Vui lòng thử lại.'); else alert('Không thể kết nối tới cổng thanh toán. Vui lòng thử lại.');
        });
    }

    if (paymentContinueButton) {
        paymentContinueButton.addEventListener('click', initPayment);
    }
});

