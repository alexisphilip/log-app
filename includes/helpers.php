<?php

/**
 * Debugs and dies. Pretty prints its parameter content.
 * @param mixed $data Any type accepted, will be pretty printed.
 * @param boolean $die Dies after being outputted.
 */
function dd($data, $die = true) {
    $debug = debug_backtrace();
    echo "<pre style='margin-left: 0.5rem; margin-bottom: 0.5rem;'>";
    echo "<b>" . gettype($data) . "</b> | line " . $debug[0]["line"] . " | <span style='color: #0070c3; font-weight: bold;'>" . $debug[0]["file"] . "</span>";
    echo "</pre>";
    // echo "<br>";
    echo "<pre style='display: inline-block; margin: 0; padding: 0.5rem; background-color: lightgrey;'>";
    print_r($data);
    echo "</pre>";
    if ($die) die;
}

$perfList = [];

/**
 * Starts recording server's time under the given name.
 * @param string $name Name of the performance recording.
 */
function perfStart($name) {
    global $perfList;
    $perfList[$name] = [
        "start" => microtime(true),
        "end" => null,
    ];
}

/**
 * Stops recording server's time. It will be saved under the given name.
 * @param string $name Name of the performance recording.
 */
function perfStop($name) {
    global $perfList;
    $perfList[$name]["end"] = microtime(true);
}

/**
 * Gets all recorded performances.
 * @return array Performance list, classed by names.
 */
function getPerf() {
    global $perfList;
    foreach ($perfList as &$value) {
        if (is_float($value["start"]) && is_float($value["end"])) {
            $value["elapsed"] = round($value["end"] - $value["start"], 3);
        }
    }
    return $perfList;
}

/**
 * Checks if ONE element matches the condition.
 * Equivalent of Array.some() in Javascript.
 * @param array $array Arrays elements to check.
 * @param function $fn Function called back on each iteration.
 * @link {@see https://stackoverflow.com/a/39877269/10607085} answer on StackOverflow
 */
function array_some($array, $fn) {
    if (!empty($array)) {
        foreach ($array as $value) {
            if ($fn($value)) {
                return true;
            }
        }
    }
    return false;
}

/**
 * Checks if ALL elements match the condition.
 * Equivalent of Array.some() in Javascript.
 * @param array $array Arrays elements to check.
 * @param function $fn Function called back on each iteration.
 * @link {@see https://stackoverflow.com/a/39877269/10607085} answer on StackOverflow
 */
function array_every($array, $fn) {
    if (!empty($array)) {
        foreach ($array as $value) {
            if(!$fn($value)) {
                return false;
            }
        }
        return true;
    }
    return false;
}

/**
 * Returns the first found element.
 * - Equivalent of array_search() in PHP, but simpler.
 * - Equivalent of Array.find() in Javascript.
 * @param array $array Arrays elements to check.
 * @param function $fn Function called back on each iteration.
 */
function array_find($array, $fn) {
    if (!empty($array)) {
        foreach ($array as $value) {
            if ($fn($value)) {
                return $value;
            }
        }
    }
    return null;
}

/**
 * Returns a JSON's file content.
 * @param string $filePath
 * @return array
 */
function getJsonFile($filePath) {
    $rawJson = file_get_contents(LOGS_DIR . $filePath);
    $data = json_decode($rawJson, true);
    return is_array($data) ? $data : [];
}
