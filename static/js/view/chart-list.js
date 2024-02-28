/**
 * @file Manages all events linked to chart list page.
 * - custom start/end date interval listeners
 * - instantiates all the Chart.js objects
 */

// #################################
// START/END DATE INTERVAL LISTENERS
// #################################

const startDateInputEl = document.querySelector("#startDate"),
      endDateInputEl = document.querySelector("#endDate");

for (const dateInputEl of [startDateInputEl, endDateInputEl]) {

    /**
     * On START/END DATE <input> CHANGE:
     * - Makes sure the days diff between the two dates is between 1 and 366 (inclusive)
     *   If not, updates one of the dates to +- 30 days.
     * - Updates the URL with the new custom date interval.
     * - Reloads the page.
     */
    dateInputEl.addEventListener("change", () => {

        let startDate = startDateInputEl.value,
            endDate = endDateInputEl.value;

        // Calculates the total of days between the two dates (since it's inclusive, we'll add 1 to take into account ending date).
        const dayDiff = Math.round((Date.parse(endDate) - Date.parse(startDate)) / (1000 * 60 * 60 * 24)) + 1;

        // If the difference is below 1 or above 366 (whole leap year).
        if (dayDiff < 1 || dayDiff > 366) {
            // If it's the start date which is not valid.
            if (dateInputEl.id === "startDate") {
                // Updates the end date to 30 days after the new start date.
                const newDate = new Date(startDate);
                newDate.setDate(newDate.getDate() + 30);
                endDateInputEl.value = newDate.toISOString().slice(0, 10);
            } // If it's the end date which is not valid.
            else {
                // Updates the start date to 30 days before the new end date.
                const newDate = new Date(endDate);
                newDate.setDate(newDate.getDate() - 30);
                startDateInputEl.value = newDate.toISOString().slice(0, 10);
            }
        }

        startDate = startDateInputEl.value;
        endDate = endDateInputEl.value;

        // Update the current URL with the new date.
        const url = new URLSearchParams(window.location.search);
        url.delete("dateFilter");
        url.set("startDate", startDate);
        url.set("endDate", endDate);

        showLoader(".js-chart-filter-list");

        // Reload the page with the new URL.
        window.location.href = `index.php?${url.toString()}`;
    });
}


// ######
// CHARTS
// ######


// ---------------------
// TOTAL LOGS PER ORIGIN
// ---------------------

const totalLogPerOrigin = g_chartList["totalLogPerOrigin"];

new Chart(document.getElementById('totalLogPerOrigin'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(totalLogPerOrigin).map(e => e.toUpperCase()),
        datasets: [{
            data: Object.values(totalLogPerOrigin).map(e => e.total),
            borderWidth: 1,
        }],
    },
});


// -------------------------------------------
// TOTAL LOGS + TOTAL LOGS PER ORIGIN per DATE
// -------------------------------------------

const logsPerOriginPerDate = g_chartList["logsPerOriginPerDate"];

new Chart(document.getElementById('logsPerOriginPerDate'), {
    type: 'line',
    data: {
        labels: Object.values(logsPerOriginPerDate).map(e => e.label),
        datasets:
            ["total", ...g_originList].map((origin) => {
                return {
                    label: origin === "total" ? "Total" : origin.toUpperCase(),
                    data: Object.values(logsPerOriginPerDate).map(e => e.datasets ? e.datasets[origin] : null),
                    borderWidth: 1,
                    tension: 0.3,
                }
            })
    },
});


// ----------------------------
// TOTAL LOGS PER HOUR IN A DAY
// ----------------------------

const logsPerHourInADay = g_chartList["logsPerHourInADay"];

new Chart(document.getElementById('logsPerHourInADay'), {
    type: 'line',
    data: {
        labels: logsPerHourInADay.map(row => `${row.hour}h`),
        datasets:
            ["total", ...g_originList].map((origin) => {
                return {
                    label: origin === "total" ? "Total" : origin.toUpperCase(),
                    data: logsPerHourInADay.map(row => row.datasetList[origin]),
                    borderWidth: 1,
                    tension: 0.3,
                }
            })
    },
});
