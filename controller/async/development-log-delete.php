<?php

/**
 * DESCRIPTION:
 * Deletes the development log directory recursively.
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

function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
            if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                rrmdir($dir. DIRECTORY_SEPARATOR .$object);
            else
                unlink($dir. DIRECTORY_SEPARATOR .$object);
            } 
        }
        rmdir($dir);
    } 
}

rrmdir(LOGS_DIR_DEVELOPMENT);


// ######
// OUTPUT
// ######

echo json_encode([
    "success" => $success,
    "msg" => $msg,
    "data" => $data,
]);
