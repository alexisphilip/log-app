/**
 * Appends an HTML terminal in the given element.
 * 
 * You can:
 * - Create custom "programs" (JS functions)
 * - Execute commands with parameters (execute these JS functions through the terminal)
 * - Append text/HTML in the terminal
 * - Change the prompt, the terminal's name...
 * 
 * @see {@link readme.md} documentation
 */
class Terminal {

    // ##################
    // PRIVATE PROPERTIES
    // ##################

    /**
     * The terminal's HTML element.
     * @var {HTMLElement}
     */
    #el;
    /**
     * Terminal's name.
     * @var {string}
     */
    #name = "terminal";
    /**
     * Default prompt.
     * @var {string}
     */
    #prompt = "user@host: ";
    /**
     * If the 
     */
    #isGenerated = true;

    // #################
    // PUBLIC PROPERTIES
    // #################

    /**
     * List of global programs, common to all terminals.
     * @var {function{}}
     */
    static globalPrograms = {};
    /**
     * List of local programs, for the current terminal instance.
     * @var {function{}}
     */
    programs = {};
    /**
     * Outputs the bash input into the terminal.
     * It's useful to set it to `false` in a bash script
     * @var {boolean}
     */
    echo = true;
    /**
     * TODO: WIP (ls program)
     */
    directoryElements = [];
    
    /**
     * Sets up global attributes and adds the "keydown" listener.
     * @param {HTMLElement} container Element which will contain the terminal element.
     */
    constructor(container) {

        // Creates the HTML terminal \\

        this.#el = container;

        const terminalEl = document.createElement("div");
        terminalEl.classList.add("terminal");

            const terminalInputWrapperEl = document.createElement("div");
            terminalInputWrapperEl.classList.add("terminal-input-wrapper");
            
                const terminalPromptEl = document.createElement("span");
                terminalPromptEl.classList.add("terminal-prompt");
            
                const terminalInputEl = document.createElement("input");
                terminalInputEl.type = "text";
                terminalInputEl.classList.add("terminal-input");
            
            terminalInputWrapperEl.append(terminalPromptEl);
            terminalInputWrapperEl.append(terminalInputEl);

            const terminalEntriesEl = document.createElement("div");
            terminalEntriesEl.classList.add("terminal-entries");
        
        terminalEl.append(terminalInputWrapperEl);
        terminalEl.append(terminalEntriesEl);
        this.#el.append(terminalEl);

        this.#el.querySelector(".terminal-prompt").innerHTML = this.#prompt;
        
        // Setting up prompt & mandatory listeners \\
        
        const inputEl = this.#el.querySelector(".terminal-input");

        this.#el.addEventListener("click", () => {
            inputEl.focus();
        });

        let commands = [],
            commandPosition = -1;

        let tabCount = 0;

        /**
         * On terminal <input> KEYDOWN:
         * - If ArrowUp/ArrowDown: goes back/forward in bash history.
         * - If Enter: executes the command and adds it to bash history.
         * - If Tab: lists the available local and global commands.
         */
        inputEl.addEventListener("keydown", (e) => {
            const el = e.currentTarget;
            let val = el.value;

            // If key is "Enter", adds the command to bash history and executes it.
            if (e.key === "Enter") {
                const command = val.trim();
                el.value = "";
                if (command !== "") {
                    commands.unshift(command);
                }
                commandPosition = -1;
                // Executes the command.
                this.#bashParse(command, "input");
            } // If key is "ArrowUp", goes back in bash history and outputs the previous command.
            else if (e.key === "ArrowUp") {
                if (commands[commandPosition + 1]) {
                    commandPosition++;
                    el.value = ""; // Allows the cursor to be set at the end of the input' value.
                    el.value = commands[commandPosition];
                    this.#moveCursorToEndOfInput(el);
                }
            } // If key is "ArrowDown", goes forward in bash history and outputs the previous command.
            else if (e.key === "ArrowDown") {
                if (commands[commandPosition - 1]) {
                    commandPosition--;
                    el.value = ""; // Allows the cursor to be set at the end of the input' value.
                    el.value = commands[commandPosition];
                    this.#moveCursorToEndOfInput(el);
                } else {
                    el.value = "";
                }
            } // If key is "Tab", lists the available local and global commands.
            else if (e.key === "Tab") {
                e.preventDefault();
                tabCount++;
                if (tabCount !== 2) {
                    return;
                }
                tabCount = 0;
                // TODO: WIP (ls program)
                if (this.directoryElements.constructor.name !== "Object") {
                    this.directoryElements = [];
                }
                const programs = [...Object.keys(Terminal.globalPrograms), ...Object.keys(this.programs), ...this.directoryElements].sort();
                // Gets all programs which start
                const matches = programs.filter(program => program.startsWith(val));
                // Outputs suggestions only if there are more than 1,
                // OR if there is only one, if the current value is not an exact suggestion name. 
                if (matches.length > 1 || (matches.length === 1 && matches[0] !== val)) {
                    this.write(val, true);
                    this.write(matches.join("    "));
                }
            } else {
                tabCount = 0;
            }
        });

        this.isGenerated = true;
    }

    /**
     * Sets the terminal's name.
     * @param {string} name
     */
    setName(name) {
        this.#name = name;
    }

    /**
     * Sets the terminal's prompt.
     * @param {string} prompt 
     */
    setPrompt(prompt) {
        this.#prompt = prompt;
        // Replaces white spaces by non breakable spaces.
        this.#prompt = this.#prompt.replace(" ", "&nbsp;");
        // Updates the main prompt element if it already has been generated.
        if (this.#isGenerated) this.#el.querySelector(".terminal-input-wrapper .terminal-prompt").innerHTML = this.#prompt;
    }

    /**
     * Registers a program (JS function) globally (for all terminal's instances).
     * @param {string} programName The program's name.
     * @param {function} programFunction The program's JS function to execute when requested.
     */
    static addProgram(programName, programFunction) {
        Terminal.globalPrograms[programName] = programFunction;
    }

    /**
     * Registers a program (JS function) locally (only for the current terminal's instance).
     * @param {string} programName The program's name.
     * @param {function} programFunction The program's JS function to execute when requested.
     */
    addProgram(programName, programFunction) {
        this.programs[programName] = programFunction;
    }

    /**
     * Executes a command string.
     * @param {string} command Command string to execute.
     */
    exec(command) {
        // First step is to parse the command string.
        this.#bashParse(command, "script");
    }

    /**
     * Outputs text in the terminal:
     * - builds an HTML element which contains the prompt & output.
     * - appends the element in the list of bash outputs.
     * @param {string} content Text/HTML to output in the terminal.
     * @param {boolean} appendPrompt Outputs the prompt before the content.
     */
    write(content, appendPrompt = false) {

        const bashEntryEl = document.createElement("pre");
        bashEntryEl.classList.add("terminal-entry");
        
        // The prompt needs to be appended in some cases:
        // - if the user hit "Enter": outputs the prompt + user input (+ command output).
        // - if the user hit "Tab": outputs the prompt + user input (+ command output).
        if (appendPrompt) {
            const promptEl = document.createElement("span");
            promptEl.classList.add("terminal-prompt");
            promptEl.innerHTML = this.#prompt;
            bashEntryEl.appendChild(promptEl);
        }
        
        const commandEl = document.createElement("span");
        commandEl.classList.add("terminal-content");
        commandEl.innerHTML = content;
        bashEntryEl.appendChild(commandEl);

        this.#el.querySelector(".terminal-entries").appendChild(bashEntryEl)
    }

    /**
     * Outputs a monospace table from a given array..
     * @param {object[]|array[]} items Array of arrays of array of objects. 
     */
    writeTable(items) {

        // Array/object formatting \\
    
        let headerVals = [],
            bodyVals = [];
    
        if (items[0].constructor.name === "Array") {
            bodyVals = items;
        } else if (items[0].constructor.name === "Object") {
            headerVals = Object.keys(items[0]),
            bodyVals = items.map(row => Object.values(row));
        } else {
            return "";
        }
    
        // Value formatting \\
        
        bodyVals = bodyVals.map(row => Object.values(row).map(col => {
            if (typeof col !== "string") {
                return JSON.stringify(col);
            }
            return col;
        }));
    
        // Calculates the max length of each columns (to later calculate col spacing) \\
    
        let maxColLengths = [];
    
        // Merges all header and body values together, to get the max length of each column values.
        const allValues = [headerVals, ...bodyVals];
        
        // For each entries.
        for (const row of allValues) {
            // For each columns (values in the row).
            for (let i = 0; i < row.length; i++) {
                // If current row is longer than any row values, or if max space.
                if (row[i].length > maxColLengths[i] || !maxColLengths[i]) maxColLengths[i] = row[i].length;
            }
        }
    
        // Creates a "separator" string used to split header to value rows, as well as top line and bottom line \\
    
        let separatorString = `+`;
        
        for (const maxColSpace of maxColLengths) {
            separatorString += `-${"-".repeat(maxColSpace)}-+`;
        }
    
        // Creates the output string \\
       
        let output = "";
    
        // Table top line.
        output += separatorString + "\n";
    
        // Table header values.
        for (let i = 0; i < headerVals.length; i++) {
            const el = headerVals[i];
            if (i === 0) output += `|`;
            output += ` ${el}${` `.repeat(maxColLengths[i] - el.length)} |`;
        }
    
        // If there are header values, appends a table header/body separator line.
        if (headerVals.length) output += "\n" + separatorString + "\n";
        
        // Table body values.
        for (let i = 0; i < bodyVals.length; i++) {
    
            // For each columns in the current row.
            for (let y = 0; y < bodyVals[i].length; y++) {
                const el = bodyVals[i][y];
                if (y === 0) output += `|`;
                output += ` ${el}${` `.repeat(maxColLengths[y] - el.length)} |`;
            }
    
            output += `\n`;
        }
        
        // Table bottom line.
        output += separatorString;
    
        this.write(output);
    }

    /**
     * Clears all outputs in the terminal.
     */
    clear() {
        this.#el.querySelector(".terminal-entries").innerHTML = "";
    }

    /**
     * Parses a command string.
     * - splits the string
     * - interprets the command name
     * - interprets the command arguments
     * @param {string} command Command string to parse.
     * @param {string} context Execution's context:
     * - input: the command was executed from the user's input.
     * - script: the command was executed from a script.
     */
    #bashParse(command, context) {

        command = command.trim();

        // If @echo is set to `true`, outputs the command.
        if (this.echo) {
            this.write(command, context === "input");
        }

        // If the command is an empty string, there's no need to interpret it.
        if (command === "") {
            return;
        }

        let words,
            program,
            regex,
            args = [],
            argsObject = {};

        /**
         * Splits the command string by spaces except when it's surrounded by single/double quotes.
         * Regex: selects words separated by spaces which aren't between single/double quotes.
         * 
         * @example
         * IN:  git commit -m "My 'commit' message"
         * OUT: ["git", "commit", "-m", "\"My 'commit' message\""]
         */
        regex = /\s(?=(?:(?:[^"]*"){2})*[^"]*$)(?=(?:(?:[^']*'){2})*[^']*$)/g;
        words = command.split(regex);

        // Removes the first word, which is the program's name.
        program = words.shift();

        // For each words in the command string.
        for (let i = 0; i < words.length; i++) {

            const word = words[i],
                  nextWord = words[i + 1];

            // If empty string, skip.
            if (word == "") {
                break;
            }

            /**
             * If the current word is an argument's key (ex: `-p` or `--page`).
             * Regex: contains 1 or 2 `-` at the start of the string and is not followed by `-`.
             */
            regex = /^-{1,2}(?!-)/;
            if (regex.test(word)) {
                
                argsObject[word] = null;
                args.push(word);
                
                // Checks if the next word is also an argument's key.
                if (regex.test(nextWord)) {
                    // If it is, we'll skip to the next loop iteration.
                    continue;
                } // If it's not a argument's key.
                else {
                    // Sets that word as the argument's value.
                    argsObject[word] = nextWord;
                    args.push(nextWord);
                    // Then we'll skip that next word in the current loop.
                    i++;
                }
            } // If it's not an argument's key, it's a word.
            else {
                argsObject[word] = null;
                args.push(word);
            }
        }

        // console.log(args);
        // console.log(argsObject);

        this.#bashLookupAndExec(program, args, argsObject, context);
    }

    /**
     * Looks up for the program to execute, then executes it.
     * @param {string} program Program's name to execute.
     * @param {array} args Command's arguments.
     * @param {object} argsObject Command's arguments as an object.
     * @param {string} context Execution's context:
     * - input: the command was executed from the user's input.
     * - script: the command was executed from a script.
     */
    #bashLookupAndExec(program, args, argsObject, context) {

        // Looks up the program in global programs.
        if (program in Terminal.globalPrograms) {
            Terminal.globalPrograms[program](this, args, argsObject);
        } // Looks up the program in local programs.
        else if (program in this.programs) {
            this.programs[program](this, args, argsObject);
        } else {
            this.write(`${this.#name}: ${program}: command not found`);
        }

        // For the "input" context only (the "script" context cannot reset the `echo` variable).
        if (context === "input") {
            // Once a program is done executing, its @echo variable is reset
            // to true since commands needs to be outputted in the terminal.
            this.echo = true;
        }
    }

    /**
     * Focuses the cursor on the given input and moves the users cursor at the end.
     * @param {HTMLInputElement} inputEl Input element to focus and move its cursor at the end.
     */
    #moveCursorToEndOfInput(inputEl) {
        setTimeout(() => {
            inputEl.selectionStart = inputEl.selectionEnd = inputEl.value.length;
            inputEl.focus();
        }, 0)
    }
}
