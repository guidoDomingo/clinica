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
     * Send a response
     * 
     * @param mixed $data The response data
     * @param int $statusCode The HTTP status code
     * @return void
     */
    private static function send($data, $statusCode = 200)
    {
        // Set the HTTP status code
        http_response_code($statusCode);
        
        // Output the response as JSON
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
}