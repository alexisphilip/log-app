<?php

/**
 * DESCRIPTION:
 * Shows various charts for all logs for the selected directory.
 * 
 * INPUT:
 * @param string directory  (GET) (OPTIONAL) Log directory path to fetch the logs from.
 *      Ex: `folder1/folder2`
 * @param string startDate  (GET) (OPTIONAL) Start date to fetch the logs from. Must be given with `endDate`
 *      Ex: "2024-01-01"
 * @param string endDate    (GET) (OPTIONAL) End date to fetch the logs from. Must be given with `startDate`.
 *      Ex: "2024-01-31"
 * @param string dateFilter (GET) (OPTIONAL) Selector string to filter the logs from. Available values:
 *      Ex:
 *      "this-week"
 *      "this-month"
 *      "this-year"
 *      "last-7-days"
 *      "last-30-days"
 *      "last-12-months"
 */

App::setTitle("LOG APP - Charts");


// #######
// PROCESS
// #######

// ###############################
// RESETS SESSION PARAM IF ORDERED
// ###############################

if ($_GET["db-log-generate-all"]) {

    // Resets the session parameter.
    $jsonLogTableName = $_SESSION["logEnvironment"] === "DEVELOPMENT" ? "log-development" : "log";
    unset($_SESSION["$jsonLogTableName-last-saved-date"]);

    // Removes the `db-log-generate-all` from the URL.
    $urlParsed = parse_url($_SERVER["REQUEST_URI"]);
    $urlQuery = $urlParsed["query"];
    parse_str($urlQuery, $params);
    unset($params["db-log-generate-all"]);
    $url = $urlParsed["path"] . "?" . http_build_query($params);

    // Redirects the to the given URL.
    header("Location: $url");
    exit();
}


// ##############
// URL VALIDATION
// ##############

$performanceData = [];

$_GET["directory"] = $_GET["directory"] ?: "";
$tplVarList["selectedDirectoryPath"] = $_GET["directory"];


// #######################
// INITS THE FILE EXPLORER (for the "file-explorer" and "breadcrumb" components)
// #######################

App::require("includes/class/FileExplorer.php");
$fileExplorer = new FileExplorer(LOGS_DIR);
$tplVarList["fileExplorer"] = $fileExplorer;


// ###############################
// GETS ALL LOG DIRECTORY ELEMENTS (for the "file-explorer" component)
// ###############################

$fullDirectory = $fileExplorer->getElements();
$tplVarList["fullDirectory"] = $fullDirectory;


// #####################################
// GETS ALL LOGS FROM `log.json` DB FILE (or generates them in `log.json` before selecting them if the table doesn't exist)
// #####################################

App::require("includes/class/Database.php");

$db = new Database();

$jsonLogTableName = $_SESSION["logEnvironment"] === "DEVELOPMENT" ? "log-development" : "log";
$tplVarList["jsonLogTableName"] = $jsonLogTableName;

// Checks if the logs were saved once.
if ($_SESSION["$jsonLogTableName-last-saved-date"]) {

    $lastSavedDate = $_SESSION["$jsonLogTableName-last-saved-date"];

    if (!((bool) strtotime($lastSavedDate))) {
        throw new Exception("Date fetched from '$jsonLogTableName-last-saved-date' is not a valid date. Found: '$lastSavedDate'");
    }

    $currentDate = (new DateTime())->format("Y-m-d");

    // If the logs were previously saved BEFORE the current date, generates the logs (to make the charts up to date).
    if (strtotime($lastSavedDate) < strtotime($currentDate)) {
        dbLogGenerateAll();
    }

} // If the table was never saved, generates all of the them.
else {
    dbLogGenerateAll();
}


perfStart("table-log-select");

// Gets all logs from the `log` JSON table.
if ($db->tableExists($jsonLogTableName)) {
    $logList = $db->select("*")->from($jsonLogTableName)->execute();
} else {
    $logList = [];
}

perfStop("table-log-select");



// ##########
// TOTAL LOGS (all logs, all time)
// ##########

$tplVarList["totalLogs"] = count($logList);


// ##########################################
// FILTERS THE LOGS ON THE SELECTED DIRECTORY
// ##########################################

// Only gets the log from the current directory.
foreach ($logList as $key => $log) {
    if (substr($log["path"], 0, strlen($_GET["directory"])) !== $_GET["directory"]) {
        unset($logList[$key]);
    }
}



// ###############
// GET ALL ORIGINS (for all the logs in the given directory)
// ###############

$originList = [];

foreach ($logList as $log) {
    if (!in_array($log["origin"], $originList)) {
        $originList[] = $log["origin"];
    }
}

// Sorts them alphabetically.
sort($originList);

$tplVarList["originList"] = $originList;


// ##########
// TOTAL LOGS (for the current selected directory, before date filtering)
// ##########

$totalLogsInSelectedDirectory = count($logList);
$tplVarList["totalLogsInSelectedDirectory"] = $totalLogsInSelectedDirectory;


// ########################
// GETS START AND END DATES (to filter the logs on)
// ########################

if ($_GET["startDate"] && (bool) strtotime($_GET["startDate"]) && $_GET["endDate"] && (bool) strtotime($_GET["endDate"])) {
    $startDate = new DateTime($_GET["startDate"]);
    $endDate = new DateTime($_GET["endDate"]);
    $dateFilterTranslation = $startDate->format("Y-m-d") . " to " . $endDate->format("Y-m-d");
}
else if ($_GET["dateFilter"] === "this-week") {
    $date = new DateTime();
    $startDate = $date->modify("Monday this week");
    $date = new DateTime();
    $endDate = $date->modify("Sunday this week");
    $dateFilterTranslation = "This week";
}
else if ($_GET["dateFilter"] === "this-month") {
    $date = new DateTime();
    $startDate = $date->modify("First day of this month");
    $date = new DateTime();
    $endDate = $date->modify("Last day of this month");
    $dateFilterTranslation = "This month";
}
else if ($_GET["dateFilter"] === "this-year") {
    $date = new DateTime();
    $startDate = $date->modify("First day of January this year");
    $date = new DateTime();
    $endDate = $date->modify("Last day of December this year");
    $dateFilterTranslation = "This year";
}
else if ($_GET["dateFilter"] === "last-7-days") {
    $date = new DateTime();
    $startDate = $date->modify("-6 days");
    $date = new DateTime();
    $endDate = $date;
    $dateFilterTranslation = "Last 7 days";
}
else if ($_GET["dateFilter"] === "last-30-days") {
    $date = new DateTime();
    $startDate = $date->modify("-29 days");
    $date = new DateTime();
    $endDate = $date;
    $dateFilterTranslation = "Last 30 days";
}
else if ($_GET["dateFilter"] === "last-12-months") {
    $date = new DateTime();
    $date->modify("-11 months");
    $startDate = $date->modify("First day of this month");
    $date = new DateTime();
    $endDate = $date->modify("Last day of this month");
    $dateFilterTranslation = "Last 12 months";
} // Defaults to last 30 days.
else {
    $date = new DateTime();
    $startDate = $date->modify("-29 days");
    $date = new DateTime();
    $endDate = $date;
    $dateFilterTranslation = "Last 30 days";
}

// Onces the dates are set, we'll set the starting date to 00:00:00 hours and the ending date to 23:59:59 hours.
if ($startDate) $startDate = new DateTime($startDate->format("Y-m-d 00:00:00"));
if ($endDate) $endDate = new DateTime($endDate->format("Y-m-d 23:59:59"));

$tplVarList["dateFilter"] = $_GET["dateFilter"];
$tplVarList["startDate"] = $startDate;
$tplVarList["endDate"] = $endDate;
$tplVarList["dateFilterTranslation"] = $dateFilterTranslation;


// ####################
// BUILDS THE URL PARTS (for the multiple action buttons)
// ####################

$url = [
    "base" => "index.php?p=chart-list",
    "directory" => "",
    "dateFilter" => "",
    "startDate" => "",
    "endDate" => "",
];

if ($_GET["directory"]) {
    $url["directory"] = "&directory=$_GET[directory]";
}

if ($_GET["dateFilter"]) {
    $url["dateFilter"] = "&dateFilter=$_GET[dateFilter]";
}

if ($_GET["startDate"]) {
    $url["startDate"] = "&startDate=$_GET[startDate]";
}

if ($_GET["endDate"]) {
    $url["endDate"] = "&endDate=$_GET[endDate]";
}

$tplVarList["url"] = $url;


// ###################
// FILTERS ON THE DATE
// ###################

// If a date filter is given, filters the logs on the given date.
if ($startDate && $endDate) {
    foreach ($logList as $key => $log) {
        $logTimestamp = strtotime($log["dateTime"]);
        // If not in the given time interval, remove it.
        if (
            // If before starting date.
            $logTimestamp < $startDate->getTimestamp()
            // If after ending date.
            || $logTimestamp > $endDate->getTimestamp()
            ) {
            unset($logList[$key]);
        }
    }
}


// ##########
// TOTAL LOGS (for the current selected directory, after date filtering)
// ##########

$totalLogsInSelectedDirectoryInSelectedTimeInterval = count($logList);
$tplVarList["totalLogsInSelectedDirectoryInSelectedTimeInterval"] = $totalLogsInSelectedDirectoryInSelectedTimeInterval;


// #################################################
// CHARTS: BUILDS UP DATASETS FOR THE VARIOUS CHARTS
// #################################################

perfStart("chart-list-calculation");

$chartList = [];


// ---------------------
// TOTAL LOGS PER ORIGIN
// ---------------------

$chartList["totalLogPerOrigin"] = [];

foreach ($logList as $log) {
    $log["origin"] = mb_strtolower($log["origin"]);
    if (array_key_exists($log["origin"], $chartList["totalLogPerOrigin"])) {
        $chartList["totalLogPerOrigin"][$log["origin"]]["total"]++;
    } else {
        $chartList["totalLogPerOrigin"][$log["origin"]] = [
            "total" => 0,
            // "color" => ORIGIN_COLOR_LIST[$log["origin"]]["colorDark"] ?: ORIGIN_COLOR_LIST["default"]["colorDark"],
            // "border" => ORIGIN_COLOR_LIST[$log["origin"]]["colorLight"] ?: ORIGIN_COLOR_LIST["default"]["colorLight"],
        ];
    }
}

// ---------------------------------
// TOTAL LOGS PER LAST TWELVE MONTHS
// ---------------------------------


$chartList["logsPerOriginPerDate"] = [];

// Gets the difference in days, which will determine the data precision (display the data by days, or months...).
$totalDaysInSelectedTimeInterval = $startDate->diff($endDate)->days + 1;
$tplVarList["totalDaysInSelectedTimeInterval"] = $totalDaysInSelectedTimeInterval;

// If we need to group totals by DAY.
if ($totalDaysInSelectedTimeInterval <= 31) {
    $dataPrecision = "day";
    $dataSteps = $totalDaysInSelectedTimeInterval;
} // If we need to group totals by MONTH.
else if ($totalDaysInSelectedTimeInterval <= 366) {
    $dataPrecision = "month";
    $startDateDiff = new DateTime($startDate->format("Y-m-d"));
    $endDateDiff = new DateTime($endDate->format("Y-m-d"));
    $startDateDiff = $startDateDiff->modify("First day of this month");
    $endDateDiff = $endDateDiff->modify("First day of next month"); // Gets the first day of the next month to for example: get a 3 months interval when getting the diff between "15th of January" and "3rd of March" (less than ~90 days, but still 3 months of interval).
    $diff = $startDateDiff->diff($endDateDiff);
    $dataSteps = $diff->y * 12 + $diff->m;
} // If over a whole year.
else {
    throw new Exception("Max days interval is 366 (one whole leap year). Given: '$totalDaysInSelectedTimeInterval'");
}


// Generates one entry per steps (days or months) between the two dates (inclusive).
$date = new DateTime($startDate->format("Y-m-d H:i:s"));
for ($i = 0; $i < $dataSteps; $i++) {

    // If we need to group totals by DAY.
    if ($dataPrecision === "day") {
        $matchingDate = $date->format("Y-m-d");
        $label = $date->format("M j");
    } // If we need to group totals by MONTH.
    else {
        $matchingDate = $date->format("Y-m-01");
        $label = $date->format("M Y");
    }

    $chartList["logsPerOriginPerDate"][$matchingDate] = [
        "label" => $label,
    ];

    if ($dataPrecision === "day") {
        $date->modify("+1 days");
    } else {
        $date->modify("+1 months");
    }
}

foreach ($logList as $log) {
    if ((bool) strtotime($log["dateTime"])) {
        $date = new DateTime($log["dateTime"]);

        if ($dataPrecision === "day") {
            $dateFormatted = $date->format("Y-m-d");
        } else {
            $dateFormatted = $date->format("Y-m-01");
        }

        if (array_key_exists($dateFormatted, $chartList["logsPerOriginPerDate"])) {
            $chartList["logsPerOriginPerDate"][$dateFormatted]["datasets"]["total"]++;
            $chartList["logsPerOriginPerDate"][$dateFormatted]["datasets"][$log["origin"]]++;
        }
    }
}


// ----------------------------
// TOTAL LOGS PER HOUR IN A DAY
// ----------------------------

$chartList["logsPerHourInADay"] = [];

$datasetListDefault = [];

// Sets all default datasets labels + values.
foreach (["total", ...$originList] as $datasetLabel) {
    $datasetListDefault[$datasetLabel] = 0;
}

// Sets all hours (00 to 23) and their default datasets.
for ($i = 0; $i < 24; $i++) {
    $hour = $i < 10 ? "0$i" : "$i";
    $chartList["logsPerHourInADay"][$hour] = [
        "hour" => $hour,
        "datasetList" => $datasetListDefault,
    ];
}

// Gets the total of logs per hour per origin (+ total).
foreach ($logList as $log) {
    if ((bool) strtotime($log["dateTime"])) {
        $date = new DateTime($log["dateTime"]);
        $hour = $date->format("H");
        $chartList["logsPerHourInADay"][$hour]["datasetList"]["total"]++;
        $chartList["logsPerHourInADay"][$hour]["datasetList"][$log["origin"]]++;
    }
}

// Once all the totals per hour are fetched, makes them average.
foreach ($chartList["logsPerHourInADay"] as &$hourlyStats) {
    // For each origins (+ total).
    foreach ($hourlyStats["datasetList"] as &$dataset) {
        $dataset = round($dataset / $totalDaysInSelectedTimeInterval, 2);
    }
}

// Resets the array keys, so it comes in order (as a normal array) when is it parsed by JS.
$chartList["logsPerHourInADay"] = array_values($chartList["logsPerHourInADay"]);


// ---------------------------
// TOP LOG VOLUME PER INSTANCE
// ---------------------------

$chartList["logVolumePerInstance"] = [];

// Gets the total logs per instance.
foreach ($logList as $log) {
    $chartList["logVolumePerInstance"][$log["idInstance"]]++;
}

// Transforms these totals it into percentages of the total of logs.
foreach ($chartList["logVolumePerInstance"] as $idInstance => &$logVolume) {
    $logVolume = [
        "idInstance" => $idInstance,
        "logVolume" => round($logVolume / $totalLogsInSelectedDirectoryInSelectedTimeInterval * 100, 2),
        "logTotal" => $logVolume,
    ];
}

// Sorts array by descending log volume (by total of logs per instances).
usort($chartList["logVolumePerInstance"], function ($a, $b) {
    return $b["logVolume"] > $a["logVolume"];
});


// -----------------
// CHARTS, ASSEMBLE!
// -----------------

$tplVarList["chartList"] = $chartList;

perfStop("chart-list-calculation");

$tplVarList["perfList"] = getPerf();

// ######
// OUTPUT
// ######

App::setJs("view/chart-list.js");

App::require("includes/components/breadcrumb.php");
App::require("includes/components/file-explorer.php");

App::getTemplate("chart-list", $tplVarList);
