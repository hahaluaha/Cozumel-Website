(function () {
    'use strict';

    function pad(n) { return n < 10 ? '0' + n : '' + n; }
    function toISO(date) { return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()); }

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

    function renderMonth(container, year, month, unavailableSet, selection, onPick) {
        var monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        var first = new Date(year, month, 1);
        var daysInMonth = new Date(year, month + 1, 0).getDate();
        var startWeekday = first.getDay();

        var html = '<div class="availability-calendar__month">';
        html += '<h4>' + monthNames[month] + ' ' + year + '</h4>';
        html += '<div class="availability-calendar__grid">';
        for (var i = 0; i < startWeekday; i++) {
            html += '<span class="availability-calendar__day availability-calendar__day--empty"></span>';
        }
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        for (var d = 1; d <= daysInMonth; d++) {
            var date = new Date(year, month, d);
            var iso = toISO(date);
            var isPast = date < today;
            var isUnavailable = unavailableSet.has(iso);
            var classes = ['availability-calendar__day'];
            if (isPast || isUnavailable) classes.push('availability-calendar__day--unavailable');
            else classes.push('availability-calendar__day--available');
            if (selection.start === iso || selection.end === iso) classes.push('availability-calendar__day--selected');
            html += '<button type="button" class="' + classes.join(' ') + '" data-date="' + iso + '"' + (isPast || isUnavailable ? ' disabled' : '') + '>' + d + '</button>';
        }
        html += '</div></div>';
        container.innerHTML += html;
    }

    function init(root) {
        var propertyId = root.getAttribute('data-property-id');
        var apiUrl = root.getAttribute('data-api-url');
        var checkinInput = document.querySelector('[name="checkin_date"]');
        var checkoutInput = document.querySelector('[name="checkout_date"]');
        var selection = { start: null, end: null };

        fetch(apiUrl)
            .then(function (res) {
                if (!res.ok) throw new Error('Availability request failed with status ' + res.status);
                return res.json();
            })
            .then(function (ranges) {
                if (!Array.isArray(ranges)) throw new Error('Availability response was not an array');

                var unavailableSet = buildUnavailableSet(ranges);
                var now = new Date();
                var viewMonthOffset = 0;
                var grid = document.createElement('div');
                grid.className = 'availability-calendar';
                var nav = document.createElement('div');
                nav.className = 'availability-calendar__nav';
                nav.innerHTML =
                    '<button type="button" class="availability-calendar__nav-btn" data-nav="prev">‹</button>' +
                    '<button type="button" class="availability-calendar__nav-btn" data-nav="next">›</button>';
                root.appendChild(nav);
                root.appendChild(grid);

                function redraw() {
                    grid.innerHTML = '';
                    for (var m = 0; m < 3; m++) {
                        var view = new Date(now.getFullYear(), now.getMonth() + viewMonthOffset + m, 1);
                        renderMonth(grid, view.getFullYear(), view.getMonth(), unavailableSet, selection, handlePick);
                    }
                }

                function handlePick(iso) {
                    if (!selection.start || (selection.start && selection.end)) {
                        selection = { start: iso, end: null };
                    } else if (iso > selection.start) {
                        if (rangeIsClear(selection.start, iso, unavailableSet)) {
                            selection.end = iso;
                        } else {
                            selection = { start: iso, end: null };
                        }
                    } else {
                        selection = { start: iso, end: null };
                    }
                    if (checkinInput) checkinInput.value = selection.start || '';
                    if (checkoutInput) checkoutInput.value = selection.end || '';
                    redraw();
                }

                grid.addEventListener('click', function (e) {
                    var btn = e.target.closest('.availability-calendar__day--available, .availability-calendar__day--selected');
                    if (btn) handlePick(btn.getAttribute('data-date'));
                });

                nav.addEventListener('click', function (e) {
                    var btn = e.target.closest('.availability-calendar__nav-btn');
                    if (!btn) return;
                    var dir = btn.getAttribute('data-nav');
                    if (dir === 'prev') {
                        viewMonthOffset = Math.max(0, viewMonthOffset - 1);
                    } else if (dir === 'next') {
                        viewMonthOffset += 1;
                    }
                    redraw();
                });

                redraw();
            })
            .catch(function (err) {
                console.error('Cozumel availability calendar: failed to load availability data.', err);
                root.innerHTML = '<p class="availability-calendar__error">Availability calendar unavailable right now — please use the form below.</p>';
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var roots = document.querySelectorAll('[data-cozumel-availability-calendar]');
        roots.forEach(init);
    });
})();
