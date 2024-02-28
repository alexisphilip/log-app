/**
 * Terminal.js example.
 */

/**
 * TODO:
 * - Main problem:
 *      - the terminal's input is always in the DOM
 *      - when a program is running, it should not be visible
 *      - I should fix that, by only appending it after a program is done being executed
 *      - that way I could easily add the feature "wait for user input"
 * - feature: wait for user input, exit program with Ctrl+C
 * - default programs:
 *      - help, ??, ?: lists all global and local programs, could put all the aliases
 *      - clear
 *      - ls
 *      - cd
 * - aliases
 * - allow default program loading, and overwrite if needed
 * - CSS:
 *      - add it automatically in constructor
 *      - allow overwrite? add method in the class or manually in user's CSS?
 * - feature: setTemplate: default terminal template, or default output HTML/CSS?
 * - feature: setTheme: is it necessary?
 * change objects passed to functions
 * when parsing "words string", remove single & double quotes
 * add a `data` attribute which custom programs can use to store custom attributes, data, etc...
 */

// #####
// SETUP
// #####

// Selects the terminal's parent, where it will be appended.
const container = document.querySelector("#myTerminal");

// Instantiates the terminal.
const myTerminal = new Terminal(container);

// Sets up options.
myTerminal.setName("myTerminal");
myTerminal.setPrompt("alexis@philip: ");

// ################
// DEFAULT PROGRAMS (to add by default, @see TODO)
// ################

Terminal.addProgram("help", (terminal) => {
    const allPrograms = [...Object.keys(Terminal.globalPrograms), ...Object.keys(terminal.programs)];
    for (const program of allPrograms) {
        terminal.write(program);
    }
});

// ###############
// CUSTOM PROGRAMS
// ###############

/**
 * Writes in the terminal.
 */
myTerminal.addProgram("ping", (terminal) => {
    terminal.write("pong");
});

/**
 * Executes commands in the terminal, without echoing the commands.
 */
myTerminal.addProgram("tables", (terminal, args, argsObject) => {
    const array = [
        ["Philip", 150, true],
        ["Standbridge", 150, true],
        ["Foret", 150, false],
    ];
    terminal.writeTable(array);
    const object = [
        {name: "Philip", value: 150, bool: true},
        {name: "Standbridge", value: 150, bool: true},
        {name: "Foret", value: 150, bool: false},
    ];
    terminal.writeTable(object);
});

