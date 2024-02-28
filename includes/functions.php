<?php

/**
 * CONTEXT:
 * In the LOG file system, all logs per TYPE/DAY are stored into a file.
 * Ex:
 *      - my-app/cron/2024-01-01.json
 *      - common/test/php/2024-01-03.json
 *      - ...
 * This means that if there are 10 directories which save logs over 10 days, there will be 100 files.
 * 
 * PROBLEM:
 * When fetching logs to make analytics over long periods of time, PHP can take quite some time to fetch all the numerous log files before processing the information.
 * 
 * SOLUTION:
 * To make the process faster, we'll create a `log.json` file thanks to our {@link Database} class, then insert one log per line, containing the minimum but mandatory data.
 * Then, when making analytics, we can query that one and only file much more easily, instead of looping through all the files in the LOG file system.
 */
function dbLogGenerateAll() {

    // ######################################
    // GETS ALL LOGS FROM THE LOG FILE SYSTEM
    // ######################################

    perfStart("file-system-parse-all-element");

    App::require("includes/class/FileExplorer.php");

    // Inits the file explorer.
    $fileExplorer = new FileExplorer(LOGS_DIR);

    // Gets all the log directory elements (folders + files) in a flat array.
    $dirElementList = $fileExplorer->getElementsFlat();

    perfStop("file-system-parse-all-element");

    perfStart("file-system-parse-all-logs");

    $logDataList = [];

    // Gets all of the existing logs.
    // For each log directory element.
    foreach ($dirElementList as &$folderOrFile) {
        // If it's a file.
        if ($folderOrFile["type"] === "file") {
            // Gets its log content.
            $logList = getJsonFile($folderOrFile["path"]);
            // For each logs in the current log file, add some important data into the data array.
            foreach ($logList as $log) {
                $logDataList[] = [
                    "id" => $log["id"],
                    "timestamp" => strtotime($log["dateTime"]),
                    "dateTime" => $log["dateTime"],
                    "timeZone" => $log["timeZone"],
                    "idInstance" => $log["idInstance"],
                    "path" => $folderOrFile["path"],
                    "origin" => $log["origin"],
                    "type" => $log["type"],
                ];
            }
        }
    }

    usort($logDataList, function ($a, $b) {
        return $a["timestamp"] - $b["timestamp"];
    });

    perfStop("file-system-parse-all-logs");



    // ####################################
    // SAVES THE LOGS DATA INTO THE JSON DB
    // ####################################

    App::require("includes/class/Database.php");

    $db = new Database();

    $tableName = $_SESSION["logEnvironment"] === "DEVELOPMENT" ? "log-development" : "log";

    // Deletes the current `log` table (if it exists).
    perfStart("table-log-delete");
    $db->deleteTable($tableName);
    perfStop("table-log-delete");

    // Inserts the logs in the `log` table.
    perfStart("table-log-insert");
    if (count($logDataList)) {
        $db->insert($tableName)->values($logDataList)->execute();
    }
    perfStop("table-log-insert");

    $data["performanceData"] = getPerf();


    // Saves the current date as the last time the LOG-DB was updated.
    $_SESSION["$tableName-last-saved-date"] = (new DateTime())->format("Y-m-d");


    return $data;
}
