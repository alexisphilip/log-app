<?php

// TODO:
// - TIME OPTIMISATION:
//   - Use JSON-MACHINE to parse logs files WAY FASTER (https://github.com/halaxa/json-machine).
// - LOG LIST:
//   - Filter: make a filter on log type: "log" or "error".
// - LOG CHARTS:
//   - Chart tooltip: make a tooltip with a button which redirects to the log list filtering on the current type/app/interval.
//   - Calendar: include a calendar with total of logs per day (background color on the days which makes a sort of heatmap).
//   - Filter: make a filter on log type: "log" or "error".
// - BREADCRUMB:
//   - Show total logs in directory? if it's not too ressource consuming.
// - USER CONFIG:
//   - Make it savable with JSON DB? sort of login system without password? Where each user would select its little account with its preferences saved.

try {

    session_start();

    // #############
    // PARSES CONFIG
    // #############

    $config = "config.json";
    $mandatoryConfigKeyList = ["LOGS_DIR_PRODUCTION", "LOGS_DIR_DEVELOPMENT", "LOCAL_TIMEZONE", "ORIGIN_COLOR_LIST"];

    if (!file_exists($config)) {
        throw new Exception("Couldn't get config file (`config.json`)");
    }

    $configRaw = file_get_contents($config);
    $config = json_decode($configRaw, true);

    $missingConfigList = array_diff($mandatoryConfigKeyList, array_keys($config));
    if ($missingConfigList) {
        throw new Exception("Missing config keys: " . implode(", ", array_values($missingConfigList)));
    }

    $unknownConfigList = array_diff($mandatoryConfigKeyList, array_keys($config));
    if ($unknownConfigList) {
        throw new Exception("Unknown config keys: " . implode(", ", array_values($unknownConfigList)));
    }

    define("LOGS_DIR_PRODUCTION", $config["LOGS_DIR_PRODUCTION"]);
    define("LOGS_DIR_DEVELOPMENT", $config["LOGS_DIR_DEVELOPMENT"]);
    define("LOCAL_TIMEZONE", $config["LOCAL_TIMEZONE"]);
    define("ORIGIN_COLOR_LIST", $config["ORIGIN_COLOR_LIST"]);

    if ($_SESSION["logEnvironment"] === "DEVELOPMENT") {
        define("LOGS_DIR", LOGS_DIR_DEVELOPMENT);
    } else {
        define("LOGS_DIR", LOGS_DIR_PRODUCTION);
    }


    // ######
    // LOCALE
    // ######

    date_default_timezone_set("UTC");


    // ########
    // INCLUDES
    // ########

    require_once("includes/helpers.php");
    require_once("includes/functions.php");
    require_once("includes/class/App.php");


    // ##########
    // PHP CHECKS
    // ##########

    if (!extension_loaded("mbstring")) {
        throw new Exception("You must load PHP's 'mbstring' extension. Go to the 'php.ini' config file to enable it.");
    }


    // ######
    // ROUTER
    // ######

    $returnJson = false;

    $a = $_GET["a"];
    $p = $_GET["p"];

    if ($a) {
        $fileName = $a;
        $type = "async-controller";
        $path = "controller/async";
        $returnJson = true;
    } elseif ($p) {
        $fileName = $p;
        $type = "controller";
        $path = "controller";
    } else {
        $fileName = "app-list";
        $type = "controller";
        $path = "controller";
    }

    $fileDir = "$path/$fileName.php";

    if (!file_exists($fileDir)) {
        throw new Exception(strtoupper($type) . " '$fileName' does not exist. File not found: '$fileDir'");
    }

    App::require($fileDir);

} catch (\Throwable $th) {

    // #######################################
    // GLOBAL ERROR MANAGEMENT & RETURN VALUES
    // #######################################

    $errorObject = [
        "message" => $th->getMessage(),
        "file" => $th->getFile(),
        "line" => $th->getLine(),
        "trace" => $th->getTrace(),
    ];


    // #######
    // IF JSON
    // #######

    if ($returnJson) {

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            "success" => false,
            "msg" => $th->getMessage(),
            "data" => [
                "errorObject" => $errorObject,
            ],
        ]);

    }

    // #######
    // IF HTML
    // #######

    else {

        ob_end_clean();

        try {

            $tplVarList["errorObject"] = $errorObject;
            App::getTemplate("error", $tplVarList);

        } catch (\Throwable $th) {

            echo "<h1>Error when displaying error template. Original error:</h1>";
            dd($errorObject);

        }

    }

}
