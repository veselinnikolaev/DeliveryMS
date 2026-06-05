<?php

declare(strict_types=1);

namespace Core;

use Core\Exceptions\DatabaseException;

class Model {

    private ?\mysqli $mysqli = null;
    private bool $debug = false;
    public string $host = '';
    public string $database = '';
    public string $user = '';
    public string $pass = '';
    public ?string $table = null;
    public ?string $primaryKey = null;

    public function connect(): void {
        // Initialize mysqli connection
        $this->host = DEFAULT_HOST;
        $this->user = DEFAULT_USER;
        $this->pass = DEFAULT_PASS;
        $this->database = DEFAULT_DB;

        // Create database connection
        if ($this->mysqli == null) {
            $this->mysqli = new \mysqli($this->host, $this->user, $this->pass, $this->database);

            // Check for connection error
            if ($this->mysqli->connect_error) {
                throw new DatabaseException("Connection failed: " . $this->mysqli->connect_error);
            }
        }
    }

    public function checkConnection(string $host, string $user, string $password, string $database): array {
        // Create connection to MySQL server (without database)
        try {
            $this->mysqli = new \mysqli($host, $user, $password);
        } catch (\Throwable) {
            return [
                'status' => false,
                'message' => "Connection to database failed."
            ];
        }

        // Check for connection error to MySQL server
        if ($this->mysqli->connect_error) {
            return [
                'status' => false,
                'message' => "Connection failed: " . $this->mysqli->connect_error
            ];
        }

        // Create database if it doesn't exist
        $query = "CREATE DATABASE IF NOT EXISTS `$database`";
        if (!$this->mysqli->query($query)) {
            $this->close();
            return [
                'status' => false,
                'message' => "Failed to create database: " . $this->mysqli->error
            ];
        }

        // After creating the database, connect to it
        $this->mysqli->select_db($database);

        // Check for connection error to the specific database
        if ($this->mysqli->error) {
            $this->close();
            return [
                'status' => false,
                'message' => "Connection to database failed: " . $this->mysqli->error
            ];
        }

        return [
            'status' => true,
            'message' => 'Connection successful!'
        ];
    }

    public function migrate(string $filePath = 'config/database.sql'): array {
        $this->connect();

        // Check if file exists
        if (!file_exists($filePath)) {
            return [
                'status' => false,
                'message' => "SQL file not found!"
            ];
        }

        // Read SQL file
        $sql = file_get_contents($filePath);

        try {
            // Execute the SQL script
            if ($this->mysqli->multi_query($sql)) {
                do {
                    // Clear results, even if there are none
                    if ($result = $this->mysqli->store_result()) {
                        $result->free();
                    }
                } while ($this->mysqli->next_result()); // Wait for all queries to finish

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
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => "Error executing script for migration: " . $e->getMessage()
            ];
        }
    }

    public function isDbMigrated(string $databaseName): bool {
        $this->connect();

        // Validate database name to prevent SQL injection
        if (!$this->isValidIdentifier($databaseName)) {
            return false;
        }

        // Check if database exists
        $dbCheckQuery = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";

        try {
            $stmt = $this->mysqli->prepare($dbCheckQuery);
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param('s', $databaseName);
            $stmt->execute();
            $dbExists = $stmt->get_result();
            $stmt->close();

            if (!$dbExists || $dbExists->num_rows === 0) {
                return false; // Database does not exist
            }

            // Check if database has tables
            $tableCheckQuery = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ?";
            $stmt = $this->mysqli->prepare($tableCheckQuery);
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param('s', $databaseName);
            $stmt->execute();
            $tables = $stmt->get_result();
            $stmt->close();

            if ($tables && $tables->num_rows > 0) {
                return true; // Has at least one table -> migrated
            }
        } catch (\Throwable) {
            return false;
        }

        return false; // No tables -> not migrated
    }

    public function getAll($options = null, $column = null, $limit = null): array {
        // Create base SELECT query
        $query = "SELECT * FROM " . $this->getTable();
        $params = [];

        // Check if options array is provided
        if ($options && is_array($options)) {
            $conditions = [];
            foreach ($options as $field => $value) {
                // Check if field contains an operator (>=, <=, >, <, !=, LIKE)
                if (preg_match('/^([a-zA-Z0-9_-]+)\s*(>=|<=|>|<|!=|LIKE)$/i', $field, $matches)) {
                    $sanitizedField = $matches[1];
                    $operator = strtoupper($matches[2]);
                    
                    if ($sanitizedField === false) {
                        continue; // Skip invalid field names
                    }
                    
                    // Build WHERE condition with operator
                    if ($operator === 'LIKE') {
                        $conditions[] = "`$sanitizedField` LIKE ?";
                    } else {
                        $conditions[] = "`$sanitizedField` $operator ?";
                    }
                    $params[] = $value;
                } else {
                    // Validate and sanitize field name for simple equality
                    $sanitizedField = $this->sanitizeFieldName($field);
                    if ($sanitizedField === false) {
                        continue; // Skip invalid field names
                    }
                    // Build WHERE conditions with placeholders
                    if (is_array($value)) {
                        // Handle IN clause
                        $placeholders = implode(',', array_fill(0, count($value), '?'));
                        $conditions[] = "`$sanitizedField` IN ($placeholders)";
                        $params = array_merge($params, $value);
                    } else {
                        $conditions[] = "`$sanitizedField` = ?";
                        $params[] = $value;
                    }
                }
            }
            // Add WHERE clause to query
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }
        } elseif ($options) {
            // If $options is not an array, append directly (WARNING: caller must ensure this is safe)
            $query .= " WHERE " . $options;
        }

        // Add ORDER BY clause if $column is provided
        if ($column) {
            $sanitizedColumn = $this->sanitizeFieldName($column);
            if ($sanitizedColumn !== false) {
                $query .= " ORDER BY `$sanitizedColumn`";
            }
        }

        // Add LIMIT clause if $limit is provided
        if ($limit) {
            $sanitizedLimit = intval($limit);
            $query .= " LIMIT " . $sanitizedLimit;
        }

        // Execute query with bound parameters
        $types = !empty($params) ? str_repeat('s', count($params)) : '';
        return $this->executeQuery($query, $params, $types);
    }

    public function get($id): array {
        // Returns a single record by primary key
        $primaryKeyName = $this->primaryKey ?: 'id';
        $query = "SELECT * FROM " . $this->getTable() . " WHERE `$primaryKeyName` = ?";
        $arr = $this->executeQuery($query, [$id], 'i'); // 'i' for integer

        return $arr[0];
    }

    public function getFirstBy($options = null): ?array {
        // Main SELECT query
        $query = "SELECT * FROM `" . $this->getTable() . "`";
        $params = [];

        if ($options && is_array($options)) {
            $conditions = [];
            foreach ($options as $field => $value) {
                // Enclose column names with backticks
                $conditions[] = "`$field` = ?";
                $params[] = $value;
            }
            $query .= " WHERE " . implode(" AND ", $conditions);
        } elseif ($options) {
            $query .= " WHERE " . $options; // No prepared statement here, be careful with injections!
        }

        $result = $this->executeQuery($query, $params, str_repeat("s", count($params)));
        if (!empty($result)) {
            return $result[0];
        }
        // Execute with protected parameters
    }

    public function countAll($options = null): int {
        $query = "SELECT COUNT(*) as total FROM " . $this->getTable();
        $params = [];

        // Build WHERE clause if options are provided
        if ($options && is_array($options)) {
            $conditions = [];
            foreach ($options as $field => $value) {
                $conditions[] = "`$field` = ?";
                $params[] = $value;
            }
            $query .= " WHERE " . implode(" AND ", $conditions);
            $types = str_repeat('s', count($params));
        } elseif ($options) {
            // If options is a raw WHERE string
            $query .= " WHERE " . $options;
            $types = '';
        } else {
            $types = '';
        }

        // Execute the query
        $result = $this->executeQuery($query, $params, $types);

        // Return the count from the result
        return isset($result[0]['total']) ? (int) $result[0]['total'] : 0;
    }

    public function getMultiple($ids): array {
        // Create base SELECT query
        $query = "SELECT * FROM " . $this->getTable();
        $primaryKeyName = $this->primaryKey ?: 'id';
        $params = [];

        // Check if array of IDs is provided
        if ($ids && is_array($ids)) {
            // Validate all IDs are integers
            $validIds = array_filter($ids, 'is_numeric');
            if (empty($validIds)) {
                return [];
            }
            // Add WHERE clause with placeholders
            $placeholders = implode(',', array_fill(0, count($validIds), '?'));
            $query .= " WHERE `$primaryKeyName` IN ($placeholders)";
            $params = $validIds;
        } elseif ($ids) {
            // If single ID, validate and add to query
            if (!is_numeric($ids)) {
                return [];
            }
            $query .= " WHERE `$primaryKeyName` = ?";
            $params = [$ids];
        }

        $types = !empty($params) ? str_repeat('i', count($params)) : '';
        $result = $this->executeQuery($query, $params, $types);
        return $result;
    }

    public function existsBy($options = null): bool {
        $query = "SELECT COUNT(*) as count FROM " . $this->getTable();
        $params = [];

        if ($options && is_array($options)) {
            $conditions = [];
            foreach ($options as $field => $value) {
                // Validate and sanitize field name
                $sanitizedField = $this->sanitizeFieldName($field);
                if ($sanitizedField === false) {
                    continue; // Skip invalid field names
                }
                // Build WHERE conditions with placeholders
                $conditions[] = "`$sanitizedField` = ?";
                $params[] = $value;
            }
            // Add WHERE clause to query
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }
        } elseif ($options) {
            // If $options is not an array, append directly (WARNING: caller must ensure this is safe)
            $query .= " WHERE " . $options;
        }

        $types = !empty($params) ? str_repeat('s', count($params)) : '';
        $result = $this->executeQuery($query, $params, $types);
        return isset($result[0]['count']) && $result[0]['count'] > 0;
    }

    public function save($data): int|false {
        $this->connect();
        // Insert new record

        $save = array();

        foreach ($this->schema as $field) {
            if (isset($data[$field['name']])) {
                if (!is_array($data[$field['name']])) {
                    // Check if the value is an empty string and convert to NULL
                    $value = $data[$field['name']];
                    $save["`" . $field['name'] . "`"] = $value === '' ? null : $value;
                } else {
                    if (isset($data[$field['name']][0])) {
                        // Check if the array value is an empty string and convert to NULL
                        $value = $data[$field['name']][0];
                        $save["`" . $field['name'] . "`"] = $value === '' ? null : $value;
                    }
                }
            }
        }

        $fields = array_keys($save);
        $values = array_values($save);

        $placeholders = implode(',', array_fill(0, count($fields), '?'));
        $query = "INSERT INTO " . $this->getTable() . " (" . implode(',', $fields) . ") VALUES ($placeholders)";

        if ($this->executeQuery($query, $values, str_repeat('s', count($values)))) { // 's' for string
            return $this->mysqli->insert_id;
        } else {
            return false;
        }
    }

    public function update($data): bool {
        // Update existing record
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
        $values[] = $data[$primaryKeyName]; // Add the primary key value at the end

        return $this->executeQuery($query, $values, str_repeat('s', count($values) - 1) . 'i'); // Add 'i' for integer
    }

    public function updateBy($data, $options = null): bool {
        // Prepare an array of fields/values to update based on the defined schema
        $save = [];
        foreach ($this->schema as $field) {
            $name = $field['name'];
            if (isset($data[$name])) {
                // If the field value is not an array, use it directly.
                if (!is_array($data[$name])) {
                    $save["`$name`"] = $data[$name];
                } else {
                    // If the field value is an array, take the first element
                    if (isset($data[$name][0])) {
                        $save["`$name`"] = $data[$name][0];
                    }
                }
            }
        }

        // If there are no fields to update, return false
        if (empty($save)) {
            return false;
        }

        // Extract the field names and values for binding
        $fields = array_keys($save);
        $values = array_values($save);

        // Build the SET part of the query with placeholders
        $set = [];
        foreach ($fields as $field) {
            $set[] = "$field = ?";
        }

        // Start building the query
        $query = "UPDATE " . $this->getTable() . " SET " . implode(',', $set);

        // Handle WHERE clause
        if ($options && is_array($options)) {
            $conditions = [];
            foreach ($options as $field => $value) {
                // Handling IN clause properly
                if (is_array($value)) {
                    $conditions[] = "`$field` IN (" . implode(',', array_fill(0, count($value), '?')) . ")";
                    $values = array_merge($values, $value); // Append all IN values
                } else {
                    $conditions[] = "`$field` = ?";
                    $values[] = $value;
                }
            }
            $query .= " WHERE " . implode(" AND ", $conditions);
        } elseif ($options) {
            // If $options is provided as a string, append it directly (i.e., 'key = "email_sending"')
            $query .= " WHERE " . $options;
        } else {
            // If no conditions are provided, prevent accidental full table updates
            return false;
        }

        // Build the types string for binding parameters (assuming all are strings)
        $types = str_repeat('s', count($values));

        // Execute the query using your custom executeQuery method
        return $this->executeQuery($query, $values, $types);
    }

    public function delete($id): bool {
        // Delete record
        $primaryKeyName = $this->primaryKey ?: 'id';
        $query = "DELETE FROM " . $this->getTable() . " WHERE `$primaryKeyName` = ?";
        return $this->executeQuery($query, [$id], 'i'); // 'i' for integer
    }

    public function deleteBy($options = null): bool {
        // Delete record
        $query = "DELETE FROM " . $this->getTable();
        $params = [];

        // Check if an array of conditions is provided
        if ($options && is_array($options)) {
            $conditions = [];
            foreach ($options as $field => $value) {
                // If $value is an array, build the IN clause
                if (is_array($value)) {
                    $conditions[] = "`$field` IN (" . implode(',', array_fill(0, count($value), '?')) . ")";
                    $params = array_merge($params, $value); // Add values for IN
                } else {
                    // Build WHERE conditions
                    $conditions[] = "`$field` = ?";
                    $params[] = $value; // Add value for normal condition
                }
            }
            // Add WHERE clause to query
            $query .= " WHERE " . implode(" AND ", $conditions);
        } elseif ($options) {
            // If $options is not an array, add directly
            $query .= " WHERE " . $options;
        }

        $types = str_repeat('s', count($params));
        // Execute query with parameters
        return $this->executeQuery($query, $params, $types);
    }

    public function updateBatch($data = null, $keyColumn = null): bool {
        $this->connect();
        // Check if data is provided
        if (empty($data) || empty($keyColumn)) {
            return false;
        }

        // Validate key column name
        $sanitizedKeyColumn = $this->sanitizeFieldName($keyColumn);
        if ($sanitizedKeyColumn === false) {
            return false;
        }

        // Prepare query
        $query = "UPDATE " . $this->getTable() . " SET value = CASE";
        $conditions = [];
        $params = [];

        foreach ($data as $row) {
            // Add CASE conditions
            $query .= " WHEN `$sanitizedKeyColumn` = ? THEN ?";
            $conditions[] = $row['key'];
            $params[] = $row['key'];
            $params[] = $row['value'];
        }

        // Complete query with placeholders
        $placeholders = implode(',', array_fill(0, count($conditions), '?'));
        $query .= " END WHERE `$sanitizedKeyColumn` IN ($placeholders)";
        $params = array_merge($params, $conditions);

        // Prepare statement for execution
        $stmt = $this->mysqli->prepare($query);
        if ($stmt) {
            // Bind parameters
            $types = str_repeat('s', count($params)); // Assuming all params are strings
            $stmt->bind_param($types, ...$params);

            // Execute query
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }

        return false;
    }

    public function executeQuery(string $query, array $params = [], string $types = ''): array|bool {
        $this->connect();
        // Prepare the query
        $stmt = $this->mysqli->prepare($query);

        // Check if query preparation was successful
        if (!$stmt) {
            if ($this->debug) {
                echo "Error preparing query: " . $this->mysqli->error;
            }
            return false;
        }

        // Bind parameters to the query
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        // Execute the query
        $stmt->execute();

        // Return results
        $result = $stmt->get_result();
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC); // Return results as associative array
        }
        return true; // For queries without results, like UPDATE or DELETE
    }

    public function getTable(): ?string {
        // Return table name
        return $this->table;
    }

    public function close(): void {
        // Close database connection
        if ($this->mysqli) {
            $this->mysqli->close();
        }
    }

    /**
     * Sanitize field name to prevent SQL injection
     * Only allows alphanumeric characters, underscores, and dashes
     * 
     * @param string $field The field name to sanitize
     * @return string|false Sanitized field name or false if invalid
     */
    private function sanitizeFieldName($field): string|false {
        // Check if field name contains only valid characters
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $field)) {
            return false;
        }
        return $field;
    }

    /**
     * Validate SQL identifier (database name, table name, etc.)
     * Only allows alphanumeric characters and underscores
     * 
     * @param string $identifier The identifier to validate
     * @return bool True if valid, false otherwise
     */
    private function isValidIdentifier(string $identifier): bool {
        // Check if identifier contains only valid characters
        return preg_match('/^[a-zA-Z0-9_]+$/', $identifier) === 1;
    }
}
