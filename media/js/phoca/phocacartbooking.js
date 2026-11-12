/*
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/**
 * Get translated string loaded from PHP by Text::script() (Joomla.Text).
 * If the string is not loaded, the language constant itself is returned (easy to spot).
 */
function phBookingText(key) {
    if (typeof Joomla !== 'undefined' && Joomla.Text && typeof Joomla.Text._ === 'function') {
        var t = Joomla.Text._(key);
        if (typeof t === 'string' && t !== '') {
            return t;
        }
    }
    return key;
}

function phBookingMonthNames() {
    return [
        'JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE',
        'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'
    ].map(phBookingText);
}

function phBookingWeekdayNames(short) {
    var keys = short
        ? ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN']
        : ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY'];
    return keys.map(phBookingText);
}

document.addEventListener('DOMContentLoaded', function () {
    phInitBookingSlotPickers();
    phInitBookingDateRangePickers();
    phInitAdminBookingCalendar();
});

/**
 * Initialize time slot pickers
 */
function phInitBookingSlotPickers() {
    var pickers = document.querySelectorAll('.ph-booking-slot-picker');
    pickers.forEach(function (picker) {
        var pid = picker.dataset.productId;
        var dateStart = document.getElementById('phBookingDateStart' + pid);
        var dateEnd   = document.getElementById('phBookingDateEnd' + pid);
        var timeStart = document.getElementById('phBookingTimeStart' + pid);
        var timeEnd   = document.getElementById('phBookingTimeEnd' + pid);

        var radios = picker.querySelectorAll('input[name="booking_slot_id"]');
        radios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                if (dateStart) dateStart.value = this.dataset.date || '';
                if (dateEnd)   dateEnd.value   = this.dataset.date || '';
                if (timeStart) timeStart.value = this.dataset.timeStart || '';
                if (timeEnd)   timeEnd.value   = this.dataset.timeEnd || '';
            });
        });
    });
}

/**
 * Initialize date range pickers with visual interactive calendar
 */
function phInitBookingDateRangePickers() {
    var pickers = document.querySelectorAll('.ph-booking-daterange-picker');
    pickers.forEach(function (picker) {
        var pid         = picker.dataset.productId;
        var startInput  = document.getElementById('phDateStart' + pid);
        var endInput    = document.getElementById('phDateEnd' + pid);
        var nightsEl    = document.getElementById('phBookingNights' + pid);
        var unavailEl   = document.getElementById('phBookingUnavailable' + pid);
        var calendarBox = document.getElementById('phBookingCalendarBox' + pid);
        var bookedJson  = picker.dataset.bookedDates || '[]';
        var minDateStr  = picker.dataset.minDate || '';
        var maxDateStr  = picker.dataset.maxDate || '';

        var bookedDates = [];
        try {
            bookedDates = JSON.parse(bookedJson);
        } catch (e) {
            bookedDates = [];
        }

        var textNight   = phBookingText('COM_PHOCACART_BOOKING_NIGHT');
        var textNights  = phBookingText('COM_PHOCACART_BOOKING_NIGHTS');
        var textBlocked = phBookingText('COM_PHOCACART_BOOKING_RANGE_CONTAINS_BOOKED');

        function isBooked(dateStr) {
            return bookedDates.indexOf(dateStr) !== -1;
        }

        function hasBookedDateInRange(start, end) {
            for (var i = 0; i < bookedDates.length; i++) {
                if (bookedDates[i] >= start && bookedDates[i] < end) {
                    return true;
                }
            }
            return false;
        }

        function daysBetween(a, b) {
            return Math.round((new Date(b) - new Date(a)) / 86400000);
        }

        function formatDate(d) {
            var month = '' + (d.getMonth() + 1);
            var day   = '' + d.getDate();
            var year  = d.getFullYear();
            if (month.length < 2) month = '0' + month;
            if (day.length < 2)   day   = '0' + day;
            return [year, month, day].join('-');
        }

        // Calendar state
        var viewDate = minDateStr ? new Date(minDateStr) : new Date();
        viewDate.setDate(1); // 1st of month

        function resetSelection() {
            if (startInput) startInput.value = '';
            if (endInput) {
                endInput.value = '';
                if (minDateStr) {
                    endInput.min = minDateStr;
                } else {
                    endInput.removeAttribute('min');
                }
            }
            if (unavailEl) {
                unavailEl.textContent = '';
                unavailEl.style.display = 'none';
            }
            if (nightsEl) {
                nightsEl.style.display = 'none';
            }
            if (startInput) startInput.classList.remove('ph-invalid');
            if (endInput)   endInput.classList.remove('ph-invalid');

            // Reset quantity in add-to-cart form to 1
            var form = picker.closest('form') || document.querySelector('form.phItemCartBoxForm');
            var qtyInput = form ? form.querySelector('input.ph-input-quantity') : document.querySelector('input.ph-input-quantity');
            if (qtyInput) {
                qtyInput.value = 1;
                qtyInput.dispatchEvent(new Event('change', { bubbles: true }));
                qtyInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }

        function renderCalendar() {
            if (!calendarBox) return;

            calendarBox.innerHTML = '';

            var year  = viewDate.getFullYear();
            var month = viewDate.getMonth();

            var monthNames = phBookingMonthNames();

            // Calendar Header
            var header = document.createElement('div');
            header.className = 'ph-cal-header';

            var prevBtn = document.createElement('button');
            prevBtn.type = 'button';
            prevBtn.className = 'ph-cal-nav ph-cal-prev';
            prevBtn.innerHTML = '‹';
            prevBtn.title = phBookingText('COM_PHOCACART_BOOKING_PREV_MONTH');
            prevBtn.addEventListener('click', function (e) {
                e.preventDefault();
                viewDate.setMonth(viewDate.getMonth() - 1);
                renderCalendar();
            });

            var titleBox = document.createElement('div');
            titleBox.className = 'ph-cal-title-box';

            var title = document.createElement('span');
            title.className = 'ph-cal-title';
            title.textContent = monthNames[month] + ' ' + year;
            titleBox.appendChild(title);

            // Reset button in header if selection exists
            var startValNow = startInput ? startInput.value : '';
            var endValNow   = endInput   ? endInput.value   : '';
            if (startValNow || endValNow) {
                var resetBtn = document.createElement('button');
                resetBtn.type = 'button';
                resetBtn.className = 'ph-cal-reset-btn';
                resetBtn.textContent = '✕ ' + phBookingText('COM_PHOCACART_RESET');
                resetBtn.title = phBookingText('COM_PHOCACART_BOOKING_CLEAR_SELECTED_DATES');
                resetBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    resetSelection();
                    renderCalendar();
                });
                titleBox.appendChild(resetBtn);
            }

            var nextBtn = document.createElement('button');
            nextBtn.type = 'button';
            nextBtn.className = 'ph-cal-nav ph-cal-next';
            nextBtn.innerHTML = '›';
            nextBtn.title = phBookingText('COM_PHOCACART_BOOKING_NEXT_MONTH');
            nextBtn.addEventListener('click', function (e) {
                e.preventDefault();
                viewDate.setMonth(viewDate.getMonth() + 1);
                renderCalendar();
            });

            header.appendChild(prevBtn);
            header.appendChild(titleBox);
            header.appendChild(nextBtn);
            calendarBox.appendChild(header);

            // Weekday labels
            var weekdays = phBookingWeekdayNames(true);
            var weekRow = document.createElement('div');
            weekRow.className = 'ph-cal-weekdays';
            weekdays.forEach(function (wd) {
                var cell = document.createElement('div');
                cell.className = 'ph-cal-weekday';
                cell.textContent = wd;
                weekRow.appendChild(cell);
            });
            calendarBox.appendChild(weekRow);

            // Days grid
            var daysGrid = document.createElement('div');
            daysGrid.className = 'ph-cal-days';

            var firstDayIndex = new Date(year, month, 1).getDay();
            // Convert Sunday (0) to 7 so Monday is 1
            firstDayIndex = (firstDayIndex === 0) ? 6 : firstDayIndex - 1;

            var totalDays = new Date(year, month + 1, 0).getDate();

            // Leading empty cells
            for (var i = 0; i < firstDayIndex; i++) {
                var emptyCell = document.createElement('div');
                emptyCell.className = 'ph-cal-cell ph-cal-empty';
                daysGrid.appendChild(emptyCell);
            }

            var startVal = startInput ? startInput.value : '';
            var endVal   = endInput   ? endInput.value   : '';

            for (var day = 1; day <= totalDays; day++) {
                var cellDate = new Date(year, month, day);
                var cellDateStr = formatDate(cellDate);

                var cell = document.createElement('div');
                cell.className = 'ph-cal-cell ph-cal-day';
                cell.textContent = day;
                cell.dataset.date = cellDateStr;

                var isPast = minDateStr && (cellDateStr < minDateStr);
                var isFuture = maxDateStr && (cellDateStr > maxDateStr);
                var isOccupied = isBooked(cellDateStr);

                if (isPast || isFuture) {
                    cell.classList.add('ph-cal-disabled', 'ph-cal-out-of-range');
                } else if (isOccupied) {
                    cell.classList.add('ph-cal-booked');
                    cell.title = phBookingText('COM_PHOCACART_BOOKING_OCCUPIED');
                } else {
                    cell.classList.add('ph-cal-available');

                    // Check selection states
                    if (startVal && cellDateStr === startVal) {
                        cell.classList.add('ph-cal-selected-start');
                        cell.title = phBookingText('COM_PHOCACART_BOOKING_SELECTED_CHECKIN_DESELECT');
                    }
                    if (endVal && cellDateStr === endVal) {
                        cell.classList.add('ph-cal-selected-end');
                        cell.title = phBookingText('COM_PHOCACART_BOOKING_SELECTED_CHECKOUT_DESELECT');
                    }
                    if (startVal && endVal && cellDateStr > startVal && cellDateStr < endVal) {
                        cell.classList.add('ph-cal-in-range');
                    }

                    // Click handler
                    cell.addEventListener('click', handleDateClick);
                }

                daysGrid.appendChild(cell);
            }

            calendarBox.appendChild(daysGrid);
        }

        function handleDateClick(e) {
            var clickedDate = e.currentTarget.dataset.date;
            if (!clickedDate) return;

            var currentStart = startInput ? startInput.value : '';
            var currentEnd   = endInput   ? endInput.value   : '';

            // 1. Clicking an already selected date deselects/resets selection
            if (clickedDate === currentStart || clickedDate === currentEnd) {
                resetSelection();
                renderCalendar();
                return;
            }

            // 2. Selecting Check-in vs Check-out
            if (!currentStart || (currentStart && currentEnd)) {
                // Selecting Check-in date
                startInput.value = clickedDate;
                if (endInput) {
                    endInput.value = '';
                    endInput.min = new Date(new Date(clickedDate).getTime() + 86400000).toISOString().slice(0, 10);
                }
            } else {
                // Selecting Check-out date
                if (clickedDate < currentStart) {
                    // Clicked date is before current start -> restart with new start date
                    startInput.value = clickedDate;
                    if (endInput) endInput.value = '';
                } else {
                    // Check if range contains booked dates
                    if (hasBookedDateInRange(currentStart, clickedDate)) {
                        if (unavailEl) {
                            unavailEl.textContent = textBlocked;
                            unavailEl.style.display = 'block';
                        }
                        // Allow user to switch start date to clicked date instead of getting stuck
                        startInput.value = clickedDate;
                        if (endInput) endInput.value = '';
                        validate();
                        renderCalendar();
                        return;
                    }
                    endInput.value = clickedDate;
                }
            }

            validate();
            renderCalendar();
        }

        function validate() {
            if (!startInput || !endInput) return;

            var start = startInput.value;
            var end   = endInput.value;

            // Locate quantity input in the add-to-cart form
            var form = picker.closest('form') || document.querySelector('form.phItemCartBoxForm');
            var qtyInput = form ? form.querySelector('input.ph-input-quantity') : document.querySelector('input.ph-input-quantity');

            startInput.classList.remove('ph-invalid');
            endInput.classList.remove('ph-invalid');
            if (nightsEl)  nightsEl.style.display  = 'none';
            if (unavailEl) unavailEl.style.display = 'none';

            if (!start || !end) {
                if (qtyInput) {
                    qtyInput.value = 1;
                }
                return;
            }

            if (end <= start) {
                endInput.classList.add('ph-invalid');
                return;
            }

            var nights = daysBetween(start, end);

            if (hasBookedDateInRange(start, end)) {
                startInput.classList.add('ph-invalid');
                endInput.classList.add('ph-invalid');
                if (unavailEl) {
                    unavailEl.textContent = textBlocked;
                    unavailEl.style.display = 'block';
                }
                return;
            }

            // Automatically compute and fill quantity based on selected date range (nights)
            if (qtyInput) {
                qtyInput.value = nights;
                qtyInput.dispatchEvent(new Event('change', { bubbles: true }));
                qtyInput.dispatchEvent(new Event('input', { bubbles: true }));
            }

            if (nightsEl) {
                var nightWord = nights === 1 ? textNight : textNights;
                var labelEl = nightsEl.querySelector('.ph-booking-nights-label');
                if (labelEl) {
                    labelEl.textContent = nights + ' ' + nightWord + ' (' + start + ' → ' + end + ')';
                }
                nightsEl.style.display = 'block';
            }

            // Keep checkout min date as day after check-in
            endInput.min = new Date(new Date(start).getTime() + 86400000).toISOString().slice(0, 10);
        }

        if (startInput) {
            startInput.addEventListener('change', function () {
                validate();
                renderCalendar();
            });
        }
        if (endInput) {
            endInput.addEventListener('change', function () {
                validate();
                renderCalendar();
            });
        }

        // Initial calendar render
        renderCalendar();
        validate();
    });
}

/**
 * Initialize backend interactive booking calendar
 */
function phInitAdminBookingCalendar() {
    var calEl = document.getElementById('phAdminBookingCalendar');
    if (!calEl) return;

    var rawBookings = calEl.dataset.bookings || '[]';
    var bookings = [];
    try {
        bookings = JSON.parse(rawBookings);
    } catch (e) {
        bookings = [];
    }

    var textDep   = phBookingText('COM_PHOCACART_BOOKING_DEPARTURE');
    var textArr   = phBookingText('COM_PHOCACART_BOOKING_ARRIVAL');
    var textToday = phBookingText('COM_PHOCACART_BOOKING_TODAY');

    var viewDate = new Date();
    viewDate.setDate(1);

    function formatDate(d) {
        var month = '' + (d.getMonth() + 1);
        var day   = '' + d.getDate();
        var year  = d.getFullYear();
        if (month.length < 2) month = '0' + month;
        if (day.length < 2)   day   = '0' + day;
        return [year, month, day].join('-');
    }

    var todayStr = formatDate(new Date());

    function getBookingColor(b) {
        var id = parseInt(b.id, 10) || 1;
        var uid = parseInt(b.user_id, 10) || 1;
        var hue = Math.abs((id * 137.5 + uid * 45) % 360);
        return {
            bg: 'hsl(' + hue + ', 65%, 42%)',
            border: 'hsl(' + hue + ', 70%, 32%)',
            text: '#ffffff'
        };
    }

    function renderAdminCalendar() {
        calEl.innerHTML = '';

        var year  = viewDate.getFullYear();
        var month = viewDate.getMonth();

        var monthNames = phBookingMonthNames();

        // Header
        var header = document.createElement('div');
        header.className = 'ph-admin-cal-header d-flex justify-content-between align-items-center mb-3';

        var navLeft = document.createElement('div');
        navLeft.className = 'btn-group';

        var prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.className = 'btn btn-outline-secondary btn-sm';
        prevBtn.innerHTML = '‹';
        prevBtn.title = phBookingText('COM_PHOCACART_BOOKING_PREV_MONTH');
        prevBtn.addEventListener('click', function () {
            viewDate.setMonth(viewDate.getMonth() - 1);
            renderAdminCalendar();
        });

        var nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.className = 'btn btn-outline-secondary btn-sm';
        nextBtn.innerHTML = '›';
        nextBtn.title = phBookingText('COM_PHOCACART_BOOKING_NEXT_MONTH');
        nextBtn.addEventListener('click', function () {
            viewDate.setMonth(viewDate.getMonth() + 1);
            renderAdminCalendar();
        });

        var todayBtn = document.createElement('button');
        todayBtn.type = 'button';
        todayBtn.className = 'btn btn-outline-primary btn-sm ms-2';
        todayBtn.textContent = textToday;
        todayBtn.addEventListener('click', function () {
            viewDate = new Date();
            viewDate.setDate(1);
            renderAdminCalendar();
        });

        navLeft.appendChild(prevBtn);
        navLeft.appendChild(nextBtn);
        navLeft.appendChild(todayBtn);

        var title = document.createElement('h5');
        title.className = 'mb-0 fw-bold ph-admin-cal-title';
        title.textContent = monthNames[month] + ' ' + year;

        header.appendChild(navLeft);
        header.appendChild(title);
        calEl.appendChild(header);

        // Weekdays
        var weekdays = phBookingWeekdayNames(false);
        var weekRow = document.createElement('div');
        weekRow.className = 'ph-admin-cal-weekdays d-grid';
        weekRow.style.gridTemplateColumns = 'repeat(7, 1fr)';
        weekdays.forEach(function (wd) {
            var wEl = document.createElement('div');
            wEl.className = 'text-center fw-bold text-muted p-2 border-bottom small';
            wEl.textContent = wd;
            weekRow.appendChild(wEl);
        });
        calEl.appendChild(weekRow);

        // Days Grid
        var daysGrid = document.createElement('div');
        daysGrid.className = 'ph-admin-cal-grid d-grid';
        daysGrid.style.gridTemplateColumns = 'repeat(7, 1fr)';

        var firstDayIndex = new Date(year, month, 1).getDay();
        firstDayIndex = (firstDayIndex === 0) ? 6 : firstDayIndex - 1;
        var totalDays = new Date(year, month + 1, 0).getDate();

        // Empty cells for month start offset
        for (var i = 0; i < firstDayIndex; i++) {
            var emptyCell = document.createElement('div');
            emptyCell.className = 'ph-admin-cal-cell ph-admin-cal-empty p-2 border bg-body-tertiary text-muted opacity-50';
            daysGrid.appendChild(emptyCell);
        }

        for (var day = 1; day <= totalDays; day++) {
            var cellDate = new Date(year, month, day);
            var dateStr  = formatDate(cellDate);

            var cell = document.createElement('div');
            cell.className = 'ph-admin-cal-cell p-2 border position-relative';
            if (dateStr === todayStr) {
                cell.classList.add('ph-admin-cal-today');
            }

            var dayNum = document.createElement('div');
            dayNum.className = 'ph-admin-cal-daynum fw-bold small mb-1';
            dayNum.textContent = day;
            cell.appendChild(dayNum);

            // Find bookings covering this date
            var arrivals   = [];
            var departures = [];
            var ongoing    = [];

            bookings.forEach(function (b) {
                var bStart = b.booking_date_start;
                var bEnd   = b.booking_date_end;
                var mode   = parseInt(b.booking_mode, 10);

                if (mode === 2) {
                    // Date range mode (rentals/hotel stays)
                    if (bStart === dateStr && bEnd === dateStr) {
                        arrivals.push(b);
                    } else if (bStart === dateStr) {
                        arrivals.push(b);
                    } else if (bEnd === dateStr) {
                        departures.push(b);
                    } else if (bStart < dateStr && bEnd > dateStr) {
                        ongoing.push(b);
                    }
                } else {
                    // Time slot mode
                    if (bStart === dateStr) {
                        ongoing.push(b);
                    }
                }
            });

            // Handle Turnover Days (departures + arrivals on the same day)
            var isTurnover = (departures.length > 0 && arrivals.length > 0);
            if (isTurnover) {
                cell.classList.add('ph-admin-turnover-day');
                var turnoverBadge = document.createElement('span');
                turnoverBadge.className = 'badge ph-turnover-badge position-absolute top-0 end-0 m-1';
                turnoverBadge.textContent = '⇄ ' + phBookingText('COM_PHOCACART_BOOKING_TURNOVER');
                turnoverBadge.title = phBookingText('COM_PHOCACART_BOOKING_TURNOVER_DAY_DESC');
                cell.appendChild(turnoverBadge);
            }

            var eventsContainer = document.createElement('div');
            eventsContainer.className = 'ph-admin-cal-events d-flex flex-column gap-1';

            // 1. Departures
            departures.forEach(function (b) {
                var pill = createBookingPill(b, 'departure', textDep);
                eventsContainer.appendChild(pill);
            });

            // 2. Arrivals
            arrivals.forEach(function (b) {
                var pill = createBookingPill(b, 'arrival', textArr);
                eventsContainer.appendChild(pill);
            });

            // 3. Ongoing / Time slot
            ongoing.forEach(function (b) {
                var pill = createBookingPill(b, 'stay', '');
                eventsContainer.appendChild(pill);
            });

            cell.appendChild(eventsContainer);
            daysGrid.appendChild(cell);
        }

        calEl.appendChild(daysGrid);
    }

    function createBookingPill(b, type, label) {
        var wrapper = document.createElement('div');
        wrapper.className = 'ph-admin-booking-pill-wrapper';

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'ph-admin-booking-pill text-decoration-none rounded px-1 py-1 d-block text-truncate small w-100 border-0 text-start';

        var color = getBookingColor(b);
        btn.style.backgroundColor = color.bg;
        btn.style.color           = color.text;
        btn.style.borderLeft      = '4px solid ' + color.border;

        if (type === 'departure') {
            btn.classList.add('ph-pill-departure');
        } else if (type === 'arrival') {
            btn.classList.add('ph-pill-arrival');
        }

        var timeInfo = '';
        if (b.booking_time_start) {
            timeInfo = ' (' + b.booking_time_start.slice(0, 5) + (b.booking_time_end ? '-' + b.booking_time_end.slice(0, 5) : '') + ')';
        }

        var userName = b.user_name || phBookingText('COM_PHOCACART_GUEST');
        var prefix = '';
        if (type === 'departure') prefix = '⇥ ';
        else if (type === 'arrival') prefix = '⇤ ';

        btn.textContent = prefix + '#' + b.id + ' ' + userName + timeInfo;

        btn.addEventListener('click', function(e) {
            e.preventDefault();
            openBookingModal(b);
        });

        wrapper.appendChild(btn);
        return wrapper;
    }

    function openBookingModal(b) {
        var calEl = document.getElementById('phAdminBookingCalendar');
        var t = {
            bookingDetails: calEl.dataset.textBookingDetails || 'Booking Details',
            order:          calEl.dataset.textOrder          || 'Order',
            customer:       calEl.dataset.textCustomer       || 'Customer',
            status:         calEl.dataset.textStatus         || 'Status',
            dates:          calEl.dataset.textDates          || 'Dates',
            time:           calEl.dataset.textTime           || 'Time',
            quantity:       calEl.dataset.textQuantity       || 'Quantity',
            notes:          calEl.dataset.textNotes          || 'Notes',
            edit:           calEl.dataset.textEdit           || 'Edit',
            close:          calEl.dataset.textClose          || 'Close',
            timeslot:       calEl.dataset.textTimeslot       || 'Time Slot',
            noTime:         calEl.dataset.textNoTime         || 'Date Range'
        };

        var statusColors = {1: 'success', 2: 'info', 3: 'danger', 4: 'primary', 5: 'warning', 6: 'secondary', 7: 'dark'};
        var statusNames  = {1: 'Confirmed', 2: 'Pending', 3: 'Cancelled', 4: 'Completed', 5: 'No-Show', 6: 'Pend. Payment', 7: 'Refunded'};
        var statusBadgeClass = 'bg-' + (statusColors[b.status] || 'secondary');
        var statusName       = statusNames[b.status] || ('Status ' + b.status);

        var dateRange = b.booking_date_start + ' \u2192 ' + (b.booking_date_end || b.booking_date_start);
        var timeRange = '';
        if (b.booking_time_start) {
            timeRange = b.booking_time_start.slice(0, 5);
            if (b.booking_time_end) timeRange += ' - ' + b.booking_time_end.slice(0, 5);
        } else {
            timeRange = '<em>' + t.noTime + '</em>';
        }

        var html = '<table class="table table-bordered table-sm mb-0">';
        html += '<tbody>';
        html += '<tr><th style="width:35%">' + t.bookingDetails + ' #</th><td><strong>#' + b.id + '</strong></td></tr>';
        html += '<tr><th>' + t.status + '</th><td><span class="badge ' + statusBadgeClass + '">' + statusName + '</span></td></tr>';
        html += '<tr><th>' + t.customer + '</th><td>' + (b.user_name ? escHtml(b.user_name) : '&mdash;') + (b.user_email ? ' &lt;' + escHtml(b.user_email) + '&gt;' : '') + '</td></tr>';
        html += '<tr><th>' + t.order + '</th><td><a href="index.php?option=com_phocacart&task=phocacartorder.edit&id=' + b.order_id + '" target="_blank">#' + escHtml(b.order_number) + '</a></td></tr>';
        html += '<tr><th>Product</th><td>' + escHtml(b.product_title || '') + '</td></tr>';
        html += '<tr><th>' + t.dates + '</th><td>' + escHtml(dateRange) + '</td></tr>';
        html += '<tr><th>' + t.time + '</th><td>' + timeRange + '</td></tr>';
        html += '<tr><th>' + t.quantity + '</th><td>' + (b.quantity || 1) + '</td></tr>';
        if (b.notes) {
            html += '<tr><th>' + t.notes + '</th><td>' + escHtml(b.notes) + '</td></tr>';
        }
        html += '</tbody></table>';

        var modalBody = document.getElementById('phBookingDetailModalBody');
        if (modalBody) modalBody.innerHTML = html;

        var editBtn = document.getElementById('phBookingDetailEditBtn');
        if (editBtn) editBtn.href = 'index.php?option=com_phocacart&task=phocacartbooking.edit&id=' + b.id;

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var modalEl = document.getElementById('phBookingDetailModal');
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    renderAdminCalendar();
}
