/**
 * Handles all JS errors.
 * @param {string} errorHandlerType Error handler type.
 * @param {ErrorEvent|PromiseRejectionEvent} event Event thrown from the `window.error` handler or `window.unhandledrejection` handler.
 * @param {string} event Error message.
 * @param {string} stack Error stack, can be null.
 */
const handleError = async (errorHandlerType, event, message, stack = null) => {

    try {

        hideLoader();

        // ### PARSES THE JS ERROR TRACE ### \\\

        let traceList = [];

        // Here, we'll try to parse the `error.stack` string to an organized and readable trace.
        try {
            // Splits the multiple lines into array elements.
            let errorStack = stack.split("\n");

            // Removes the first line, which is the error message.
            errorStack.shift();

            // For each trace elements.
            for (let line of errorStack) {

                const obj = {};

                // Removes the first 3 chars of each trace element, which is `at `.
                // I: `at async myFunction (https://example.com/my_file.js?ver=8734:4:28)`
                // O: `async myFunction (https://example.com/my_file.js?ver=8734:4:28)`
                const lineClean = line.trim().substring(3);

                // Splits the trace element:
                // I: "async myFunction (https://example.com/my_file.js?ver=8734:4:28)"
                // O: ["async", "myFunction", "(https://example.com/my_file.js?ver=8734:4:28)"]
                const lineSplit = lineClean.split(" ");

                // Gets the file (+ col & row), which is always the last part of the trace element.
                // I: ["async", "myFunction", "(https://example.com/my_file.js?ver=8734:4:28)"]
                // O: ["https:", "//example.com/my_file.js?ver=8734", "4", "28"]
                const fileRowColSplit = lineSplit.pop().slice(1, -1).split(":");

                // Extracts the file, line and column.
                obj.column = parseInt(fileRowColSplit.pop());
                obj.line = parseInt(fileRowColSplit.pop());
                obj.file = fileRowColSplit.join(":").match(/^.*\.js/)[0]; // Only gets the pure `https://site.com/file.js` without the GET parameters.

                // Gets the `element` (function or method or HTML element...) by joining the remaining of the array.
                // I:  ["async", "myFunction"]
                // O: "async myFunction"
                obj.element = lineSplit.join(" ");

                // Adds the trace.
                traceList.push(obj);
            }

        } catch (error) {
            console.warn("`handleError`: couldn't get error trace from the following error's stack.", error.stack);
        }

        // ### SHOWS AN ERROR TOAST ### \\\

        const log = {
            message,
        };

        if (traceList.length) {
            log.file = traceList[0].file;
            log.line = traceList[0].line;
            log.column = traceList[0].column;
            log.trace = traceList;
        }

        const logJson = JSON.stringify(log, null, 4);

        const toastContent = `
            <pre style="margin-bottom: 0 !important;">${logJson}</pre>
        `;

        new Toast()
            .toast(null, {class: "border-danger-subtle", style: {"--bs-toast-max-width": "500px"}})
            .header(null, {class: "bg-danger-subtle"})
            .title("An error happened")
            .content(toastContent)
            .timeout(60000) // 1 minute
            .display();

    } catch (error) {

        // If an error happens, we need to catch it and NOT throw it back, to prevent getting into an error catching + throwing loop.
        console.log("'handleError' function crash:", error);

    }

}

/**
 * Appends a loader to the given element (<body> by default).
 * @param {string|HTMLElement} [container="body"] Selector string or HTMLElement of the element to append the loader to.
 */
const showLoader = (container = "body") => {

    if (typeof container === "string") {
        container = document.querySelector(container);
    } else if (container instanceof HTMLElement) {
        container = container;
    } else {
        throw new Error("showLoader: 'container' parameter must a selector string or an 'HTMLElement' instance.");
    }

    // If the container CSS `position` property is not "absolute" (if it is, we won't need to set it to "relative").
    if (container.style.position !== "absolute") {

        // If the container has the CSS `position` property set.
        if (container.style.position) {
            container.dataset.previousPositionValue = container.style.position;
        }

        // Makes the element's position relative.
        container.style.setProperty("position", "relative", "important");
    }

    const html = `
        <div class="js-loader position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background-color: rgba(var(--bs-body-bg-rgb), 0.5);">
            <div class="spinner-border" style="max-width: 3rem; max-height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;

    container.insertAdjacentHTML("beforeend", html);
};

/**
 * Removes a loader from the given element (<body> by default).
 * @param {string|HTMLElement} [container="body"] Selector string or HTMLElement of the element to remove the loader from.
 */
const hideLoader = (container = "body") => {

    if (typeof container === "string") {
        container = document.querySelector(container);
    } else if (container instanceof HTMLElement) {
        container = container;
    } else {
        throw new Error("showLoader: 'container' parameter must a selector string or an 'HTMLElement' instance.");
    }

    const loaderEl = container.querySelector(".js-loader");

    if (loaderEl) {
        // Removes the loader element.
        loaderEl.remove();
        // Sets back the previous `absolute` position of the element.
        if (container.dataset.previousPositionValue) {
            container.style.setProperty("position", container.dataset.previousPositionValue);
        }
    }
}

/**
 * Switches to a theme without reloading the page.
 * @param {string} theme Theme to switch to.
 */
const setTheme = (theme = "light") => {

    const
        html = document.querySelector("html"),
        themeBtn = document.querySelector(".js-bs-theme-btn");

    // If theme button is not in the DOM, sets the theme to "dark" and throws an error.
    if (!themeBtn) {
        html.dataset.bsTheme = "dark";
        throw new Error("Cannot find THEME button. You must include it in the app's header.");
    }

    html.dataset.bsTheme = theme;
    App.setData("bsTheme", theme);
    themeBtn.dataset.bsTheme = theme;
    themeBtn.innerText = theme === "light" ? "☀" : "🌙";

};

/**
 * Inits one Bootstrap's for each `[title]` HTML attribute in the DOM.
 */
const initTooltips = () => {
    const tooltipTriggerList = document.querySelectorAll('[title]');
    const tooltipList = [...tooltipTriggerList].map((tooltipTriggerEl) => {
        const tooltip = new bootstrap.Tooltip(tooltipTriggerEl);
        // Adds back the `title` HTML attribute to allow to be targeted by CSS styles as an element with a `title` attribute.
        tooltipTriggerEl.setAttribute("title", "");
        return tooltip;
    });
};

/**
 * Tests if a variable is a NATIVE Object/class instance.
 * @param {array|object} variable Native Object/class instance.
 * @returns {boolean}
 */
const isObjectOrClassInstance = (variable) => {
    // If NOT undefined|null
    // AND if type of Object
    // AND if instance of Object
    // AND if instance of a class
    return !!variable && typeof variable === 'object' && (variable.constructor === Object || !!variable.constructor.toString().match(/^class/));
}

/**
 * Tests if a variable is a NATIVE Array/Object/class instance.
 * @param {array|object} variable Native Array/Object/class instance.
 * @returns {boolean}
 */
const isArrayOrObjectOrClassInstance = (variable) => {
    // If NOT undefined|null
    // AND if type of Object
    // AND if instance of Array
    // AND if instance of Object
    // AND if instance of a class
    return !!variable && typeof variable === 'object' && (variable.constructor === Array || variable.constructor === Object || !!variable.constructor.toString().match(/^class/));
}

/**
 * Makes an asynchronous FETCH request.
 * @param {object} fetchData 
 * {
 *     url:    {string}                    URL to target
 *     method: {string} [optional="POST"]  HTTP method
 *     data:   {object} [optional]         JS object to send to PHP
 * }
 * @param {boolean} strict Return format should be strict. In non strict mode, returns response without check. NOT RECOMMENDED.
 * @returns {object}
 * {
 *     success: {boolean}           If the request and PHP process performed correctly. If true, the data should be used. If false, the error should be used.
 *     data:    {object/undefined}  Response data object (optional)
 *     msg:     {object/undefined}  Error/success message (optional)
 * }
 */
const fetchRequest = async (fetchData, strict = true) => {

    try {

        // ###############
        // DATA VALIDATION
        // ###############

        if (typeof fetchData?.url !== "string") {
            throw {msg: "`url` property is not a string."};
        }

        if (!["POST", "GET"].includes(fetchData?.method)) {
            fetchData.method = "POST";
        }

        // If `data` is undefined (not given) or null.
        if (fetchData.data === undefined || fetchData.data === null) {
            fetchData.data = {};
        } // If `data` is not a native "Object/class instance".
        else if (!isObjectOrClassInstance(fetchData?.data)) {
            throw {msg: "`data` property should be: not given (undefined) OR null OR an object (native or class instance)."};
        }

        // #######
        // REQUEST
        // #######

        // Instantiates a FormData object to allow easy strings/numbers/files... to be sent to the server.
        const formData = new FormData();

        // For each key/values in the given object, add these to the FormData object.
        for (let [key, value] of Object.entries(fetchData.data)) {
            // Value is either an native "Array/Object/class instance" (not a string, boolean, number...).
            if (isArrayOrObjectOrClassInstance(value)) {
                // Transforms the Object/Array in to JSON.
                value = JSON.stringify(value);
            }
            // Appends the key/value pair into the FormData object.
            formData.append(key, value);
        }

        // Executes the request.
        const response = await fetch(
            fetchData.url,
            {
                method: fetchData.method,
                body: formData,
                headers: fetchData.headers
            }
        );

        // ###################
        // RESPONSE VALIDATION
        // ###################

        // If the request did not perform correctly (404, 500, crash, no internet...)
        if (!response.ok) {
            throw {msg: response.statusText};
        }

        /**
         * Expected response format.
         * @var {object}
         * {
         *     success: {boolean}           If the request and PHP process performed correctly. If true, the data should be used. If false, the error should be used.
         *     data:    {object/undefined}  Response data object (optional)
         *     msg:     {object/undefined}  Error/success message (optional)
         * }
         */
        let responseObj;

        // #####################
        // JSON RESPONSE PARSING
        // #####################

        // In non strict mode, returns the raw content of the ajax call. Not recommended.
        if (!strict) {
            return await response.text();
        }

        // Here we'll try to parse the request's JSON response into a JS object.
        // We needed to write another `try catch` statement since the parent `try catch` could not parse a `json()` SyntaxError (it's a parsing error).
        try {
            // Tries to parse JSON response into JS.
            responseObj = await response.json();
        } // If `await response.json()` returns an error, it's a parsing error.
        catch (error) {
            throw {
                data: {originalErrorMsg: error},
                msg: "JSON response could not be parsed. There may be an error in its format.",
            };
        }

        // ###################
        // RESPONSE VALIDATION
        // ###################

        if (responseObj.success === undefined) {
            throw {msg: "Response object has to contain `success` boolean property, saying wether or not the request and the process have resulted correctly."};
        }

        if (responseObj.data === undefined) {
            throw {msg: "Response object has to contain `data` object property, even if its content is undefined."};
        }

        if (responseObj.msg === undefined) {
            throw {msg: "Response object has to contain `msg` string property, even if its content is undefined."};
        }

        /**
         * Returns the response object `success` is true.
         */
        if (responseObj.success) {
            return responseObj;
        } // If AJAX file returned `success` false, there is a error.
        else {
            throw responseObj;
        }

    }
    /**
     * If ANY KIND OF ERROR OCCURS.
     * @param {object} errorObj The error object:
     */
    catch (errorObj) {
        throw new Error(errorObj.msg);
    }
};

/**
 * Gets a {@link CSSStyleSheet} loaded in the DOM.
 * @param {string} stylesheetId Stylesheet's <link> element ID.
 * @returns {CSSStyleSheet}
 */
const getStylesheet = (id) => {
    return [...document.styleSheets].find(stylesheet => stylesheet.ownerNode.id === id) || null;
}

/**
 * Gets a {@link CSSRule} from a {@link CSSStyleSheet} loaded in the DOM
 * @param {string} stylesheetId Stylesheet's <link> element ID.
 * @param {string} selector Selector of the CSS rule which contains the property you'd like to update.
 * @returns {CSSRule}
 */
const getStylesheetRule = (stylesheet, selector) => {
    return Object.values(stylesheet.cssRules).find(rule => rule.selectorText === selector);
}

/**
 * Updates a value of a CSS property inside a {@link CSSStyleSheet} loaded in the DOM.
 * @param {string} stylesheetId Stylesheet's <link> element ID.
 * @param {string} selector Selector of the CSS rule which contains the property you'd like to update.
 * @param {string} property Property you'd like to update.
 * @param {string|number} value Value of the property you'd like to update.
 */
const updateCss = (stylesheetId, selector, property, value) => {

    const stylesheet = getStylesheet(stylesheetId);

    const rule = getStylesheetRule(stylesheet, selector);

    if (rule && rule.style.hasOwnProperty(property)) {
        rule.style[property] = value;
    }
}

/**
 * Gets a value of a CSS property inside a {@link CSSStyleSheet} loaded in the DOM.
 * @param {string} stylesheetId Stylesheet's <link> element ID.
 * @param {string} selector Selector of the CSS rule which contains the property you'd like to update.
 * @param {string} property Property you'd like to update.
 * @returns {string}
 */
const getCssValue = (stylesheetId, selector, property) => {

    const stylesheet = getStylesheet(stylesheetId);

    const rule = getStylesheetRule(stylesheet, selector);

    if (rule && rule.style.hasOwnProperty(property)) {
        return rule.style[property];
    }
}

/**
 * Opens/closes a tab from the given ID.
 * @param {string} id Tab's ID.
 * @param {null|boolean} show null will toggle the class, boolean will set it.
 */
const openTab = (id, show = null) => {

    const currentBtn = document.querySelector(`.js-side-bar-btn[data-id="${id}"]`);

    // Changes the state of the current button.
    if (show === null) {
        currentBtn.classList.toggle("active");
        show = currentBtn.classList.contains("active");
    } else {
        show ? currentBtn.classList.add("active") : currentBtn.classList.add("active");
    }

    // For each tabs in the same category as the current button ("left" or "bottom").
    for (const tab of document.querySelector(`.js-side-bar-tab[data-id="${currentBtn.dataset.id}"]`).closest(".js-side-bar-tab-wrapper").querySelectorAll(".js-side-bar-tab")) {
        const currentId = tab.dataset.id;
        // Shows the current tab.
        if (id === currentId && show) {
            tab.classList.remove("d-none");
        } // Hides the current tab and "unselects" its corresponding button.
        else {
            tab.classList.add("d-none");
            document.querySelector(`.js-side-bar-btn[data-id="${currentId}"]`).classList.remove("active");
        }
    }

    // Gets active buttons and save their IDs in the local storage.
    const sideBarOpenedTabIdList = [...document.querySelectorAll(".js-side-bar-btn.active")].map(e => e.dataset.id);
    App.setData("sideBarOpenedTabIdList", sideBarOpenedTabIdList);
}
