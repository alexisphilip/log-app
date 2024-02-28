# terminal.js

JS integration of an HTML & CSS terminal with built-in functions.

## Usage

Instantiate the terminal.

```javascript
const container = document.querySelector("#myTerminal");
const myTerminal = new Terminal(container);
```

### Basic methods

```javascript
// Executes a bash command.
myTerminal.exec("myCommand -arg1 val1");

// Writes in the terminal.
myTerminal.write("Hello world!");

// Writes a table in the terminal.
myTerminal.writeTable([["val1", "val2"], ["val3", "val4"]]); // Array of arrays (table without header)
myTerminal.writeTable([{col1: "val1", col2: "val2"}, {col1: "val3", col2: "val4"}]); // Array in objects (table with header)

// Clears the terminal.
myTerminal.clear();

// Changes the terminal's name.
myTerminal.setName("myTerminal");

// Changes the prompt.
myTerminal.setPrompt("name@example: ");
```

### Add a script (JS function)

**Local** script (for the current terminal instance).

```javascript
myTerminal.addProgram("ping", (terminal) => {
    terminal.write("pong");
});

myTerminal.exec("ping"); // Output: "pong"
```

**Global** script (for all terminals).

```javascript
Terminal.addProgram("ping", (terminal) => {
    terminal.write("pong");
});

myTerminal.exec("ping"); // Output: "pong"
```

> `Terminal` methods can be used through the `terminal` parameter.

### Script arguments

You can get script arguments as an `Array` or an `Object`.

```javascript
Terminal.addProgram("myScript", (terminal, args, argsObject) => {
    // args =       ["-p", "val1", "-m", "val2", "val3"]
    // argsObject = {"-p": "val1", "-m": "val2"}
});

myTerminal.exec("myScript -p val1 -m val2 val3");
```
