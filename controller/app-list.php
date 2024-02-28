<?php

/**
 * DESCRIPTION:
 * Lists all logs folders in a pretty way.
 * 
 * INPUT:
 * No input required.
 */

App::setTitle("LOG APP - Home");


// #######
// PROCESS
// #######

App::require("includes/class/FileExplorer.php");

// Inits the file explorer to the logs directory.
$fileExplorer = new FileExplorer(LOGS_DIR);
$tplVarList["fileExplorer"] = $fileExplorer;


// ###############################
// GETS ALL LOG DIRECTORY ELEMENTS
// ###############################

$fullDirectory = $fileExplorer->getElements();


// #############################################
// GET TOTAL LOGS PER DIRECTORY FOR TODAY'S DATE
// #############################################

$currentDate = (new DateTime())->format("Y-m-d");

// For each app types.
foreach ($fullDirectory as &$directoryElement) {

    // For each apps.
    foreach ($directoryElement["content"] as &$subDirectoryElement) {

        $subDirectoryElement["totalLogs"] = 0;

        $subDirectoryElements = $fileExplorer->getElementsFlat($subDirectoryElement["path"]);

        // For each directory elements in the current app.
        foreach ($subDirectoryElements as $subSubDirectoryElement) {
            $fileName = "$currentDate.json";
            // If it's a file and is of the current date.
            if ($subSubDirectoryElement["type"] === "file" && $subSubDirectoryElement["name"] === $fileName) {
                // Count how many logs it contains, and adds it total to the total logs on the current app.
                $subDirectoryElement["totalLogs"] += count(getJsonFile($subSubDirectoryElement["path"]));
            }
        }
    }
}

$tplVarList["fullDirectory"] = $fullDirectory;


// ######
// OUTPUT
// ######

App::setJs("view/app-list.js");

App::getTemplate("app-list", $tplVarList);
