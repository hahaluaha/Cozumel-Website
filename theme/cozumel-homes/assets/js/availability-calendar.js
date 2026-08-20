(function () {
    'use strict';

    var monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    var weekdayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

    function pad(n) { return n < 10 ? '0' + n : '' + n; }
    function toISO(date) { return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()); }
    function formatDisplay(iso) {
        var d = new Date(iso + 'T00:00:00');
        return monthNames[d.getMonth()].slice(0, 3) + ' ' + d.getDate() + ', ' + d.getFullYear();
    }

    function buildUnavailableSet(ranges) {
        var set = new Set();
        ranges.forEach(function (range) {
            var cur = new Date(range.start + 'T00:00:00');
            var end = new Date(range.end + 'T00:00:00');
            while (cur < end) {
                set.add(toISO(cur));
                cur.setDate(cur.getDate() + 1);
            }
        });
        return set;
    }

    function rangeIsClear(startISO, endISO, unavailableSet) {
        var cur = new Date(startISO + 'T00:00:00');
        var end = new Date(endISO + 'T00:00:00');
        while (cur < end) {
            if (unavailableSet.has(toISO(cur))) return false;
            cur.setDate(cur.getDate() + 1);
        }
        return true;
    }

    function renderMonth(container, year, month, unavailableSet, selection, minDate) {
        var first = new Date(year, month, 1);
        var daysInMonth = new Date(year, month + 1, 0).getDate();
        var startWeekday = first.getDay();

        var html = '<div class="availability-calendar__month">';
        html += '<h4>' + monthNames[month] + ' ' + year + '</h4>';
        html += '<div class="availability-calendar__weekdays">';
        weekdayNames.forEach(function (name) {
            html += '<span class="availability-calendar__weekday">' + name + '</span>';
        });
        html += '</div>';
        html += '<div class="availability-calendar__grid">';
        for (var i = 0; i < startWeekday; i++) {
            html += '<span class="availability-calendar__day availability-calendar__day--empty"></span>';
        }
        for (var d = 1; d <= daysInMonth; d++) {
            var date = new Date(year, month, d);
            var iso = toISO(date);
            var isBeforeMin = date < minDate;
            var isUnavailable = unavailableSet.has(iso);
            var classes = ['availability-calendar__day'];
            if (isBeforeMin || isUnavailable) classes.push('availability-calendar__day--unavailable');
            else classes.push('availability-calendar__day--available');
            if (selection.start === iso || selection.end === iso) classes.push('availability-calendar__day--selected');
            html += '<button type="button" class="' + classes.join(' ') + '" data-date="' + iso + '"' + (isBeforeMin || isUnavailable ? ' disabled' : '') + '>' + d + '</button>';
        }
        html += '</div></div>';
        container.innerHTML += html;
    }

    function init(root) {
        var apiUrl = root.getAttribute('data-api-url');
        var checkinTrigger = root.querySelector('[data-role="checkin"]');
        var checkoutTrigger = root.querySelector('[data-role="checkout"]');
        var checkinInput = root.querySelector('input[name="checkin_date"]');
        var checkoutInput = root.querySelector('input[name="checkout_date"]');
        var popover = root.querySelector('.availability-calendar-popover');
        var form = root.closest('form');
        var restOfForm = form ? form.querySelector('.inquiry-form__rest') : null;

        var selection = { start: null, end: null };
        var mode = null;
        var unavailableSet = new Set();
        var viewMonthOffset = 0;

        function today() {
            var t = new Date();
            t.setHours(0, 0, 0, 0);
            return t;
        }

        function minDateForMode() {
            if (mode === 'checkout' && selection.start) {
                var d = new Date(selection.start + 'T00:00:00');
                d.setDate(d.getDate() + 1);
                return d;
            }
            return today();
        }

        function redraw() {
            var grid = popover.querySelector('.availability-calendar__grid-wrap');
            grid.innerHTML = '';
            var base = today();
            var minDate = minDateForMode();
            for (var m = 0; m < 3; m++) {
                var view = new Date(base.getFullYear(), base.getMonth() + viewMonthOffset + m, 1);
                renderMonth(grid, view.getFullYear(), view.getMonth(), unavailableSet, selection, minDate);
            }
        }

        function openPopover(newMode) {
            if (newMode === 'checkout' && !selection.start) return;
            mode = newMode;
            if (mode === 'checkin') {
                selection = { start: null, end: null };
                checkinInput.value = '';
                checkinTrigger.textContent = 'Select date';
                checkoutInput.value = '';
                checkoutTrigger.textContent = 'Select date';
                checkoutTrigger.disabled = true;
                if (restOfForm) restOfForm.classList.add('is-hidden');
                viewMonthOffset = 0;
            } else {
                var t = today();
                var startDate = new Date(selection.start + 'T00:00:00');
                var monthDiff = (startDate.getFullYear() - t.getFullYear()) * 12 + (startDate.getMonth() - t.getMonth());
                viewMonthOffset = Math.max(0, monthDiff);
            }
            popover.classList.remove('is-hidden');
            redraw();
        }

        function closePopover() {
            popover.classList.add('is-hidden');
            mode = null;
        }

        function handlePick(iso) {
            if (mode === 'checkin') {
                selection.start = iso;
                selection.end = null;
                checkinInput.value = iso;
                checkinTrigger.textContent = formatDisplay(iso);
                checkoutTrigger.disabled = false;
                closePopover();
            } else if (mode === 'checkout') {
                if (iso <= selection.start || !rangeIsClear(selection.start, iso, unavailableSet)) return;
                selection.end = iso;
                checkoutInput.value = iso;
                checkoutTrigger.textContent = formatDisplay(iso);
                closePopover();
                if (restOfForm) restOfForm.classList.remove('is-hidden');
            }
        }

        popover.innerHTML =
            '<div class="availability-calendar__nav">' +
                '<button type="button" class="availability-calendar__nav-btn" data-nav="prev">‹</button>' +
                '<button type="button" class="availability-calendar__nav-btn" data-nav="next">›</button>' +
                '<button type="button" class="availability-calendar__close" data-nav="close">Close</button>' +
            '</div>' +
            '<div class="availability-calendar__grid-wrap"></div>';

        popover.addEventListener('click', function (e) {
            var dayBtn = e.target.closest('.availability-calendar__day--available, .availability-calendar__day--selected');
            if (dayBtn) { handlePick(dayBtn.getAttribute('data-date')); return; }
            var navBtn = e.target.closest('[data-nav]');
            if (!navBtn) return;
            var dir = navBtn.getAttribute('data-nav');
            if (dir === 'prev') viewMonthOffset = Math.max(0, viewMonthOffset - 1);
            else if (dir === 'next') viewMonthOffset += 1;
            else if (dir === 'close') { closePopover(); return; }
            redraw();
        });

        checkinTrigger.addEventListener('click', function () {
            if (!checkinTrigger.disabled) openPopover('checkin');
        });
        checkoutTrigger.addEventListener('click', function () {
            if (!checkoutTrigger.disabled) openPopover('checkout');
        });

        checkinTrigger.disabled = true;
        checkinTrigger.textContent = 'Loading dates…';

        fetch(apiUrl)
            .then(function (res) {
                if (!res.ok) throw new Error('Availability request failed with status ' + res.status);
                return res.json();
            })
            .then(function (ranges) {
                if (!Array.isArray(ranges)) throw new Error('Availability response was not an array');
                unavailableSet = buildUnavailableSet(ranges);
                checkinTrigger.disabled = false;
                checkinTrigger.textContent = 'Select date';
            })
            .catch(function (err) {
                console.error('Cozumel availability calendar: failed to load availability data.', err);
                [checkinTrigger, checkoutTrigger].forEach(function (trigger, i) {
                    var input = i === 0 ? checkinInput : checkoutInput;
                    var label = trigger.closest('label');
                    input.type = 'date';
                    input.disabled = false;
                    input.style.display = 'block';
                    input.style.marginTop = '4px';
                    if (label) {
                        input.setAttribute('aria-label', label.textContent.trim());
                        label.insertBefore(input, trigger);
                    }
                    trigger.remove();
                });
                if (restOfForm) restOfForm.classList.remove('is-hidden');
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var roots = document.querySelectorAll('[data-cozumel-availability-calendar]');
        roots.forEach(init);
    });
})();
