<?php

/**
 * Mini JSON database management system.
 * Table: INSERT, DELETE, or CHECK IF EXISTS.
 * Data: INSERT or GET data.
 * 
 * @example
 * $db = new Database();
 * if ($db->tableExists("myTable")) {
 *     $db->deleteTable("myTable");
 * }
 * $db->createTable("myTable");
 * $dataToInsert = [
 *      [
 *          "id" => 1,
 *          "firstName" => "John",
 *          "lastName" => "Doe",
 *      ],
 *      [
 *          "id" => 2,
 *          "firstName" => "Jane",
 *          "lastName" => "Doe",
 *      ]
 * ];
 * $db->insert("myTable")->values($dataToInsert)->execute();
 * $data = $db->select("*")->from("myTable")->where(["firstName" => "John", "lastName" => "Doe"])->execute();
 */
class Database {
    /**
     * The SELECT statement. Accepts either a string representing a single column name or '*' for all columns, or an array of column names.
     * @var string|array
     * @example
     */
    public $select;
    /**
     * The FROM statement, indicating the table name.
     * @var string
     * @example 
     */
    public $from;
    /**
     * The WHERE statement in the form of an associative array where keys represent column names and values represent conditions.
     * @var string
     * @example 
     */
    public $where;
    // /**
    //  * The ORDER BY statement, indicating the column name to sort by.
    //  * @var string
    //  * @example 
    //  */
    // public $orderBy;
    /**
     * The INSERT statement, indicating the table name where data will be inserted.
     * @var string
     * @example 
     */
    public $insert;
    /**
     * An associative array where keys represent column names and values represent the values to be inserted.
     * @var string
     * @example 
     */
    public $values;
    /**
     * The current directory where the data JSON file will be stored.
     * @return string
     */
    private $dataDir;

    /**
     * Constructs a new {@link Database}'s instance.
     * @param string $dataDir The directory where data is stored. Default is "data".
     * @return Database
     */
    public function __construct($dataDir = "data") {
        if (is_dir($dataDir)) {
            $this->dataDir = $dataDir;
        } else {
            $this->exception("given database `$dataDir` directory is not valid");
        }
    }

    // ##############
    // PUBLIC METHODS
    // ##############

    /**
     * Sets the SELECT statement.
     * @param string|array $select The SELECT statement. Accepts either a string representing a single column name or '*' for all columns, or an array of column names.
     * @return $this
     */
    public function select($select) {
        $this->select = $select;
        return $this;
    }

    /**
     * Sets the FROM statement.
     * @param string $from The FROM statement, indicating the table name.
     * @return $this
     */
    public function from($from) {
        $this->from = $from;
        return $this;
    }

    /**
     * Sets the WHERE statement.
     * @param array $where The WHERE statement in the form of an associative array where keys represent column names and values represent conditions.
     * @return $this
     */
    public function where($where) {
        $this->where = $where;
        return $this;
    }

    // /**
    //  * Sets the ORDER BY statement.
    //  * @param string $orderBy The ORDER BY statement, indicating the column name to sort by.
    //  * @return $this
    //  */
    // public function orderBy($orderBy) {
    //     $this->orderBy = $orderBy;
    //     return $this;
    // }

    /**
     * Sets the INSERT statement.
     * @param string $insert The INSERT statement, indicating the table name where data will be inserted.
     * @return $this
     */
    public function insert($insert) {
        $this->insert = $insert;
        return $this;
    }

    public function values($values) {
        $this->values = $values;
        return $this;
    }

    /**
     * Executes the current SELECT / INSERT query.
     * @return void|array Returns an array if SELECT, void if INSERT.
     */
    public function execute() {

        // #########
        // IF SELECT
        // #########

        if ($this->select !== null && $this->from !== null) {

            // Gets the table's content.
            $tableData = $this->getTableContent($this->from);

            if (count($tableData) === 0) {
                return [];
            }

            // Gets the table's columns if it already has some values.
            $tableColumns = $this->getColumns($tableData);

            // #########################
            // CHECKS `SELECT` STATEMENT
            // #########################

            $selectColumns = [];

            if (is_array($this->select)) {
                if (count($this->select) === 0) {
                    $this->exception("SELECT: must be a string (* or column name) or an array of columns names");
                }
                foreach ($this->select as $columnName) {
                    if (!is_string($columnName) || !in_array($columnName, $tableColumns)) {
                        $this->exception("SELECT: '$columnName' is not a valid column name. It must be a string (* or column name) or an array of columns names");
                    } 
                }
                $selectColumns = $this->select;
            } else if (is_string($this->select)) {
                if ($this->select !== "*" && !in_array($this->select, $tableColumns)) {
                    $this->exception("SELECT: '$this->select' is not a valid column name. It must be a string (* or column name) or an array of columns names");
                }
                if ($this->select === "*") {
                    $selectColumns = $tableColumns;
                } else {
                    $selectColumns = [$this->select];
                }
            } else {
                $this->exception("SELECT: must be a string (* or column name) or an array of columns names");
            }

            // ########################
            // CHECKS `WHERE` STATEMENT
            // ########################

            if ($this->where) {
                // foreach ($array as $value) {
                //     if (!$fn($value)) {
                //         return false;
                //     }
                // }
                // return true;
            }

            // ##############
            // FILTERS OUTPUT
            // ##############

            // For each entries.
            foreach ($tableData as $key => &$entry) {

                // For each columns + values.
                foreach ($entry as $colName => $colValue) {
                    if (!in_array($colName, $selectColumns)) {
                        unset($entry[$colName]);
                    }
                    foreach ($this->where as $whereColName => $whereColValue) {
                        if ($whereColName === $colName && $whereColValue !== $colValue) {
                            unset($tableData[$key]);
                        }
                    }
                }
            }

            // if ($this->orderBy) {

            // }

            $this->resetQuery();

            return $tableData;

        }

        // #########
        // IF UPDATE
        // #########

        else if ($this->insert !== null && $this->values !== null) {

            $tableName = $this->insert;

            // Creates the table if it does not exists.
            if (!$this->tableExists($tableName)) {
                $this->createTable($tableName);
            }

            $tableData = $this->getTableContent($tableName);
            $tableColumns = $this->getColumns($tableData);

            $tableNewData = [];

            // If it is an array containing arrays containing values.
            if (is_array($this->values) && is_array(reset($this->values))) {
                foreach ($this->values as $value) {
                    $this->checkInsertValues($value, $tableColumns);
                }
                $tableNewData = $this->values;
            } // If its an array containing values.
            else {
                $this->checkInsertValues($this->values, $tableColumns);
                $tableNewData = [$this->values];
            }

            $this->resetQuery();

            $this->writeFile($tableName, array_merge($tableData, $tableNewData));
        }
    }

    /**
     * Checks if the given values match the column definition.
     * If if doesn't match, throws an Exception.
     * @param array $values Array of key => values to check.
     * @param array $columns Array of column names of the current table.
     */
    public function checkInsertValues($values, $columns) {

        if (!is_array($values)) {
            $this->exception("VALUES: must be an array of 'key' => 'values', or an array of arrays of 'key' => 'values'");
        } else if (count($this->values) === 0) {
            $this->exception("VALUES: must be an array of 'key' => 'values', or an array of arrays of 'key' => 'values'");
        }

        if (count($columns) === 0) return;

        foreach ($values as $key => $value) {
            if (!in_array($key, $columns)) {
                $this->exception("VALUES: column '$key' is not a valid column in the current table. Available columns: " . implode(", ", $columns));
            }
        }
    }

    /**
     * Checks if a JSON table exists.
     * @param string Table's name.
     * @return boolean
     */
    public function tableExists($name) {
        $filePath = $this->dataDir . "/". $name . ".json";
        return file_exists($filePath);
    }

    /**
     * Creates a JSON table if it doesn't exist.
     * @param string Table's name.
     */
    public function createTable($name) {
        $filePath = $this->dataDir . "/". $name . ".json";
        if (!file_exists($filePath)) {
            touch($filePath);
        }
    }

    /**
     * Deletes a JSON table if it exists.
     * @param string Table's name.
     */
    public function deleteTable($name) {
        $filePath = $this->dataDir . "/". $name . ".json";
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // ###############
    // PRIVATE METHODS
    // ###############

    /**
     * Gets a table's content.
     * - If it exists, gets it content (must be an array or null)
     *   - If its content is empty, returns [].
     *   - If its content is an array, returns its content.
     *   - If its content is anything other than the above, throws an exception.
     * - If it doesn't exist, throw an exception.
     * @param string $name Table's name.
     * @return array
     */
    private function getTableContent($name) {

        $filePath = $this->dataDir . "/" . $name . ".json";

        if (!$this->tableExists($name)) {
            $this->exception("given table name does not exist: `$name`. Cannot find JSON file: `" . $filePath . "`");
        }

        $contentRaw = file_get_contents($filePath);
        $arrayParsed = json_decode($contentRaw, true);

        if (is_array($arrayParsed)) {
            return $arrayParsed;
        } else if ($arrayParsed === null) {
            return [];
        } else {
            $this->exception("table `$name` does not seem to contain an array or an empty value. Cannot parse its content");
        }
    }

    /**
     * Gets the column names of the values to insert.
     * @param array $content Values to insert.
     * @return array Column names.
     */
    private function getColumns($content) {
        if (count($content)) return array_keys($content[0]);
        else return [];
    }

    /**
     * Writes into a JSON file.
     * @param string $name Table's name.
     * @param array $data Values to insert.
     */
    private function writeFile($name, $data) {
        $filePath = $this->dataDir . "/". $name . ".json";
        if (!file_exists($filePath)) {
            $this->exception("writeFile: cannot write into file '$filePath' since it does not exists");
        }
        $newArray = json_encode($data);
        file_put_contents($filePath, $newArray);
    }

    /**
     * Resets the current's SQL statements.
     */
    private function resetQuery() {
        $this->select = null;
        $this->from = null;
        $this->where = null;
        // $this->orderBy = null;
        $this->insert = null;
        $this->values = null;
    }

    /**
     * Throws an exception.
     * @param string $msg Exception's message.
     */
    private function exception($msg) {
        $this->resetQuery();
        $completeExceptionMessage = "Database: ";
        throw new Exception($completeExceptionMessage . $msg);
    }
}
