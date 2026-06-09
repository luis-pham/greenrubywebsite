(function () {
    var headers = document.querySelectorAll('#faq-category .faq-accordion-header');
    if (!headers.length) {
        return;
    }

    function setExpanded(header, isOpen) {
        header.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

    function closeAllItems() {
        document.querySelectorAll('#faq-category .faq-accordion-item').forEach(function (accordionItem) {
            accordionItem.classList.remove('expand');
            var accordionHeader = accordionItem.querySelector('.faq-accordion-header');
            if (accordionHeader) {
                setExpanded(accordionHeader, false);
            }
        });
    }

    function toggleItem(header) {
        var item = header.closest('.faq-accordion-item');
        if (!item) {
            return;
        }

        var wasOpen = item.classList.contains('expand');

        closeAllItems();

        if (!wasOpen) {
            item.classList.add('expand');
            setExpanded(header, true);
        }
    }

    headers.forEach(function (header) {
        header.addEventListener('click', function () {
            toggleItem(header);
        });

        header.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                toggleItem(header);
            }
        });
    });
})();
