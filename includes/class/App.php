<?php

/**
 * Application's main class, containing the most important and common functions such as:
 * - Setting the page's title.
 * - Getting a template (VIEW).
 * - Setting a CSS/JS/IMG/vendor file for the VIEW.
 * - Etc...
 */
class App {
    /**
     * The current HTML's page title.
     * @var string
     */
    public static $pageTitle;
    /**
     * All the static files (CSS, JS, IMG) to add to the DOM.
     * @var array
     */
    public static $staticFileList = [];

    // ############
    // FILE METHODS
    // ############

    /**
     * Includes a file or throws an Exception if it doesn't exist.
     * Wrapper of PHP's native function `require_once`.
     * @param string $fileDir Full path of the file to include.
     */
    public static function require($fileDir) {
        if (file_exists($fileDir)) {
            require_once($fileDir);
        } else {
            throw new Exception("Failed opening required file: '$fileDir'");
        }
    }

    // ################
    // TEMPLATE METHODS
    // ################

    /**
     * Sets the HTML's page title.
     * @param string $title HTML page's title.
     */
    public static function setTitle($title) {
        self::$pageTitle = $title;
    }

    /**
     * Includes the given template file inside an object buffer catcher, to be able to clean the buffer output in case an error occurs. 
     * @param string $viewName View name.
     * @param array $tplVarList Associative array of key (variable name) and value (variable content) to use in the template.
     */
    public static function getTemplate($viewName, $tplVarList = []) {

        if (!file_exists("view/$viewName.php")) {
            throw new Exception("'$viewName' view doesn't exist. File not found: 'view/$viewName.php'");
        }

        foreach ($tplVarList as $variable => $value) {
            ${$variable} = $value;
        }

        ob_start();

        require_once("view/$viewName.php");

        ob_end_flush();
    }

    /**
     * Sets a JS file to include in the DOM.
     * @param string $fileName JS file name to include.
     * @example 
     * App::setJs("view/my-script.js");
     * App::setJs("class/MyClass.js");
     */
    public static function setJs($fileName) {
        self::$staticFileList["js"][] = $fileName;
    }

    /**
     * Gets a JS file full path.
     * @param string $fileName JS file name to get.
     * @return string JS file's full path.
     */
    public static function getJs($fileName) {
        return self::getStatic("js", $fileName);
    }

    /**
     * Gets a IMG file full path.
     * @param string $fileName IMG file name to get.
     * @return string IMG file's full path.
     */
    public static function getImg($fileName) {
        return self::getStatic("img", $fileName);
    }

    /**
     * Gets a CSS file full path.
     * @param string $fileName CSS file name to get.
     * @return string CSS file's full path.
     */
    public static function getCss($fileName) {
        return self::getStatic("css", $fileName);
    }

    /**
     * Gets a vendor file full path.
     * @param string $fileName Vendor file name to get.
     * @return string Vendor file's full path.
     */
    public static function getVendor($fileName) {
        return self::getStatic("vendors", $fileName);
    }

    /**
     * Gets a static file full path.
     * @param string $type Static's file type to get ("js", "img", "css" OR "vendors").
     * @param string $fileName Static's file name to get.
     * @return string Static file's full path.
     */
    private static function getStatic($type, $fileName) {
        $fileDir = "static/" . strtolower($type) . "/$fileName";
        if (file_exists($fileDir)) {
            return $fileDir;
        } else {
            throw new Exception(strtoupper($type) . " file does not exists: '$fileDir'");
        }
    }
}
