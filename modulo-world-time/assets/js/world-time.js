(function () {
    'use strict';

    const timeFormatters = new Map();
    const dateFormatters = new Map();

    function getFormatter(cache, timezone, options) {
        const key = timezone + '|' + JSON.stringify(options);
        if (!cache.has(key)) {
            cache.set(key, new Intl.DateTimeFormat('es-MX', Object.assign({ timeZone: timezone }, options)));
        }
        return cache.get(key);
    }

    function updateClock(clock, now) {
        const timezone = clock.getAttribute('data-timezone');
        if (!timezone) return;

        try {
            const timeOptions = { hour: '2-digit', minute: '2-digit', hour12: true };
            const dateOptions = { day: '2-digit', month: 'short' };
            const timeText = getFormatter(timeFormatters, timezone, timeOptions).format(now)
                .replace(/\s*a\.\s*m\./i, ' a. m.')
                .replace(/\s*p\.\s*m\./i, ' p. m.');
            const dateText = getFormatter(dateFormatters, timezone, dateOptions).format(now).replace(/\.$/, '');
            const previous = clock.getAttribute('data-last-time');

            clock.textContent = timeText;
            clock.setAttribute('data-last-time', timeText);

            const date = clock.parentElement.querySelector('.wt-date-display');
            if (date) date.textContent = dateText;

            if (previous && previous !== timeText) {
                clock.classList.remove('wt-minute-flash');
                // Reinicia la animación para que el destello ocurra en cada cambio de minuto.
                void clock.offsetWidth;
                clock.classList.add('wt-minute-flash');
            }
        } catch (error) {
            clock.textContent = '--:--';
            const date = clock.parentElement.querySelector('.wt-date-display');
            if (date) date.textContent = '--';
            if (!clock.hasAttribute('data-timezone-error')) {
                console.warn('El Ambientalista Post: zona horaria no válida.', timezone);
                clock.setAttribute('data-timezone-error', '1');
            }
        }
    }

    function updateAllClocks(root) {
        (root || document).querySelectorAll('.wt-time-display[data-timezone]').forEach(function (clock) {
            updateClock(clock, new Date());
        });
    }

    function init() {
        updateAllClocks(document);
        window.setInterval(function () { updateAllClocks(document); }, 1000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }

    // Elementor puede insertar o volver a renderizar el widget sin recargar la página.
    document.addEventListener('elementor/frontend/init', function () {
        if (window.elementorFrontend && window.elementorFrontend.hooks) {
            window.elementorFrontend.hooks.addAction('frontend/element_ready/world_time_widget.default', function ($scope) {
                updateAllClocks($scope && $scope[0] ? $scope[0] : document);
            });
        }
    });
})();
