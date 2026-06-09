function validateStep1BookingInfo(options) {
    options = options || {};
    var page = document.getElementById('booking-page');
    var errors = [];
    var fieldErrors = {};

    var msgVoyage = options.msgVoyageRequired || (page && page.getAttribute('data-msg-voyage-required')) || '';
    var msgCabin = options.msgCabinRequired || (page && page.getAttribute('data-msg-cabin-required')) || '';
    var msgGuestPerCabin = options.msgGuestPerCabin || (page && page.getAttribute('data-msg-guest-per-cabin')) || '';

    if (!page || !page.getAttribute('data-itinerary-id')) {
        errors.push(msgVoyage);
        fieldErrors.voyage = msgVoyage;
    }

    var cabinList = document.getElementById('in-voyage-cabins-list');
    var cards = cabinList ? cabinList.querySelectorAll('.in-voyage-cabin-card') : [];
    if (!cards.length) {
        errors.push(msgCabin);
        fieldErrors.cabin = msgCabin;
    } else {
        for (var i = 0; i < cards.length; i++) {
            var card = cards[i];
            var inputs = card.querySelectorAll('.in-voyage-qty-control input');
            var sum = 0;
            for (var j = 0; j < inputs.length; j++) {
                var v = parseInt(inputs[j].value, 10);
                if (!isNaN(v) && v > 0) sum += v;
            }
            if (sum < 1) {
                var nameEl = card.querySelector('.in-voyage-cabin-name');
                var cabinName = nameEl ? nameEl.textContent.trim() : ('Cabin ' + (i + 1));
                var msg = cabinName + ': ' + msgGuestPerCabin;
                errors.push(msg);
                fieldErrors['cabin_' + i] = msg;
            }
        }
    }

    return {
        valid: errors.length === 0,
        errors: errors,
        fieldErrors: fieldErrors
    };
}

function validateStep2GuestDetails(options) {
    options = options || {};
    var page = document.getElementById('booking-page');
    var errors = [];
    var fieldErrors = {};

    var msgFullName = options.msgFullNameRequired || (page && page.getAttribute('data-msg-guest-full-name-required')) || '';
    var msgPhoneRequired = options.msgPhoneRequired || (page && page.getAttribute('data-msg-guest-phone-required')) || '';
    var msgPhoneNumeric = options.msgPhoneNumeric || (page && page.getAttribute('data-msg-guest-phone-numeric')) || '';
    var msgEmailRequired = options.msgEmailRequired || (page && page.getAttribute('data-msg-guest-email-required')) || '';
    var msgEmailInvalid = options.msgEmailInvalid || (page && page.getAttribute('data-msg-guest-email-invalid')) || '';

    var nameEl = document.getElementById('guest-full-name');
    var phoneEl = document.getElementById('guest-phone');
    var emailEl = document.getElementById('guest-email');

    var fullName = nameEl ? nameEl.value.trim() : '';
    var phone = phoneEl ? phoneEl.value.trim() : '';
    var email = emailEl ? emailEl.value.trim() : '';

    if (!fullName) {
        errors.push(msgFullName);
        fieldErrors.fullName = msgFullName;
    }

    if (!phone) {
        errors.push(msgPhoneRequired);
        fieldErrors.phone = msgPhoneRequired;
    } else {
        var phoneDigits = phone.replace(/[\s\-\.\(\)]/g, '');
        if (!/^\+?\d+$/.test(phoneDigits)) {
            errors.push(msgPhoneNumeric);
            fieldErrors.phone = msgPhoneNumeric;
        }
    }

    if (!email) {
        errors.push(msgEmailRequired);
        fieldErrors.email = msgEmailRequired;
    } else {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            errors.push(msgEmailInvalid);
            fieldErrors.email = msgEmailInvalid;
        }
    }

    return {
        valid: errors.length === 0,
        errors: errors,
        fieldErrors: fieldErrors
    };
}
