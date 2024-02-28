/**
 * @file Manages all events linked to the log list page.
 */

// Inits the Table class, which manages the log table (add/updates/removes filters).
const table = new Table();


// ####################
// TABLE FILTER: SEARCH
// ####################

const
    searchEl = document.querySelector("#search"),
    clearSearchEl = document.querySelector("#clearSearch");

/**
 * Sets the value saved in `localStorage` to the input and the table filters.
 */
const search = App.getUrlParam("search")
searchEl.value = search;
table.setFilter("search", search);

/**
 * On "SEARCH" <input> input:
 * - Removes all the potential <mark>.
 * - Saves the `search` value in the URL parameters.
 * - Saves the `search` value in the table filter.
 * - Updates the table.
 */
searchEl.addEventListener("input", () => {
    const search = searchEl.value.trim() || null;
    table.mark.unmark();
    App.setUrlParam("search", search);
    table.setFilter("search", search);
    table.update();
});

/**
 * On "CLEAR SEARCH" <button> click:
 * - Clears the search <input>.
 * - Removes all the potential <mark>.
 * - Clears the `search` value from the URL parameters.
 * - Clears the `search` value from the table filter.
 * - Updates the table.
 */
clearSearchEl.addEventListener("click", () => {
    searchEl.value = "";
    table.mark.unmark();
    App.setUrlParam("search", null);
    table.setFilter("search", null);
    table.update();
});


// ##################
// TABLE FILTER: DATE
// ##################

const dateInputEl = document.querySelector(".js-date-input");

/**
 * On "DATE" <input> change:
 * - Add/updates the `date` GET parameter.
 * - Reloads the page (which will load with the new date, given in the URL).
 */
dateInputEl.addEventListener("change", (e) => {
    const date = e.currentTarget.value;
    App.setUrlParam("date", date);
    location.reload();
});


// ####################
// TABLE FILTER: ORIGIN
// ####################

let preCheckedOriginList = [];

const originListFromUrl = App.getUrlParam("originList");
if (originListFromUrl) {
    preCheckedOriginList = originListFromUrl.split(",");
    table.setFilter("originList", preCheckedOriginList);
}

// For all ORIGIN buttons.
for (const originBtn of document.querySelectorAll(".js-origin-filter input")) {

    // If some ORIGIN were saved in the URL.
    if (preCheckedOriginList.length) {
        // Checks/unchecks the origin button.
        originBtn.checked = preCheckedOriginList.includes(originBtn.value);
    }

    /**
     * On "ORIGIN BUTTON" CLICK
     */
    originBtn.addEventListener("click", (e) => {

        let selectedOriginList = [];
        
        // For all ORIGIN buttons.
        for (const inputEl of document.querySelectorAll(".js-origin-filter input")) {
            inputEl.checked && selectedOriginList.push(inputEl.value);
        }

        if (selectedOriginList.length) {
            // Updates the `originList` table filter.
            table.setFilter("originList", selectedOriginList);
            // Updates the URL parameters.
            App.setUrlParam("originList", selectedOriginList.join(","));
        } else {
            // Updates the `originList` table filter.
            table.setFilter("originList", null);
            // Updates the URL parameters.
            App.setUrlParam("originList", null);
        }

        // Updates the table.
        table.update();
    });
}


// ######################
// TABLE FILTER: SWITCHES
// ######################

// For each <input> switches which are used to filter some table values.
// Switches: `dateTimeUtc`, `dateTimeInstance`, `dateTimeLocal`
for (const switchEl of document.querySelectorAll(`.js-switch-filter`)) {

    // Since the page just loaded, we'll try to get previous filter values from the `localStorage`.
    let isChecked = App.getData(`logListTable_${switchEl.id}`);

    // If there was a previous value (it's a boolean), we'll update the switch.
    // Otherwise, it'll just take its default HTML value.
    if (typeof isChecked === "boolean") {
        switchEl.checked = isChecked;
    }

    // Sets it as a table filter.
    table.setFilter(switchEl.id, switchEl.checked);

    /**
     * On `DateTime` SWITCH click:
     * - Saves its value.
     * - Sets it a a table filter.
     * - Updates the table.
     */
    switchEl.addEventListener("click", (e) => {
        App.setData(`logListTable_${switchEl.id}`, switchEl.checked);
        table.setFilter(switchEl.id, switchEl.checked);
        table.update();
    });
}


// #################################################
// AFTER ALL FILTERS ARE INITIATED, UPDATE THE TABLE
// #################################################

table.update();


// #################################
// TABLE TR EXPAND/COLLAPSE ON CLICK
// #################################

// For each lines in the table.
for (const logEl of table.rowList) {
    /**
     * On TABLE LINE click:
     * - Opens/closes the line's content.
     * 
     * Has 3 listeners to prevent opening/closing when when mouse down + drag, so the user can select a cell's content.
     */

    let isDragging = false,
        hasSelection = false;

    logEl.addEventListener('mousedown', () => isDragging = false);
    logEl.addEventListener('mousemove', () => isDragging = true);
    logEl.addEventListener('mouseup', (e) => {
        // Opens the <tr> only if the user was not dragging its mouse and does not have a selection.
        if (!isDragging && !hasSelection) {
            e.currentTarget.classList.toggle("opened");
        }
        hasSelection = !!getSelection().toString();
    });
}


// #########################################
// TABLE SETTINGS: LINE CLAMP WHEN COLLAPSED
// #########################################

const lineClampSelector = "table.log-list tr td span";

/**
 * Sets the new line clamp value in the:
 * - Main stylesheet
 * - App's setting (in local storage).
 * - Line clamp's input.
 * @param {integer} value Line clamp integer.
 */
const setLineClampValue = (value) => {
    // Updates the main stylesheet: sets the new value in the CSS rule.
    updateCss("less:static-css-style", lineClampSelector, "webkitLineClamp", value);
    // Saves its value.
    App.setData("logListTable_lineClamp", value);
    // Sets that value to the input.
    const lineClampInputEl = document.querySelector("#lineClamp");
    lineClampInputEl.value = value;
}

// Tries to get the `lineClamp` value from localStorage.
let lineClampValue = App.getData("logListTable_lineClamp");

// If no value was saved in the app's local storage, gets its default value from the stylesheet.
if (!lineClampValue) {
    lineClampValue = getCssValue("less:static-css-style", lineClampSelector, "webkitLineClamp");
}

// Updates its value.
setLineClampValue(lineClampValue);

/**
 * On LINE CLAMP <input> change:
 */
document.querySelector("#lineClamp").addEventListener("change", (e) => {
    setLineClampValue(e.currentTarget.value);
});


// #########################
// TABLE SETTINGS: FONT SIZE
// #########################

const fontSizeSelector = "table.log-list";

/**
 * Sets the new font size value in the:
 * - Main stylesheet
 * - App's setting (in local storage).
 * - Font size's input.
 * @param {integer} value Font size integer.
 */
const setFontSize = (value) => {
    // Updates the main stylesheet: sets the new value in the CSS rule.
    updateCss("less:static-css-style", fontSizeSelector, "fontSize", `${value}px`);
    // Saves its value.
    App.setData("logListTable_fontSize", value);
    // Sets that value to the input.
    const fontSizeInputEl = document.querySelector("#fontSize");
    fontSizeInputEl.value = value;
}

// Tries to get the `fontSize` value from localStorage.
let fontSizeValue = App.getData("logListTable_fontSize");

// If no value was saved in the `settings`, it gets its default value from the stylesheet.
if (!fontSizeValue) {
    fontSizeValue = getCssValue("less:static-css-style", fontSizeSelector, "fontSize");
    // Removes `px` from the `14px` string.
    fontSizeValue = fontSizeValue.substring(0, fontSizeValue.length - 2);
}

// Updates its value.
setFontSize(fontSizeValue);

/**
 * On FONT SIZE <input> change:
 */
document.querySelector("#fontSize").addEventListener("change", (e) => {
    setFontSize(e.currentTarget.value);
});