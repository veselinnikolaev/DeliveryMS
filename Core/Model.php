<?php

namespace Core;

class Model {

    private $mysqli;
    private $debug = false;
    public $host = '';
    public $database = '';
    public $user = '';
    public $pass = '';
    public $table = null;
    public $primaryKey = null;

    public function __construct() {
        // Инициализиране на mysqli връзката
        $this->host = DEFAULT_HOST;
        $this->user = DEFAULT_USER;
        $this->pass = DEFAULT_PASS;
        $this->database = DEFAULT_DB;

        $this->connect();
    }

    public function connect() {
        // Създаване на връзка с базата данни
        $this->mysqli = new \mysqli($this->host, $this->user, $this->pass, $this->database);

        // Проверка за грешка при връзка
        if ($this->mysqli->connect_error) {
            die("Connection failed: " . $this->mysqli->connect_error);
        }
    }

    public function checkConnection($host, $user, $password, $database) {
        // Създаване на връзка към MySQL сървъра (без база данни)
        $this->mysqli = new \mysqli($host, $user, $password);

        // Проверка за грешка при връзка към MySQL сървър
        if ($this->mysqli->connect_error) {
            return [
                'status' => false,
                'message' => "Connection failed: " . $this->mysqli->connect_error
            ];
        }

        // Създаване на база данни, ако не съществува
        $query = "CREATE DATABASE IF NOT EXISTS `$database`";
        if (!$this->mysqli->query($query)) {
            return [
                'status' => false,
                'message' => "Failed to create database: " . $this->mysqli->error
            ];
        }

        // След създаване на базата данни, свързваме се към нея
        $this->mysqli->select_db($database);

        // Проверка за грешка при връзка към конкретната база данни
        if ($this->mysqli->connect_error) {
            return [
                'status' => false,
                'message' => "Connection to database failed: " . $this->mysqli->connect_error
            ];
        }

        return [
            'status' => true,
            'message' => 'Connection successful!'
        ];
    }

    public function migrate($databaseName, $filePath = 'config/database.sql') {
        // Check if file exists
        if (!file_exists($filePath)) {
            return [
                'status' => false,
                'message' => "SQL file not found!"
            ];
        }

        // Read SQL file
        $sql = file_get_contents($filePath);

        str_replace('{database_name}', $databaseName, $sql);

        // Execute the SQL script
        if ($this->mysqli->multi_query($sql)) {
            // Wait for all queries to finish
            do {
                $result = $this->mysqli->store_result();
                if ($result) {
                    $result->free();
                }
            } while ($this->mysqli->next_result());

            return [
                'status' => true,
                'message' => "Database setup successfully!"
            ];
        } else {
            return [
                'status' => false,
                'message' => "Error executing script: " . $this->mysqli->error
            ];
        }
    }

    public function isDbMigrated($databaseName) {
        $dbCheckQuery = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$databaseName'";
        $dbExists = $this->mysqli->query($dbCheckQuery);

        if ($dbExists->num_rows > 0) {
            return false;
        }

        return true;
    }

    public function getAll($options = null, $column = null, $limit = null) {
        // Създаване на основна SELECT заявка
        $query = "SELECT * FROM " . $this->getTable();

        // Проверка дали има подаден масив с условия
        if ($options && is_array($options)) {
            $conditions = [];
            foreach ($options as $field => $value) {
                // Изграждане на условията за WHERE
                $conditions[] = "$field = '$value'";
            }
            // Добавяне на WHERE частта към заявката
            $query .= " WHERE " . implode(" AND ", $conditions);
        } elseif ($options) {
            // Ако $options не е масив, добавяме директно
            $query .= " WHERE " . $options;
        }

        // Добавяне на ORDER BY частта, ако е подаден $column
        if ($column) {
            $query .= " ORDER BY " . $column;
        }

        // Добавяне на LIMIT частта, ако е подаден $limit
        if ($limit) {
            $query .= " LIMIT " . $limit;
        }

        // Изпълняване на заявката
        return $this->executeQuery($query);
    }

    public function get($id) {
        // Връща един запис по primary key
        $primaryKeyName = $this->primaryKey ?: 'id';
        $query = "SELECT * FROM " . $this->getTable() . " WHERE `$primaryKeyName` = ?";
        $arr = $this->executeQuery($query, [$id], 'i'); // 'i' за integer

        return $arr[0];
    }

    public function getFirstBy($options = null) {
        // Основна SELECT заявка
        $query = "SELECT * FROM `" . $this->getTable() . "`";
        $params = [];

        if ($options && is_array($options)) {
            $conditions = [];
            foreach ($options as $field => $value) {
                // Ограждане на имената на колоните с backticks
                $conditions[] = "`$field` = ?";
                $params[] = $value;
            }
            $query .= " WHERE " . implode(" AND ", $conditions);
        } elseif ($options) {
            $query .= " WHERE " . $options; // Тук няма prepared statement, внимавай с инжекциите!
        }

        return $this->executeQuery($query, $params, str_repeat("s", count($params)))[0]; // Изпълняване със защитени параметри
    }

    public function getMultiple($ids) {
        // Създаване на основна SELECT заявка
        $query = "SELECT * FROM " . $this->getTable();
        $primaryKeyName = $this->primaryKey ?: 'id';
        // Проверка дали има подаден масив с условия
        if ($ids && is_array($ids)) {
            // Добавяне на WHERE частта към заявката
            $query .= " WHERE `$primaryKeyName` IN (" . implode(", ", $ids) . ")";
        } elseif ($ids) {
            // Ако $options не е масив, добавяме директно
            $query .= " WHERE `$primaryKeyName` IN (" . $ids . ")";
        }
        return $this->executeQuery($query)[0];
    }

    public function existsBy($options = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->getTable();

        if ($options && is_array($options)) {
            $conditions = [];
            foreach ($options as $field => $value) {
                // Изграждане на условията за WHERE
                $conditions[] = "$field = '$value'";
            }
            // Добавяне на WHERE частта към заявката
            $query .= " WHERE " . implode(" AND ", $conditions);
        } elseif ($options) {
            // Ако $options не е масив, добавяме директно
            $query .= " WHERE " . $options;
        }

        $result = $this->executeQuery($query);
        return isset($result[0]['count']) && $result[0]['count'] > 0;
    }

    public function save($data) {
        // Вставка на нов запис

        $save = array();

        foreach ($this->schema as $field) {
            if (isset($data[$field['name']])) {
                if (!is_array($data[$field['name']])) {
                    $save["`" . $field['name'] . "`"] = $data[$field['name']];
                } else {
                    if (isset($data[$field['name']][0])) {
                        $save["`" . $field['name'] . "`"] = $data[$field['name']][0];
                    }
                }
            }
        }

        $fields = array_keys($save);
        $values = array_values($save);

        $placeholders = implode(',', array_fill(0, count($fields), '?'));
        $query = "INSERT INTO " . $this->getTable() . " (" . implode(',', $fields) . ") VALUES ($placeholders)";

        if ($this->executeQuery($query, $values, str_repeat('s', count($values)))) { // 's' за string
            return $this->mysqli->insert_id;
        } else {
            return false;
        }
    }

    public function update($data) {
        // Обновяване на съществуващ запис
        $save = array();

        foreach ($this->schema as $field) {

            if (isset($data[$field['name']])) {

                if (!is_array($data[$field['name']])) {
                    $save["`" . $field['name'] . "`"] = $data[$field['name']];
                } else {
                    if (isset($data[$field['name']][0])) {
                        $save["`" . $field['name'] . "`"] = $data[$field['name']][0];
                    }
                }
            }
        }

        $fields = array_keys($save);
        $values = array_values($save);

        $primaryKeyName = $this->primaryKey ?: 'id';

        $set = [];
        foreach ($fields as $field) {
            $set[] = "$field = ?";
        }

        $query = "UPDATE " . $this->getTable() . " SET " . implode(',', $set) . " WHERE `$primaryKeyName` = ?";
        $values[] = $data[$primaryKeyName]; // Добавяме стойността за primary key накрая

        return $this->executeQuery($query, $values, str_repeat('s', count($values) - 1) . 'i'); // Добавяме 'i' за integer
    }

    public function delete($id) {
        // Изтриване на запис
        $primaryKeyName = $this->primaryKey ?: 'id';
        $query = "DELETE FROM " . $this->getTable() . " WHERE `$primaryKeyName` = ?";
        return $this->executeQuery($query, [$id], 'i'); // 'i' за integer
    }

    public function updateBatch($data = null, $keyColumn = null) {
        // Проверка дали има подадени данни
        if (empty($data) || empty($keyColumn)) {
            return false;
        }

        // Подготовка на заявката
        $query = "UPDATE settings SET value = CASE";
        $conditions = [];
        $params = [];

        foreach ($data as $row) {
            // Добавяне на условията за CASE
            $query .= " WHEN `{$keyColumn}` = ? THEN ?";
            $conditions[] = $row['key'];
            $params[] = $row['key'];
            $params[] = $row['value'];
        }

        // Завършване на заявката
        $query .= " END WHERE `{$keyColumn}` IN ('" . implode("','", $conditions) . "')";

        // Подготовка на заявката за изпълнение
        if ($stmt = $this->mysqli->prepare($query)) {
            // Свързване на параметрите
            $types = str_repeat('s', count($params)); // Assuming all params are strings
            $stmt->bind_param($types, ...$params);

            // Изпълнение на заявката
            return $stmt->execute();
        }

        return false;
    }

    public function executeQuery($query, $params = [], $types = '') {
        // Подготовка на заявката
        $stmt = $this->mysqli->prepare($query);

        // Проверка дали заявката е успешна
        if (!$stmt) {
            if ($this->debug) {
                echo "Error preparing query: " . $this->mysqli->error;
            }
            return false;
        }

        // Привързване на параметрите към заявката
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        // Изпълнение на заявката
        $stmt->execute();

        // Връщане на резултати
        $result = $stmt->get_result();
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC); // Връщаме резултатите като асоциативен масив
        }
        return true; // За не-заявки с резултати, като UPDATE или DELETE
    }

    public function getTable() {
        // Връща името на таблицата
        return $this->table;
    }

    public function close() {
        // Затваряне на връзката с базата данни
        $this->mysqli->close();
    }
}
