# Log App

Welcome to the Log App documentation! 🖐

## FEATURES

### 🔍 Log viewer

Lists all logs per **day**, **type** & **subdirectory**.

### 📊 Log analytics

Shows charts for all logs in a **time interval** & **subdirectory**.

### 🙌 Multi-folder logs

Logs can be saved and parsed in a *3 level max* folder architecture.\
This means you can organize your logs per apps, types, origins... (JS, CRON, LOGIN, whatever you like!)

## HOW IT'S MADE

### ⚙ Config

The app's configuration is made through the `config.json` file.

> `config.json`

### 📂 Architecture (MVC)

```text
├─ controller/      CONTROLLERS: sync + async
│  ├─ async/          - Async: file called by async JS (AJAX, fetch...) which returns JSON
│  └─ ...             - Sync:  file called by basic HTTP, which returns plain HTML
├─ data/            DATA: JSON database tables
├─ includes/        INCLUDES: PHP files to include
│  ├─ class/          - CLASSES: PHP classes
│  ├─ components/     - COMPONENTS: PHP template files to use multiple times in `view/`
│  └─ vendors/        - VENDORS: external PHP libraries
├─ static/          STATIC files
│  ├─ css/            - CSS/LESS files
│  ├─ img/            - IMAGE files
│  ├─ js/             - JAVASCRIPT files
│  │  ├─ class/         - CLASSES: JS classes
│  │  ├─ view/          - VIEW: scripts specific to a view
│  │  ├─ vendors        - VENDORS: external JS libraries
│  │  └─ ...            - Scripts
│  └─ vendors/
├─ view/            VIEW: HTML template files
│  ├─ common/         - Common templates (document-top, document-bottom...)
│  └─ ...             - Page templates (app-list, log-list, chart-list...)
├ config.ini
└ index.php
```

## HOW TO USE/UPDATE

Here's how to:

- Create a **new PAGE**
- Create a **new ASYNC request** (AJAX)

### Create a new PAGE

1. Create a controller in `controller/`:

    ```php
    <?php

    /**
     * DESCRIPTION:
     * [SMALL DESCRIPTION OF THE CURRENT CONTROLLER/PAGE]
     * 
     * INPUT:
     * @param string paramName1  (GET) (OPTIONAL) [PARAMETER DESCRIPTION]
     * @param int    paramName2  (POST)           [PARAMETER DESCRIPTION]
     */

    App::setTitle("LOG APP - [PAGE NAME]");


    // #######
    // PROCESS
    // #######

    // Your controller code goes here.

    App::require("includes/[OPTIONAL_PHP_FILES].php");

    $tplVarList["myTemplateVariable"] = "This variable will be usable in the VIEW template";


    // ######
    // OUTPUT
    // ######

    App::setJs("view/[YOUR JAVASCRIPT FILE].js");

    App::getTemplate("[VIEW NAME]", $tplVarList);

    ```

1. Create a view in `view/`:

    ```html
    <?php App::getTemplate("common/document-top", $tplVarList) ?>

    <!-- APP HEADER -->
    <header class="row mx-0 g-3 p-3 justify-content-xl-between justify-content-center bg-body-tertiary border-bottom">
        <div class="col-auto hstack gap-3">
            <a href="index.php"role="button" aria-disabled="true"><img src="<?= App::getImg("main-logo.png") ?>" style="width: 2rem;"></a>
            <h4 class="mb-0"><b>[PAGE NAME]</b></h4>
        </div>
        <div class="col-auto hstack">
            <button type="button" class="btn btn-link js-bs-theme-btn" style="text-decoration: none;"></button>
        </div>
    </header>

    <!-- APP CONTENT -->
    <main class="d-flex flex-1 overflow-hidden position-relative">

        <!-- YOUR PAGE CONTENT! -->

    </main>

    <!-- TEMPLATES -->
    <div class="template-list">

        <!-- HERE PUT YOUR HIDDEN HTML TEMPLATES TO CLONE WITH JS -->

    </div>

    <!-- GLOBAL MEMBERS -->
    <script>
        const g_myGlobalVariable = <?= json_encode($YOUR_PHP_DATA) ?>;
    </script>

    <?php App::getTemplate("common/document-bottom", $tplVarList) ?>

    ```

### Create a new ASYNC request (AJAX)

1. Make the JS call in an `async` function/method:

    > Please not than en error will be thrown by default if the request response is not parsable JSON or its object property `success` is `false`.

    ```js
    (async () => {

        showLoader();

        const response = await fetchRequest({
            url: "index.php?a=[YOUR ASYNC CONTROLLER NAME]"
            data: {
                // YOUR POST DATA HERE
            }
        });

        if (response.msg) {}
        if (response.data) {}

    })();
    ```

1. Create the async controller in `controller/async`:

    The async controller must always return the following object:

    ```text
    {
        success: {boolean}
        msg:     {null|string}
        data:    {null|object}
    }
    ```

    Template:

    ```php
    <?php

    /**
     * DESCRIPTION:
     * [SMALL DESCRIPTION OF THE CURRENT CONTROLLER/PAGE]
     * 
     * INPUT:
     * @param string paramName1  (GET) (OPTIONAL) [PARAMETER DESCRIPTION]
     * @param int    paramName2  (POST)           [PARAMETER DESCRIPTION]
     */

    $success = true;
    $msg = null;
    $data = null;


    // #######
    // PROCESS
    // #######

    // Your async controller page code here.


    // ######
    // OUTPUT
    // ######

    echo json_encode([
        "success" => $success,
        "msg" => $msg,
        "data" => $data,
    ]);
    ```

## Credits

Alexis Philip (checkout my [Github](github.com/alexisphilip)!)
