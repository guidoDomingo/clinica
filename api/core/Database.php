<?php
namespace Api\Core;

/**
 * Database Class
 * 
 * Handles database connections and provides a query builder interface
 */
class Database
{
    /**
     * @var \PDO The PDO connection instance
     */
    private static $connection = null;
    
    /**
     * @var array Database configuration
     */
    private static $config = [
        'host' => '',
        'port' => '',
        'database' => '',
        'username' => '',
        'password' => '',
        'driver' => 'pgsql'
    ];
    
    /**
     * Initialize the database connection with configuration
     * 
     * @param array $config Database configuration
     * @return void
     */
    public static function init($config)
    {
        self::$config = array_merge(self::$config, $config);
    }
    
    /**
     * Get the PDO connection instance
     * 
     * @return \PDO
     */
    public static function getConnection()
    {
        if (self::$connection === null) {
            self::connect();
        }
        
        return self::$connection;
    }
    
    /**
     * Connect to the database
     * 
     * @return void
     * @throws \Exception If connection fails
     */
    private static function connect()
    {
        $driver = self::$config['driver'];
        $host = self::$config['host'];
        $port = self::$config['port'];
        $database = self::$config['database'];
        $username = self::$config['username'];
        $password = self::$config['password'];
        
        try {
            $dsn = "{$driver}:host={$host};port={$port};dbname={$database}";
            self::$connection = new \PDO($dsn, $username, $password);
            self::$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Execute a SQL query
     * 
     * @param string $sql The SQL query
     * @param array $params The query parameters
     * @return \PDOStatement
     */
    public static function query($sql, $params = [])
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Fetch all records from a query
     * 
     * @param string $sql The SQL query
     * @param array $params The query parameters
     * @return array
     */
    public static function fetchAll($sql, $params = [])
    {
        return self::query($sql, $params)->fetchAll();
    }
    
    /**
     * Fetch a single record from a query
     * 
     * @param string $sql The SQL query
     * @param array $params The query parameters
     * @return array|null
     */
    public static function fetch($sql, $params = [])
    {
        $result = self::query($sql, $params)->fetch();
        return $result !== false ? $result : null;
    }
    
    /**
     * Insert a record into a table
     * 
     * @param string $table The table name
     * @param array $data The data to insert
     * @return int The last insert ID
     */
    public static function insert($table, $data)
    {
        $columns = array_keys($data);
        $placeholders = array_map(function ($column) {
            return ":$column";
        }, $columns);
        
        $sql = "INSERT INTO $table (" . implode(', ', $columns) . ") "
             . "VALUES (" . implode(', ', $placeholders) . ")";
        
        self::query($sql, $data);
        return self::getConnection()->lastInsertId();
    }
    
    /**
     * Update records in a table
     * 
     * @param string $table The table name
     * @param array $data The data to update
     * @param string $where The WHERE clause
     * @param array $whereParams The WHERE parameters
     * @return int The number of affected rows
     */
    public static function update($table, $data, $where, $whereParams = [])
    {
        $setClauses = array_map(function ($column) {
            return "$column = :$column";
        }, array_keys($data));
        
        $sql = "UPDATE $table SET " . implode(', ', $setClauses) . " WHERE $where";
        
        $params = array_merge($data, $whereParams);
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Delete records from a table
     * 
     * @param string $table The table name
     * @param string $where The WHERE clause
     * @param array $params The WHERE parameters
     * @return int The number of affected rows
     */
    public static function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Begin a database transaction
     * 
     * @return bool
     */
    public static function beginTransaction()
    {
        return self::getConnection()->beginTransaction();
    }

    /**
     * Commit a database transaction
     * 
     * @return bool
     */
    public static function commit()
    {
        return self::getConnection()->commit();
    }

    /**
     * Rollback a database transaction
     * 
     * @return bool
     */
    public static function rollBack()
    {
        return self::getConnection()->rollBack();
    }
}