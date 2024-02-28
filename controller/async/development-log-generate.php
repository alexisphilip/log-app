<?php

/**
 * DESCRIPTION:
 * Randomly creates development log files (`.json`) for each folders, for the past n days.
 * 
 * INPUT:
 * @param int daysToCreatePerSubDirectories (POST) Days to create per subdirectory. If you have 8 subdirectories, it will attempt to create a random set of logs over `n` days in each subdirectories.
 * @param int minLogsToCreatePerFile        (POST) Minimum logs to create per file. The number will be randomly chosen.
 * @param int maxLogsToCreatePerFile        (POST) Maximum logs to create per file. The number will be randomly chosen.
 */

$success = true;
$msg = null;
$data = null;


// ##########
// VALIDATION
// ##########

$_POST["daysToCreatePerSubDirectories"] = intval($_POST["daysToCreatePerSubDirectories"]);
$_POST["minLogsToCreatePerFile"] = intval($_POST["minLogsToCreatePerFile"]);
$_POST["maxLogsToCreatePerFile"] = intval($_POST["maxLogsToCreatePerFile"]);

if ($_POST["daysToCreatePerSubDirectories"] < 1 || $_POST["daysToCreatePerSubDirectories"] > 731) {
    throw new Exception("`daysToCreatePerSubDirectories` must be between 1 and 731 (inclusive)");
}

if ($_POST["minLogsToCreatePerFile"] < 0 || $_POST["minLogsToCreatePerFile"] > 100) {
    throw new Exception("`minLogsToCreatePerFile` must be between 0 and 100 (inclusive)");
}

if ($_POST["maxLogsToCreatePerFile"] < 0 || $_POST["maxLogsToCreatePerFile"] > 100) {
    throw new Exception("`maxLogsToCreatePerFile` must be between 0 and 100 (inclusive)");
}


// ######
// CONFIG
// ######

// Number of day files to generate for each origins.
define("DAYS_TO_CREATE_PER_SUB_DIRECTORIES", $_POST["daysToCreatePerSubDirectories"]);

// Number of logs to create in a single file (one file = one day in the current directory).
define("MIN_LOGS_TO_CREATE_PER_FILE", $_POST["minLogsToCreatePerFile"]);
define("MAX_LOGS_TO_CREATE_PER_FILE", $_POST["maxLogsToCreatePerFile"]);

// Directory structure to generate (3 LEVELS ONLY ARE REQUIRED, 2 OR 4 WILL NOT WORK).
define("DIRECTORY", [
    "common" => [
        "cron" => [
            "cron",
        ],
        "web-service" => [
            "sql",
        ],
    ],
    "mobile" => [
        "app1" => [
            "js",
            "php",
            "sql",
        ],
        "app2" => [
            "cron",
            "sql",
        ],
    ],
    "web" => [
        "app1" => [
            "cron",
            "js",
            "php",
            "sql",
        ]
    ],
]);

define("ERROR_MESSAGE_LIST", [
    "js" => [
        "fetch() did not return readable JSON",
        "Invalid input",
        "testTest is not defined",
        "User is not defined",
        "Vehicle is not defined",
        "Cannot read properties of undefined (reading 'name')",
        "Cannot read properties of null (reading 'style')",
        "Cannot read properties of undefined (reading 'id')",
        "",
    ],
    "php" => [
        "Undefined constant \"d\"",
        "Failed opening required 'lib.php' (include_path='.;C:\php\pear')",
        "Syntax error, unexpected '{' in <path>",
        "Call to undefined function myFunction()",
        "Call to undefined class Database",
        "Include(jtp.php): failed to open stream: No such file or directory in C:\www\my-app\controller\\file.php",
        "Include(libraries.php): failed to open stream: No such file or directory in C:\www\my-app\\vendors",
        "Undefined variable: automobile in C:\www\my-app\\view\\my-view.php",
        "Undefined variable: testTest in C:\www\my-app\\view\\file.php",
        "",
    ],
    "sql" => [
        "SQLSTATE[42000]: [Microsoft][ODBC Driver 13 for SQL Server][SQL Server]Error converting data type nvarchar to int.",
        "SQLSTATE[42000]: [Microsoft][ODBC Driver 13 for SQL Server][SQL Server]Error converting data type nchar to int.",
        "SQLSTATE[42000]: [Microsoft][ODBC Driver 13 for SQL Server][SQL Server]Error converting data type int to nvarchar.",
        "SQLSTATE[42S22]: [Microsoft][ODBC Driver 13 for SQL Server][SQL Server]Invalid column name 'test_test'.",
        "SQLSTATE[42S22]: [Microsoft][ODBC Driver 13 for SQL Server][SQL Server]Invalid column name 'user_connect_date'.",
        "SQLSTATE[42S22]: [Microsoft][ODBC Driver 13 for SQL Server][SQL Server]Invalid column name 'id_user'.",
        "SQLSTATE[42S22]: [Microsoft][ODBC Driver 13 for SQL Server][SQL Server]Invalid column name 'id_vehicle'.",
        "SQLSTATE[42S22]: [Microsoft][ODBC Driver 13 for SQL Server][SQL Server]Invalid column name 'order_date'.",
        "SQLSTATE[42S22]: [Microsoft][ODBC Driver 13 for SQL Server][SQL Server]Invalid column name 'total'.",
        "SQLSTATE[42000]: [Microsoft][ODBC Driver 13 for SQL Server][SQL Server]Could not find stored procedure 'test_sp_get'.",
        "SQLSTATE[42000]: [Microsoft][ODBC Driver 13 for SQL Server][SQL Server]Could not find stored procedure 'user_get'.",
        "SQLSTATE[42000]: [Microsoft][ODBC Driver 13 for SQL Server][SQL Server]Could not find stored procedure 'order_get'.",
        "",
    ],
    "cron" => [
        "Failed opening required 'lib.php' (include_path='.;C:\php\pear')",
        "Failed opening required 'class/Vehicle.php' (include_path='.;C:\php\pear')",
        "Failed opening required 'class/User.php' (include_path='.;C:\php\pear')",
        "Failed opening required 'class/Database.php' (include_path='.;C:\php\pear')",
        "Undefined variable: testTest in C:\www\my-app\\cron\\file.php",
        "Cannot connect to database: invalid credentials",
        "Include(libraries.php): failed to open stream: No such file or directory in C:\www\my-app\\vendors",
        "Fatal error: Uncaught Error: Call to undefined class Database",
        "",
    ],
]);


// #######
// PROCESS
// #######

perfStart("generate-logs");

if (!is_dir(LOGS_DIR_DEVELOPMENT)) {
    mkdir(LOGS_DIR_DEVELOPMENT);
}

// SETS 10 MINUTES MAX EXECUTION TIME
set_time_limit(600);

function getRandomWeightedHour() {

    $midnightWeight = 6;
    $workingHoursWeight = 4;

    $weightPerHours = [
        0 => $midnightWeight, // Weight 3 for 00 hours
        8 => $workingHoursWeight, // Weight 2 for 8 to 12 hours
        9 => $workingHoursWeight,
        10 => $workingHoursWeight,
        11 => $workingHoursWeight,
        14 => $workingHoursWeight, // Weight 2 for 14 to 17 hours
        15 => $workingHoursWeight,
        16 => $workingHoursWeight,
        17 => $workingHoursWeight
    ];

    // Adds the "weight=1" to the remaining hours.
    for ($i = 0; $i <= 23; $i++) {
        if (!array_key_exists($i, $weightPerHours)) {
            $weightPerHours[$i] = 1;
        }
    }

    // Gather all hours with weights
    $weightedHours = [];
    foreach ($weightPerHours as $hour => $weight) {
        for ($i = 0; $i < $weight; $i++) {
            $weightedHours[] = $hour;
        }
    }

    // Randomly select an hour from the weighted array
    $randomIndex = random_int(0, count($weightedHours) - 1);
    $randomHour = $weightedHours[$randomIndex];
    
    $randomHour = $randomHour < 10 ? "0$randomHour" : "$randomHour";

    return $randomHour;
}

$days = [];

// Generates X days in YYYY-MM-DD format
$date = new DateTime();
for ($i = 0; $i < DAYS_TO_CREATE_PER_SUB_DIRECTORIES; $i++) {
    $days[] = $date->format("Y-m-d");
    $date->modify("-1 days");
}

$totalLogs = 0;
$totalFiles = 0;

$timeZoneList = timezone_identifiers_list();
        
// FOR EACH "LEVEL 1" FOLDERS
foreach (DIRECTORY as $level1FolderName => $level1FolderContent) {

    // FOR EACH "LEVEL 2" FOLDERS
    foreach ($level1FolderContent as $level2FolderName => $level2FolderContent) {

        // FOR EACH "LEVEL 3" FOLDERS
        foreach ($level2FolderContent as $level3FolderName) {

            // For each days to generate the logs to.
            foreach ($days as $day) {

                // Creates the log file path.
                $fileDir = "$level1FolderName/$level2FolderName/$level3FolderName";
                $fileName = "$day.json";
                $filePath = "$fileDir/$fileName";

                // Will contain all the logs in the file above.
                $logList = [];

                // For each logs to insert in the file above.
                for ($i = 1; $i <= random_int(MIN_LOGS_TO_CREATE_PER_FILE, MAX_LOGS_TO_CREATE_PER_FILE); $i++) {

                    $date = new DateTime($day);
                    $date->setTime(getRandomWeightedHour(), random_int(0, 59), random_int(0, 59));

                    // TRACE: 4/5 chances

                    $trace = null;
                    $file = null;
                    $line = null;
                    $column = null;
                    if (random_int(1, 5) > 1) {
                        $trace = [
                            [
                                "file" => "folder/directory/myFile.php",
                                "line" => random_int(1, 1000),
                                "column" => random_int(1, 60),
                                "args" => "array(1) {\n[0] => string(10) \"/tmp/a.php\"\n}",
                                "function" => 'string(12) "include_once"',
                            ],
                            [
                                "file" => "app/myFolder/myDir/otherFile.php",
                                "line" => random_int(1, 20),
                                "args" => "array(1) {\n[0] => string(10) \"/tmp/a.php\"\n}",
                                "function" => 'string(12) "include_once"',
                            ],
                            [
                                "file" => "app/myFolder/myDir/otherFile.php",
                                "line" => random_int(1, 20),
                                "args" => "array(1) {\n[0] => string(10) \"/tmp/a.php\"\n}",
                                "function" => 'string(12) "include_once"',
                            ],
                            [
                                "file" => "app/myFolder/myDir/otherFile.php",
                                "line" => random_int(1, 20),
                                "args" => "array(1) {\n[0] => string(10) \"/tmp/a.php\"\n}",
                                "function" => 'string(12) "include_once"',
                            ],
                        ];
                        $file = $trace[0]["file"];
                        $line = $trace[0]["line"];
                        $column = $trace[0]["column"];
                    }

                    // DATA: 1/5 chances

                    $data = null;
                    if (random_int(1, 5) > 4) {
                        $data = [
                            "obj" => "my data",
                            "custom_data" => [
                                "little" => "custom",
                                "data" => "object",
                                "total" => 13487,
                                "hey" => true,
                            ],
                            "yes",
                        ];
                    }

                    // MESSAGE

                    // Gets the available messages for the current origin, then gets a random one.
                    $messageList = ERROR_MESSAGE_LIST[$level3FolderName];
                    if ($messageList) {
                        $message = $messageList[random_int(0, count($messageList) - 1)];
                    } else {
                        $message = "Default error message";
                    }

                    // TIMEZONE
                    $timeZone = $timeZoneList[random_int(0, count($timeZoneList) - 1)];

                    $log = [];
                    $log["id"] = bin2hex(random_bytes(8));
                    $log["dateTime"] = $date->format("Y-m-d H:i:s");
                    $log["timeZone"] = $timeZone;
                    $log["type"] = "error";
                    $log["appTypeName"] = $level1folderName;
                    $log["appName"] = $level2FolderName;
                    $log["origin"] = $level3FolderName;
                    $log["message"] = $message;
                    $log["file"] = $file;
                    $log["line"] = $line;
                    $log["column"] = $column;
                    $log["trace"] = $trace;
                    $log["data"] = $data;
                    $log["idInstance"] = random_int(1, 100);

                    $logList[] = $log;

                    $totalLogs++;
    
                }

                // If there are no logs to save, does not create the file.
                if (count($logList) === 0) {
                    continue;
                }

                // Creates the dir if it doesn't exist.
                if (!is_dir(LOGS_DIR_DEVELOPMENT . $fileDir)) {
                    mkdir(LOGS_DIR_DEVELOPMENT . $fileDir, 0777, true);
                }

                // Creates the file if it doesn't exist (it should never exist, since we're creating it for the first time).
                if (!file_exists(LOGS_DIR_DEVELOPMENT . $filePath)) {
                    touch(LOGS_DIR_DEVELOPMENT . $filePath);
                }

                // Adds content into the current file.
                file_put_contents(LOGS_DIR_DEVELOPMENT . $filePath, json_encode($logList));

                $totalFiles++;
            }
        }
    }
}

perfStop("generate-logs");

$data["totalFiles"] = $totalFiles;
$data["totalLogs"] = $totalLogs;
$data["perfData"] = getPerf();


// ######
// OUTPUT
// ######

echo json_encode([
    "success" => $success,
    "msg" => $msg,
    "data" => $data,
]);
