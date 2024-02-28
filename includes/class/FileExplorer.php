<?php

/**
 * Parses a whole directory for folders and files.
 */
class FileExplorer {
    /**
     * @var string Root's directory to look the folders and files from.
     */
    private $rootDirectory;
    /**
     * Directory's content (FOLDERS & FILES) as a RECURSIVE array.
     * @var array[][]
     */
    private $directoryRecursive;
    /**
     * Directory's content (FOLDERS & FILES) as a FLAT array.
     * @var array[]
     */
    private $directoryFlat;

    /**
     * Constructs a new {@link FileExplorer}'s instance.
     * @param string $rootDirectory Root's directory to look the folders and files from.
     */
    public function __construct($rootDirectory) {

        $this->rootDirectory = $rootDirectory;

        // Recursively gets all the directory's content (FOLDERS & FILES) and formats adds data to each elements (type, name, path...).
        // Also adds each directory in the `directoryFlat` property.
        $this->directoryRecursive = $this->scanGetDirectory();
    }

    // ##############
    // PUBLIC METHODS
    // ##############

    /**
     * Directory's content (FOLDERS & FILES) as a RECURSIVE array.
     * @param null|string $path If given, gets the directory's content from that path, not above.
     * @return array[][] RECURSIVE directory's content (FOLDERS & FILES).
     * [
     *     [
     *         "name" => "my-folder",
     *         "path" => "my-folder",
     *         "type" => "folder",
     *         "content" => [
     *             [
     *                 "name" => "my-file",
     *                 "path" => "my-folder/my-file",
     *                 "type" => "file"
     *             ],
     *             [
     *                 "name" => "other-file",
     *                 "path" => "my-folder/other-file",
     *                 "type" => "file"
     *             ]
     *         ]
     *     ],
     *     [
     *         "name" => "other-folder",
     *         "path" => "other-folder,
     *         "type" => "folder",
     *         "content" => []
     *     ],
     * ]
     */
    public function getElements($path = null) {
        if ($path) {
            return $this->findRecursive($path);
        } else {
            return $this->directoryRecursive;
        }
    }

    /**
     * Directory's content (FOLDERS & FILES) as a FLAT array.
     * @param null|string $path If given, gets the directory's content from that path, not above.
     * @return array[] FLAT directory's content (FOLDERS & FILES).
     * [
     *     [
     *         "name" => "my-folder",
     *         "path" => "my-folder",
     *         "type" => "folder"
     *     ],
     *     [
     *         "name" => "my-file",
     *         "path" => "my-folder/my-file",
     *         "type" => "file"
     *     ],
     *     [
     *         "name" => "other-file",
     *         "path" => "my-folder/other-file",
     *         "type" => "file"
     *     ],
     *     [
     *         "name" => "other-folder",
     *         "path" => "other-folder,
     *         "type" => "folder"
     *     ],
     * ]
     */
    public function getElementsFlat($path = null) {
        if ($path) {
            $directoryElementList = [];
            foreach ($this->directoryFlat as $key => $value) {
                if (substr($key, 0, strlen($path)) === $path) {
                    $directoryElementList[$key] = $value;
                }
            }
            return $directoryElementList ?: null;
        } else {
            return $this->directoryFlat;
        }
    }

    // ###############
    // PRIVATE METHODS
    // ###############

    /**
     * Finds an element from the given path in the current directory.
     * Only works if the PHP file structure object is already initiated {@link self->directoryRecursive} by {@link self->scanGetDirectory}.
     * @param string $path Directory path to search (FOLDER OR FILE).
     * @param string $relativeDirectory If given, gets the directory's content from that path, not above.
     * @return array The searched directory element (`$path`) (FOLDER OR FILE).
     */
    private function findRecursive($path, $relativeDirectory = null) {

        // I: "folder1/folder2/file"
        // O: ["folder1", "folder2", "file"]
        $pathExploded = explode("/", $path);

        // I: ["folder1", "folder2", "file"]
        // O: "folder1"
        $pathFirstEl = array_shift($pathExploded);

        // If relative directory is null, we'll start the root directory.
        if (!$relativeDirectory) {
            $relativeDirectory = $this->directoryRecursive;
        }

        $directoryElement = $relativeDirectory[$pathFirstEl];

        if ($directoryElement) {
            if (count($pathExploded)) {
                return $this->findRecursive(implode("/", $pathExploded), $directoryElement["content"]);
            }
            return $directoryElement;
        }

        return [];
    }

    /**
     * Scans the given directory recursively, returning all elements (FOLDERS & FILES).
     * @param string $dir Directory to scan.
     * @param array[] Multi level recursive array of the directory's content.
     */
    private function scanGetDirectory($dir = "") {

        $dir = $dir ? "$dir/" : "";

        $directoryRecursive = [];

        // Gets the current directory's content (FOLDERS & FILES).
        $folderListOrFileList = array_diff(scandir($this->rootDirectory . $dir) ?: [], [".", ".."]);

        // For each FOLDERS & FILES.
        foreach ($folderListOrFileList as $folderOrFile) {

            // If FOLDER.
            if (is_dir($this->rootDirectory . $dir . $folderOrFile)) {

                $dirPath = $dir . $folderOrFile;

                $this->directoryFlat[$dirPath] = [
                    "name" => $folderOrFile,
                    "path" => $dirPath,
                    "type" => "folder",
                ];

                $directoryRecursive[$folderOrFile] = [
                    "name" => $folderOrFile,
                    "path" => $dirPath,
                    "type" => "folder",
                    "content" => $this->scanGetDirectory($dirPath),
                ];

            } // If FILE.
            else {

                $filePath = $dir . $folderOrFile;

                $this->directoryFlat[$filePath] = [
                    "name" => $folderOrFile,
                    "path" => $filePath,
                    "type" => "file",
                ];

                $directoryRecursive[$folderOrFile] = [
                    "name" => $folderOrFile,
                    "path" => $filePath,
                    "type" => "file",
                ];

            }

        }

        return $directoryRecursive;
    }
}