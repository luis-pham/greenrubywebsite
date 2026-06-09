(function(window) {
    'use strict';

    function CabinPriceCalculator() {
        this.cabinsData = {};
        this.locale = 'vi';
    }

    CabinPriceCalculator.prototype.setLocale = function(locale) {
        this.locale = locale || 'vi';
    };

    CabinPriceCalculator.prototype.roundPrice = function(price, locale) {
        var loc = locale || this.locale;
        if (loc === 'vi') {
            return Math.round(price);
        }
        return Math.round(price * 100) / 100;
    };

    CabinPriceCalculator.prototype.setCabinData = function(cabinId, cabinInfo) {
        if (!cabinId || !cabinInfo) return;
        
        this.cabinsData[cabinId] = {
            capacity: parseInt(cabinInfo.capacity, 10) || 2,
            overCapacityAdult: cabinInfo.over_capacity_adult != null ? parseInt(cabinInfo.over_capacity_adult, 10) || 0 : 0,
            overCapacityChild_6_12: cabinInfo.over_capacity_child_6_12 != null ? parseInt(cabinInfo.over_capacity_child_6_12, 10) || 0 : 0,
            overCapacityChild_2_5: cabinInfo.over_capacity_child_2_5 != null ? parseInt(cabinInfo.over_capacity_child_2_5, 10) || 0 : 0,
            overCapacityInfant: cabinInfo.over_capacity_infant != null ? parseInt(cabinInfo.over_capacity_infant, 10) || 0 : 0,
            prices: cabinInfo.prices || {},
            duration: cabinInfo.duration || 2,
            name: cabinInfo.name || 'Cabin'
        };
    };

    CabinPriceCalculator.prototype.validateGuests = function(cabinId, guests) {
        var cabin = this.cabinsData[cabinId];
        if (!cabin) {
            return {
                valid: false,
                errors: ['Cabin data not found. Please refresh the page.']
            };
        }

        var errors = [];
        var capacity = cabin.capacity;
        var adults = parseInt(guests.adults) || 0;
        var children_6_12 = parseInt(guests.children_6_12) || 0;
        var children_2_5 = parseInt(guests.children_2_5) || 0;
        var infants = parseInt(guests.infants) || 0;

        var maxAdults = cabin.overCapacityAdult && cabin.overCapacityAdult > 0 ? cabin.overCapacityAdult : (2 * capacity - 1);
        var maxChild_6_12 = cabin.overCapacityChild_6_12 && cabin.overCapacityChild_6_12 > 0 ? cabin.overCapacityChild_6_12 : capacity;
        var maxChild_2_5 = cabin.overCapacityChild_2_5 && cabin.overCapacityChild_2_5 > 0 ? cabin.overCapacityChild_2_5 : capacity;
        var maxInfant = cabin.overCapacityInfant && cabin.overCapacityInfant > 0 ? cabin.overCapacityInfant : capacity;

        if (adults < 0) errors.push('Number of adults must be 0 or greater');
        if (adults > maxAdults) {
            errors.push('Number of adults must not exceed ' + maxAdults);
        }
        if (children_6_12 < 0) errors.push('Number of children (6-12y) must be 0 or greater');
        if (children_6_12 > maxChild_6_12) {
            errors.push('Number of children (6-12y) must not exceed ' + maxChild_6_12);
        }
        if (children_2_5 < 0) errors.push('Number of children (2-5y) must be 0 or greater');
        if (children_2_5 > maxChild_2_5) {
            errors.push('Number of children (2-5y) must not exceed ' + maxChild_2_5);
        }
        if (infants < 0) errors.push('Number of infants must be 0 or greater');
        if (infants > maxInfant) {
            errors.push('Number of infants must not exceed ' + maxInfant);
        }

        var totalGuests = adults + children_6_12 + children_2_5;
        if (totalGuests === 0) errors.push('At least one guest is required');

        return {
            valid: errors.length === 0,
            errors: errors,
            totalGuests: totalGuests,
            adults: adults,
            children_6_12: children_6_12,
            children_2_5: children_2_5,
            infants: infants
        };
    };

    CabinPriceCalculator.prototype.calculatePrice = function(cabinId, guests) {
        var cabin = this.cabinsData[cabinId];
        if (!cabin) {
            return { success: false, error: 'Cabin data not found', price: 0 };
        }

        var validation = this.validateGuests(cabinId, guests);
        if (!validation.valid) {
            return { success: false, errors: validation.errors, price: 0 };
        }

        var capacity = cabin.capacity;
        var duration = cabin.duration;
        var prices = cabin.prices[duration] || {};
        var adults = validation.adults;
        var children_6_12 = validation.children_6_12;
        var children_2_5 = validation.children_2_5;
        var infants = validation.infants;
        var totalGuests = validation.totalGuests;

        if (totalGuests <= capacity) {
            var basePrice = prices[totalGuests] || 0;
            if (!basePrice && totalGuests > 0) {
                var maxCapacityPrice = prices[capacity] || 0;
                if (maxCapacityPrice > 0) basePrice = (maxCapacityPrice / capacity) * totalGuests;
            }
            var roundedPrice = this.roundPrice(basePrice);
            return {
                success: true,
                price: roundedPrice,
                breakdown: {
                    basePrice: basePrice,
                    extraCharges: 0,
                    totalGuests: totalGuests,
                    capacity: capacity,
                    exceededCapacity: false
                }
            };
        }

        var basePrice = prices[capacity] || 0;
        if (basePrice === 0) {
            return { success: false, error: 'Base price for capacity ' + capacity + ' not found', price: 0 };
        }

        var pricePerPerson = basePrice / capacity;
        var extraCharges = 0;
        var adultsExceeded = 0;
        var children_6_12_exceeded = 0;
        var children_2_5_exceeded = 0;

        var remainingInBase = capacity;
        var adultsInBase = Math.min(adults, remainingInBase);
        remainingInBase -= adultsInBase;
        adultsExceeded = adults - adultsInBase;

        var children_6_12_inBase = Math.min(children_6_12, remainingInBase);
        remainingInBase -= children_6_12_inBase;
        children_6_12_exceeded = children_6_12 - children_6_12_inBase;

        var children_2_5_inBase = Math.min(children_2_5, remainingInBase);
        children_2_5_exceeded = children_2_5 - children_2_5_inBase;

        extraCharges += adultsExceeded * pricePerPerson;
        extraCharges += children_6_12_exceeded * pricePerPerson * 0.75;
        extraCharges += children_2_5_exceeded * pricePerPerson * 0.5;

        var totalPrice = basePrice + extraCharges;
        var roundedPrice = this.roundPrice(totalPrice);
        var roundedExtraCharges = this.roundPrice(extraCharges);

        return {
            success: true,
            price: roundedPrice,
            breakdown: {
                basePrice: basePrice,
                extraCharges: roundedExtraCharges,
                pricePerPerson: pricePerPerson,
                totalGuests: totalGuests,
                capacity: capacity,
                exceededCapacity: true,
                exceeded: {
                    adults: adultsExceeded,
                    children_6_12: children_6_12_exceeded,
                    children_2_5: children_2_5_exceeded,
                    infants: 0
                }
            }
        };
    };

    CabinPriceCalculator.prototype.formatPrice = function(price, locale) {
        var isVND = (locale === 'vi');
        var num = Number(price) || 0;
        if (isVND) {
            var rounded = Math.round(num);
            return rounded.toLocaleString('vi-VN') + ' VND';
        }
        var rounded = Math.round(num * 100) / 100;
        return '$' + rounded.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    CabinPriceCalculator.prototype.getCabinInfo = function(cabinId) {
        return this.cabinsData[cabinId] || null;
    };

    CabinPriceCalculator.prototype.clearAllData = function() {
        this.cabinsData = {};
    };

    CabinPriceCalculator.prototype.batchCalculate = function(cabinsList) {
        if (!Array.isArray(cabinsList)) return { success: false, error: 'Invalid cabins list' };

        var results = [];
        var totalPrice = 0;
        var hasErrors = false;

        for (var i = 0; i < cabinsList.length; i++) {
            var item = cabinsList[i];
            var result = this.calculatePrice(item.cabinId, item.guests);
            if (result.success) {
                totalPrice += result.price;
                results.push({ cabinId: item.cabinId, success: true, price: result.price, breakdown: result.breakdown });
            } else {
                hasErrors = true;
                results.push({ cabinId: item.cabinId, success: false, errors: result.errors || [result.error] });
            }
        }
        return { success: !hasErrors, totalPrice: totalPrice, cabins: results };
    };

    window.CabinPriceCalculator = CabinPriceCalculator;
    if (!window.cabinPriceCalculator) {
        window.cabinPriceCalculator = new CabinPriceCalculator();
    }
})(window);
