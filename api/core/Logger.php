<?php
namespace Api\Core;

/**
 * Logger Class
 * 
 * Handles application logging functionality
 */
class Logger
{
    /**
     * @var string The default log file path
     */
    private static $logFile = 'c:/laragon/www/clinica/logs/application.log';
    
    /**
     * Log a message with context data
     * 
     * @param mixed $data The data to log
     * @param string $type The type of log (info, error, debug)
     * @param string $context Additional context information
     * @return void
     */
    public static function log($data, $type = 'info', $context = '')
    {
        $timestamp = date('Y-m-d H:i:s');
        $logData = json_encode($data, JSON_PRETTY_PRINT);
        
        $message = "[{$timestamp}] [{$type}] {$context}\n";
        $message .= "Data: {$logData}\n";
        $message .= "----------------------------------------\n";
        
        error_log($message, 3, self::$logFile);
    }
    
    /**
     * Log info level message
     * 
     * @param mixed $data The data to log
     * @param string $context Additional context information
     * @return void
     */
    public static function info($data, $context = '')
    {
        self::log($data, 'info', $context);
    }
    
    /**
     * Log error level message
     * 
     * @param mixed $data The data to log
     * @param string $context Additional context information
     * @return void
     */
    public static function error($data, $context = '')
    {
        self::log($data, 'error', $context);
    }
    
    /**
     * Log debug level message
     * 
     * @param mixed $data The data to log
     * @param string $context Additional context information
     * @return void
     */
    public static function debug($data, $context = '')
    {
        self::log($data, 'debug', $context);
    }
}