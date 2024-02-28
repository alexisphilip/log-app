<?php

/**
 * DESCRIPTION:
 * Updates the `logEnvironment` session variable to "DEVELOPMENT" or unsets it.
 * 
 * INPUT:
 * @param string logEnvironment (POST) Determines which from which folders all the logs in the app should be parsed from.
 *      - "DEVELOPMENT": all the logs will be parsed from the directory set in `config.json` under `LOGS_DIR_DEVELOPMENT`.
 *      - null:          all the logs will be parsed from the directory set in `config.json` under `LOGS_DIR_PRODUCTION`.
 */

$success = true;
$msg = null;
$data = null;


// #######
// PROCESS
// #######

if ($_POST["logEnvironment"] === "DEVELOPMENT") {
    $_SESSION["logEnvironment"] = "DEVELOPMENT";
} else {
    unset($_SESSION["logEnvironment"]);
}


// ######
// OUTPUT
// ######

echo json_encode([
    "success" => $success,
    "msg" => $msg,
    "data" => $data,
]);
