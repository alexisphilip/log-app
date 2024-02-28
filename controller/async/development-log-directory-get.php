<?php

/**
 * DESCRIPTION:
 * Recursively gets all the folders & total of files in each directory of the development logs.
 * 
 * INPUT:
 * No input required.
 */

$success = true;
$msg = null;
$data = null;


// #######
// PROCESS
// #######

App::require("includes/class/FileExplorer.php");
$fileExplorer = new FileExplorer(LOGS_DIR_DEVELOPMENT);

$data["directoryElementList"] = $fileExplorer->getElements();

$totalFiles = 0;
// $totalLogs = 0;

foreach ($fileExplorer->getElementsFlat() as $element) {
    if ($element["type"] === "file") {
        $totalFiles++;
        // $totalLogs += count(getJsonFile($element["path"]));
    }
}

$data["totalFiles"] = $totalFiles;
// $data["totalLogs"] = $totalLogs;


// ######
// OUTPUT
// ######

echo json_encode([
    "success" => $success,
    "msg" => $msg,
    "data" => $data,
]);
