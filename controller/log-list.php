<?php

/**
 * DESCRIPTION:
 * Lists all logs for the selected directory.
 * 
 * INPUT:
 * @param string directory (GET) (OPTIONAL) Log directory path to fetch the logs from.
 *      Ex: `folder1/folder2`
 * @param string date      (GET) (OPTIONAL) Date to fetch the logs from.
 *      Ex: "2024-01-01"
 */

App::setTitle("LOG APP - Logs");


// #######
// PROCESS
// #######

$_GET["directory"] = $_GET["directory"] ?: "";


// ####################
// BUILDS THE URL PARTS (for the multiple action buttons)
// ####################

$url = [
    "base" => "index.php?p=log-list",
    "directory" => "",
    "date" => "",
    "search" => "",
    "originList" => "",
];

if ($_GET["directory"]) {
    $url["directory"] = "&directory=$_GET[directory]";
}

if ($_GET["date"]) {
    $url["date"] = "&date=$_GET[date]";
}

if ($_GET["search"]) {
    $url["search"] = "&search=$_GET[search]";
}

if ($_GET["originList"]) {
    $url["originList"] = "&originList=$_GET[originList]";
}

$tplVarList["url"] = $url;


// #######################
// INITS THE FILE EXPLORER (for the "file-explorer" and "breadcrumb" components)
// #######################

App::require("includes/class/FileExplorer.php");

// Inits the file explorer to the logs directory.
$fileExplorer = new FileExplorer(LOGS_DIR);
$tplVarList["fileExplorer"] = $fileExplorer;


// ###############################
// GETS ALL LOG DIRECTORY ELEMENTS
// ###############################

$fullDirectory = $fileExplorer->getElements();
$tplVarList["fullDirectory"] = $fullDirectory;


// ############################################
// GETS ALL LOG FILES IN THE SELECTED DIRECTORY
// ############################################

$selectedLogFiles = [];

$selectedDirectoryPath = $_GET["directory"] ?: "";
$tplVarList["selectedDirectoryPath"] = $selectedDirectoryPath;

foreach ($fileExplorer->getElementsFlat($selectedDirectoryPath) as $folderOrFile) {
    if ($folderOrFile["type"] === "file") {
        $selectedLogFiles[] = $folderOrFile;
    }
}

$tplVarList["selectedLogFiles"] = $selectedLogFiles;


// ###################################
// GETS THE DATE TO FILTER THE LOGS ON
// ###################################

// If a valid date is set in the URL.
if ($_GET["date"] && (bool) strtotime($_GET["date"])) {
    $dateObj = new DateTime($_GET["date"]);
} // If no date from URL or not valid, gets today's date.
else {
    if ($_GET["date"]) $tplVarList["isCurrentDateInvalid"] = true;
    $dateObj = new DateTime();
}

// Formats it to YYYY-MM-DD.
$currentDate = $dateObj->format("Y-m-d");
$tplVarList["currentDate"] = $currentDate;


// #################################
// GET ALL THE LOG IN EACH LOG FILES
// #################################

$logList = [];

// Builds the logs files name to get, ex: `2024-01-01.json`
$logFileName = "$currentDate.json";

// Gets each log files content, for the current date.
foreach ($selectedLogFiles as $logFile) {
    if ($logFile["name"] === $logFileName) {
        $fileLogList = getJsonFile($logFile["path"]);
        $logList = array_merge($logList, $fileLogList);
    }
}


// ############################
// APPLIES CHANGES TO EACH LOGS
// ############################

$originList = [];

// Applies changes to each logs.
foreach ($logList as &$log) {

    $log["origin"] = mb_strtolower($log["origin"]);

    if (!in_array($log["origin"], $originList)) $originList[] = $log["origin"];

    if ($log["dateTime"]) {
        
        // Date time: UTC
        $date = new DateTime($log["dateTime"]);
        $log["dateTimeUtcOffset"] = 0;
        $log["dateTimeUtc"] = $date->format("Y-m-d H:i:s");
        $log["dateTimeUtcTimeZone"] = "UTC";

        // Date time: instance time, which is set by the given log `timeZone`.
        if ($log["timeZone"]) {
            $date = new DateTime($log["dateTime"]);
            $date->setTimezone(new DateTimeZone($log["timeZone"]));
            $log["dateTimeInstanceOffset"] = $date->getOffset() / 3600;
            $log["dateTimeInstance"] = $date->format("Y-m-d H:i:s");
            $log["dateTimeInstanceTimeZone"] = $log["timeZone"];
        }

        // Date time: LOCAL: is set thanks to the app config.
        $date = new DateTime($log["dateTime"]);
        $date->setTimezone(new DateTimeZone(LOCAL_TIMEZONE));
        $log["dateTimeLocalOffset"] = $date->getOffset() / 3600;
        $log["dateTimeLocal"] = $date->format("Y-m-d H:i:s");
        $log["dateTimeLocalTimeZone"] = LOCAL_TIMEZONE;
    }
}

// Orders them by most recent.
usort($logList, function ($a, $b) {
    return strtotime($b["dateTime"]) - strtotime($a["dateTime"]);
});

$tplVarList["logList"] = $logList;
$tplVarList["originList"] = $originList;


// ######
// OUTPUT
// ######

App::require("includes/components/breadcrumb.php");
App::require("includes/components/file-explorer.php");

App::setJs("class/Table.js");
App::setJs("view/log-list.js");

App::getTemplate("log-list", $tplVarList);
