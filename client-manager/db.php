<?php
// client-manager/db.php
require 'app.php';

// Database Connection
try {
    $dsn = "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];
    
    $pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS'], $options);
} catch (PDOException $e) {
    error_log("DB Connection Error: " . $e->getMessage());
    header('HTTP/1.1 503 Service Unavailable');
    exit('Database connection failed. Please try again later.');
}

/**
 * Execute a SQL query with parameters
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters for prepared statement
 * @return PDOStatement Executed statement
 */
function query($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query Error: {$e->getMessage()} - SQL: $sql");
        throw $e;
    }
}

/**
 * Fetch a single row
 * @param string $sql SQL query
 * @param array $params Query parameters
 * @return array|null Fetched row or null
 */
function fetch($sql, $params = []) {
    return query($sql, $params)->fetch();
}

/**
 * Fetch all rows
 * @param string $sql SQL query
 * @param array $params Query parameters
 * @return array Fetched rows
 */
function fetch_all($sql, $params = []) {
    return query($sql, $params)->fetchAll();
}

/**
 * Insert record and return last insert ID
 * @param string $table Table name
 * @param array $data Associative array of column => value
 * @return int Last insert ID
 */
function insert($table, $data) {
    global $pdo;
    
    $columns = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    query($sql, array_values($data));
    
    return $pdo->lastInsertId();
}

/**
 * Update records
 * @param string $table Table name
 * @param array $data Associative array of column => value
 * @param string $condition WHERE condition
 * @param array $params Condition parameters
 * @return int Number of affected rows
 */
function update($table, $data, $condition, $params = []) {
    $set = [];
    $values = [];
    
    foreach ($data as $column => $value) {
        $set[] = "$column = ?";
        $values[] = $value;
    }
    
    $values = array_merge($values, $params);
    $set = implode(', ', $set);
    
    $sql = "UPDATE $table SET $set WHERE $condition";
    $stmt = query($sql, $values);
    
    return $stmt->rowCount();
}

/**
 * Delete records
 * @param string $table Table name
 * @param string $condition WHERE condition
 * @param array $params Condition parameters
 * @return int Number of affected rows
 */
function delete($table, $condition, $params = []) {
    $sql = "DELETE FROM $table WHERE $condition";
    $stmt = query($sql, $params);
    return $stmt->rowCount();
}