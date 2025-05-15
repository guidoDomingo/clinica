<?php
namespace Api\Core;

/**
 * Response Class
 * 
 * Handles API responses in a standardized format
 */
class Response
{
    /**
     * Send a success response
     * 
     * @param mixed $data The response data
     * @param int $statusCode The HTTP status code
     * @return void
     */
    public static function success($data = null, $statusCode = 200)
    {
        self::send([
            'status' => 'success',
            'data' => $data
        ], $statusCode);
    }

    /**
     * Send an error response
     * 
     * @param mixed $error The error details
     * @param int $statusCode The HTTP status code
     * @return void
     */
    public static function error($error = null, $statusCode = 400)
    {
        self::send([
            'status' => 'error',
            'error' => $error
        ], $statusCode);
    }

    /**
     * Send a JSON response
     * 
     * @param array $data The response data
     * @param int $statusCode The HTTP status code
     * @return array The response data (for testing)
     */
    public static function json($data, $statusCode = 200)
    {
        self::send($data, $statusCode);
        return $data;
    }    /**
     * Send a response
     * 
     * @param mixed $data The response data
     * @param int $statusCode The HTTP status code
     * @return void
     */
    private static function send($data, $statusCode = 200)
    {
        // Check if this is a test environment
        $isTestEnv = defined('TESTING_MODE') && TESTING_MODE === true;
        
        // If in test mode or headers already sent, just return the data without exiting
        if ($isTestEnv || headers_sent()) {
            echo "\nAPI RESPONSE (HTTP $statusCode):\n";
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // Set the HTTP status code
        http_response_code($statusCode);
        
        // Output the response as JSON
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}