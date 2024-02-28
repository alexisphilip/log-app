// ##############
// ERROR HANDLERS
// ##############

/**
 * On `window.unhandledrejection`, we'll catch the `PromiseRejectionEvent` event object and give its data to the normalized {@link handleError} object.
 * @see https://developer.mozilla.org/en-US/docs/Web/API/PromiseRejectionEvent
 */
window.addEventListener("unhandledrejection", (event) => {
    handleError("async", event, event.reason.message, event.reason.stack);
});

/**
 * On `window.error`, we'll catch the `ErrorEvent` event object and give its data to the normalized {@link handleError} object.
 * @see https://developer.mozilla.org/en-US/docs/Web/API/ErrorEvent
 */
window.addEventListener("error", async (event) => {
    handleError("sync", event, event.error.message, event.error.stack);
});


// #############
// ON LINK CLICK (adds loader)
// #############

// For each clickable links in the page.
for (const linkEl of document.querySelectorAll("a")) {
    // Adds the "click" listener on link on the current website (which starts with `index.php`).
    if (linkEl.getAttribute("href").startsWith("index.php")) {
        linkEl.addEventListener("click", () => {
            showLoader();
        });
    }
}


// ###################
// ON DATE INPUT CLICK (opens date picker)
// ###################

for (const inputEl of document.querySelectorAll(`input[type="date"]`)) {
    inputEl.addEventListener("click", () => {
        /**
         * @see https://developer.mozilla.org/en-US/docs/Web/API/HTMLInputElement/showPicker
         */
        inputEl.showPicker();
    });
}


// #########################
// LIGHT/DARK THEME SWITCHER (+ saves current state in `localStorage`)
// #########################

// If THEME was saved in local storage, otherwise sets "light" by default.
const theme = App.getData("bsTheme") || "light";

setTheme(theme);

const themeBtnEl = document.querySelector(".js-bs-theme-btn");

if (themeBtnEl) {
    /**
     * On THEME SWITCH button, switches the theme.
     */
    themeBtnEl.addEventListener("click", () => {
        setTheme(themeBtnEl.dataset.bsTheme === "light" ? "dark" : "light");
    });
}


// ##############################
// INITS ALL BOOTSTRAP'S TOOLTIPS
// ##############################

initTooltips();


// ################
// SIDE BAR MANAGER
// ################

const sideBarBtnList = document.querySelectorAll(".js-side-bar-btn");

if (sideBarBtnList.length) {

    // For each SIDE BAR buttons.
    for (const btn of sideBarBtnList) {
        /**
         * On SIDE BAR <button> "click":
         */
        btn.addEventListener("click", () => {
            openTab(btn.dataset.id);
        });
    }

    // Opens the previously opened tabs.
    if (App.getData("sideBarOpenedTabIdList")) {
        App.getData("sideBarOpenedTabIdList").map(id => openTab(id));
    }

    // ############################
    // SIDE BAR TABS CUSTOM SCRIPTS
    // ############################

    // -----------
    // Terminal.js
    // -----------

    const terminalTab = document.querySelector(`.js-side-bar-tab[data-id="terminal"]`);
    const myTerminal = new Terminal(terminalTab);

    // Sets its color.
    terminalTab.querySelector(".terminal").style.backgroundColor = "var(--bs-body-secondary)";
    terminalTab.querySelector(".terminal").style.color = "var(--bs-body-color)";

    // Sets its name + prompt.
    myTerminal.setName("terminal");
    myTerminal.setPrompt("user@logapp: ");

    // ### Adds some programs to the terminal! ### \\

    const helpProgram = (terminal) => {
        const allPrograms = [...Object.keys(Terminal.globalPrograms), ...Object.keys(terminal.programs)];
        allPrograms.sort((a, b) => a.localeCompare(b))
        terminal.write("List of available commands:");
        for (const program of allPrograms) {
            terminal.write(`  ${program}`);
        }
    };

    Terminal.addProgram("help", helpProgram);
    Terminal.addProgram("?", helpProgram);

    Terminal.addProgram("exit", (terminal) => {

        const output = (seconds) => {
            terminal.write(`Terminal will auto-destroy in ${seconds}`);
        }

        let seconds = 5;
        output(seconds);

        const intervalId = setInterval(() => {
            seconds--;
            output(seconds);

            if (seconds < 1) {
                clearInterval(intervalId);
                // Clears the terminal and closes it.
                terminal.clear();
                openTab("terminal", false);
            }
        }, 1000);
    });

    Terminal.addProgram("clear", (terminal) => {
        terminal.clear();
    });

    Terminal.addProgram("ping", (terminal) => {
        terminal.write("pong");
        terminal.write("<sup><i>Brings back some Minecraft memories huh?</i></sup>");
    });

    Terminal.addProgram("applocalstorage", (terminal, args, argsObject) => {

        const commands = {
            'get': () => {
                if (localStorage.logApp) {
                    terminal.write(localStorage.logApp);
                } else {
                    terminal.write("No localStorage data for the current app.");
                }
            },
            'delete': () => {
                delete localStorage.logApp;
                terminal.write("Deleted app's localStorage content.");
            }
        }

        if (args.length) {
            if (commands.hasOwnProperty(args[0])) {
                commands[args[0]]();
            } else {
                terminal.write(`applocalstorage: ${args[0]} is not a valid argument. Type 'applocalstorage' to get the list of available arguments.`);
            }
        } else {
            terminal.write("Manages the app's `localStorage`.");
            terminal.write("Arguments:");
            terminal.write("  get: returns the app's localStorage content");
            terminal.write("  delete: deletes the app's localStorage content");
        }
    });

    Terminal.addProgram("theme", (terminal, args, argsObject) => {

        const commands = {
            'set': () => {
                if (["light", "dark"].includes(args[1])) {
                    setTheme(args[1]);
                    terminal.write(`App theme set to '${args[1]}'`);
                } else {
                    terminal.write(`'${args[1]}' is not a supported theme.`);
                }
            },
            'list': () => {
                terminal.writeTable([
                    {theme: "light"},
                    {theme: "dark"},
                ]);
            }
        }

        if (args.length) {
            if (commands.hasOwnProperty(args[0])) {
                commands[args[0]]();
            } else {
                terminal.write(`theme: ${args[0]} is not a valid argument. Type 'theme' to get the list of available arguments.`);
            }
        } else {
            terminal.write("Manages the app's theme.");
            terminal.write("Arguments:");
            terminal.write("  set [theme]: sets a theme");
            terminal.write("  list: lists the available themes");
        }
    });

    Terminal.addProgram("credits", (terminal) => {

        const logAppLink = `<a href="https://github.com/alexisphilip/log-app" target="_blank" rel="noopener noreferrer">Log App</a>`,
              terminalJsLink = `<a href="https://github.com/alexisphilip/terminal.js" target="_blank" rel="noopener noreferrer">terminal.js</a>`;

        terminal.write(`<i>${logAppLink}</i> & <i>${terminalJsLink}</i> made with ❤`);
        terminal.write("       by Alexis Philip ✌");
    });

    myTerminal.write("Log App terminal");
    myTerminal.write("Type `help` or `?` to list available commands");


    // -----
    // Other
    // -----
}
