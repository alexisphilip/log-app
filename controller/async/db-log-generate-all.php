<?php

/**
 * DESCRIPTION:
 * Creates a temporary JSON file containing all logs (minimal data only) to allow faster parsing of all logs (in a single file) instead of parsing all log files individually.
 * 
 *     CONTEXT:
 *     In the LOG file system, all logs per TYPE/DAY are stored into a file.
 *     Ex:
 *          - my-app/cron/2024-01-01.json
 *          - common/test/php/2024-01-03.json
 *          - ...
 *     This means that if there are 10 directories which save logs over 10 days, there will be 100 files.
 *     
 *     PROBLEM:
 *     When fetching logs to make analytics over long periods of time, PHP can take quite some time to fetch all the numerous log files before processing the information.
 *     
 *     SOLUTION:
 *     To make the process faster, we'll create a `log.json` file thanks to our {@link Database} class, then insert one log per line, containing the minimum but mandatory data.
 *     Then, when making analytics, we can query that one and only file much more easily, instead of looping through all the files in the LOG file system.
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

$data = dbLogGenerateAll();


// ######
// OUTPUT
// ######

echo json_encode([
    "success" => $success,
    "msg" => $msg,
    "data" => $data,
]);
