<?php
/**
 * Configuration File
 * 
 * This file contains all the configuration settings for the application
 */

// Database configuration
$dbConfig = [
    'driver' => 'pgsql',
    'host' => '192.168.0.39',
    'port' => '5432',
    'database' => 'crm_clinic_db',
    'username' => 'admindba',
    'password' => 'wjstks'
];

// Initialize the database connection
\Api\Core\Database::init($dbConfig);

// Set timezone
date_default_timezone_set('America/Asuncion');

// Define API version
define('API_VERSION', '1.0.0');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);