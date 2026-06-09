document.addEventListener('DOMContentLoaded', function () {
    var calculator = window.cabinPriceCalculator;
    if (!calculator) return;

    var page = document.getElementById('booking-page');
    if (!page) return;

    var lang = page.getAttribute('data-lang') || 'en';
    calculator.setLocale(lang);
    var currentDuration = null;

    function storeCabinDataFromAPI(cabinData) {
        if (!cabinData || !cabinData.id) return;
        calculator.setCabinData(cabinData.id, {
            capacity: cabinData.capacity || 2,
            over_capacity_adult: cabinData.over_capacity_adult,
            over_capacity_child_6_12: cabinData.over_capacity_child_6_12,
            over_capacity_child_2_5: cabinData.over_capacity_child_2_5,
            over_capacity_infant: cabinData.over_capacity_infant,
            prices: cabinData.prices || {},
            duration: currentDuration || 2,
            name: cabinData.name || 'Cabin'
        });
    }

    function loadAndStoreCabinsData(cruiseId) {
        if (!cruiseId) return;
        var apiUrl = page.getAttribute('data-api-cabins') || '/api/booking/cabins';
        var params = 'lang=' + encodeURIComponent(lang) + '&cruise_id=' + encodeURIComponent(cruiseId);
        if (currentDuration) {
            params += '&duration=' + encodeURIComponent(currentDuration);
        }
        fetch(apiUrl + '?' + params, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.success && Array.isArray(data.items)) {
                    data.items.forEach(function (cabin) { storeCabinDataFromAPI(cabin); });
                }
            }).catch(function () {});
    }

    document.addEventListener('booking:itinerarySelected', function (e) {
        var detail = e && e.detail ? e.detail : null;
        if (detail && detail.duration) currentDuration = detail.duration;
        if (detail && detail.cruise_id) loadAndStoreCabinsData(detail.cruise_id);
    });

    document.addEventListener('booking:cabinQuantityChanged', function () {
        recalculateAllCabinPrices();
    });

    function recalculateAllCabinPrices() {
        var cards = document.querySelectorAll('#in-voyage-cabins-list .in-voyage-cabin-card');
        
        cards.forEach(function (card) {
            var cabinId = card.getAttribute('data-cabin-id');
            if (!cabinId) return;
            var cabinIdInt = parseInt(cabinId, 10);
            var cabinInfo = calculator.getCabinInfo(cabinIdInt);
            if (!cabinInfo) return;

            var guestFields = card.querySelectorAll('.in-voyage-guest-field');
            var guests = { adults: 0, children_6_12: 0, children_2_5: 0, infants: 0 };
            guestFields.forEach(function (field, index) {
                var input = field.querySelector('input');
                if (input) {
                    var value = parseInt(input.value, 10) || 0;
                    switch (index) {
                        case 0: guests.adults = value; break;
                        case 1: guests.children_6_12 = value; break;
                        case 2: guests.children_2_5 = value; break;
                        case 3: guests.infants = value; break;
                    }
                }
            });

            var result = calculator.calculatePrice(cabinIdInt, guests);
            var fareEl = card.querySelector('.in-voyage-cabin-fare strong');
            if (fareEl && result.success) {
                card.setAttribute('data-cabin-price', result.price);
                if (result.price > 0) {
                    fareEl.textContent = calculator.formatPrice(result.price, lang);
                }
            }
        });
        
        updateSummaryPrices();
        
        requestAnimationFrame(function() {
            if (typeof window.__updateEstimatedTotal === 'function') {
                window.__updateEstimatedTotal();
            }
        });
    }

    function updateSummaryPrices() {
        var summaryCabinsList = document.getElementById('summary-cabins-list');
        var summaryCabinsBlock = document.getElementById('summary-cabins-block');
        
        if (!summaryCabinsList) return;

        var cards = document.querySelectorAll('#in-voyage-cabins-list .in-voyage-cabin-card');
        
        if (cards.length === 0) {
            summaryCabinsList.innerHTML = '';
            if (summaryCabinsBlock) summaryCabinsBlock.style.display = 'none';
            return;
        }
        
        if (summaryCabinsBlock) summaryCabinsBlock.style.display = '';
        
        var grouped = {};
        cards.forEach(function (card) {
            var nameEl = card.querySelector('.in-voyage-cabin-name');
            var cabinName = nameEl ? nameEl.textContent.trim() : 'Cabin';
            
            if (!grouped[cabinName]) {
                grouped[cabinName] = { count: 0, total: 0, guestsText: '' };
            }
            grouped[cabinName].count += 1;
            
            var price = 0;
            var priceAttr = card.getAttribute('data-cabin-price');
            if (priceAttr) {
                price = parseFloat(priceAttr) || 0;
            } else {
                var fareEl = card.querySelector('.in-voyage-cabin-fare strong');
                if (fareEl && fareEl.textContent) {
                    var fareText = fareEl.textContent.trim();
                    var cleaned;
                    if ((lang || '').toLowerCase() === 'vi') {
                        cleaned = fareText.replace(/[^\d]/g, '');
                        price = parseInt(cleaned, 10) || 0;
                    } else {
                        cleaned = fareText.replace(/[^\d.]/g, '');
                        price = parseFloat(cleaned) || 0;
                    }
                }
            }
            grouped[cabinName].total += price;

            // Build guests text from quantities on this card (Adults, Children, Infants)
            var guestSummaryParts = [];
            var guestFields = card.querySelectorAll('.in-voyage-guest-field');
            guestFields.forEach(function (field, index) {
                var input = field.querySelector('input');
                if (!input) return;
                var value = parseInt(input.value, 10) || 0;
                if (value <= 0) return;
                var label = '';
                if (window.__bookingLocale === 'vi') {
                    if (index === 0) {
                        label = value + ' Người lớn';
                    } else if (index === 1) {
                        label = value + ' Trẻ 6-12 tuổi';
                    } else if (index === 2) {
                        label = value + ' Trẻ 2-5 tuổi';
                    } else if (index === 3) {
                        label = value + ' Em bé';
                    }
                } else {
                    if (index === 0) {
                        label = value + ' Adult' + (value > 1 ? 's' : '');
                    } else if (index === 1) {
                        label = value + ' Child 6-12';
                    } else if (index === 2) {
                        label = value + ' Child 2-5';
                    } else if (index === 3) {
                        label = value + ' Infant' + (value > 1 ? 's' : '');
                    }
                }
                if (label) guestSummaryParts.push(label);
            });
            var guestSummary = guestSummaryParts.join(', ');
            if (guestSummary) {
                if (grouped[cabinName].guestsText) {
                    grouped[cabinName].guestsText += ' | ' + guestSummary;
                } else {
                    grouped[cabinName].guestsText = guestSummary;
                }
            }
        });

        summaryCabinsList.innerHTML = '';
        
        Object.keys(grouped).forEach(function (name) {
            if (grouped[name].count === 0) return;
            
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
            var roundedTotal = lang === 'vi' ? Math.round(grouped[name].total) : Math.round(grouped[name].total * 100) / 100;
            priceDiv.textContent = calculator.formatPrice(roundedTotal, lang);

            inner.appendChild(nameDiv);
            inner.appendChild(guestsDiv);
            inner.appendChild(priceDiv);
            row.appendChild(inner);
            summaryCabinsList.appendChild(row);
        });
    }

    function showCabinValidationErrors() {}

    function clearCabinValidationErrors() {}

    function showCapacityExceededInfo() {}

    function hideCapacityExceededInfo() {}

    function bindQuantityValidation(card) {
        var cabinId = card.getAttribute('data-cabin-id');
        if (!cabinId) return;
        var cabinIdInt = parseInt(cabinId, 10);
        var cabinInfo = calculator.getCabinInfo(cabinIdInt);
        if (!cabinInfo) return;
        var capacity = cabinInfo.capacity || 2;
        var maxAdults = cabinInfo.overCapacityAdult && cabinInfo.overCapacityAdult > 0 ? cabinInfo.overCapacityAdult : (2 * capacity - 1);
        var maxChild_6_12 = cabinInfo.overCapacityChild_6_12 && cabinInfo.overCapacityChild_6_12 > 0 ? cabinInfo.overCapacityChild_6_12 : capacity;
        var maxChild_2_5 = cabinInfo.overCapacityChild_2_5 && cabinInfo.overCapacityChild_2_5 > 0 ? cabinInfo.overCapacityChild_2_5 : capacity;
        var maxInfant = cabinInfo.overCapacityInfant && cabinInfo.overCapacityInfant > 0 ? cabinInfo.overCapacityInfant : capacity;

        var adultsField = card.querySelector('.in-voyage-guest-field:nth-child(1)');
        if (adultsField) {
            var plusBtn = adultsField.querySelector('.qty-plus');
            var input = adultsField.querySelector('input');
            if (plusBtn && input) {
                plusBtn.addEventListener(
                    'click',
                    function (e) {
                        var current = parseInt(input.value, 10) || 0;
                        if (current >= maxAdults) {
                            e.preventDefault();
                            if (typeof e.stopImmediatePropagation === 'function') {
                                e.stopImmediatePropagation();
                            } else {
                                e.stopPropagation();
                            }
                        }
                    },
                    true
                );
            }
        }

        [1, 2, 3].forEach(function (index) {
            var field = card.querySelector('.in-voyage-guest-field:nth-child(' + (index + 1) + ')');
            if (!field) return;
            var plusBtn = field.querySelector('.qty-plus');
            var input = field.querySelector('input');
            if (!plusBtn || !input) return;

            plusBtn.addEventListener(
                'click',
                function (e) {
                    var current = parseInt(input.value, 10) || 0;
                    var limit = capacity;
                    if (index === 1) {
                        limit = maxChild_6_12;
                    } else if (index === 2) {
                        limit = maxChild_2_5;
                    } else if (index === 3) {
                        limit = maxInfant;
                    }
                    if (current >= limit) {
                        e.preventDefault();
                        if (typeof e.stopImmediatePropagation === 'function') {
                            e.stopImmediatePropagation();
                        } else {
                            e.stopPropagation();
                        }
                    }
                },
                true
            );
        });
    }

    function showQuickToast() {}

    document.addEventListener('booking:cabinsUpdated', function () {
        setTimeout(function() {
            var cards = document.querySelectorAll('#in-voyage-cabins-list .in-voyage-cabin-card');
            cards.forEach(function (card) { bindQuantityValidation(card); });
            recalculateAllCabinPrices();
        }, 50);
    });

    var originalAddCabinListeners = document.querySelectorAll('.add-cabin-btn');
    originalAddCabinListeners.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var realCabinId = btn.getAttribute('data-cabin-id');
            var cabinIdInt = realCabinId ? parseInt(realCabinId, 10) : null;
            
            if (cabinIdInt && !calculator.getCabinInfo(cabinIdInt)) {
                var capacity = parseInt(btn.getAttribute('data-cabin-capacity')) || 2;
                calculator.setCabinData(cabinIdInt, {
                    capacity: capacity,
                    prices: {},
                    duration: currentDuration || 2,
                    name: btn.getAttribute('data-cabin-name') || 'Cabin'
                });
            }
            
            setTimeout(function () {
                var cards = document.querySelectorAll('#in-voyage-cabins-list .in-voyage-cabin-card');
                var lastCard = cards[cards.length - 1];
                if (lastCard && realCabinId) {
                    lastCard.setAttribute('data-cabin-id', realCabinId);
                    bindQuantityValidation(lastCard);
                    setTimeout(function() { 
                        updateSummaryPrices();
                        if (typeof window.__updateEstimatedTotal === 'function') window.__updateEstimatedTotal();
                    }, 100);
                }
            }, 150);
        });
    });

    var style = document.createElement('style');
    style.textContent = `
        .presidential-suite-parent {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }
        .presidential-suite-parent .mmddyyyy {
            flex: 0 1 auto;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .presidential-suite-parent .mmddyyyy.scrollable {
            display: inline-block;
            animation: scroll-text 8s linear infinite;
            animation-play-state: paused;
        }
        .presidential-suite-parent .mmddyyyy.scrollable:hover {
            animation-play-state: running;
        }
        .presidential-suite-parent .summary-guests-info {
            flex: 1 1 auto;
            text-align: center;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .presidential-suite-parent .summary-guests-info.scrollable {
            display: inline-block;
            animation: scroll-text 8s linear infinite;
            animation-play-state: paused;
        }
        .presidential-suite-parent .summary-guests-info.scrollable:hover {
            animation-play-state: running;
        }
        .presidential-suite-parent .summary-price-value {
            flex: 0 0 auto;
            text-align: right;
            min-width: 0;
            white-space: nowrap;
        }
        @keyframes scroll-text {
            0% { transform: translateX(0); }
            50% { transform: translateX(calc(-100% + var(--container-width, 200px))); }
            100% { transform: translateX(0); }
        }
    `;
    document.head.appendChild(style);

    window.__cabinPriceRecalculate = recalculateAllCabinPrices;
    window.__cabinPriceUpdateSummary = updateSummaryPrices;
});
