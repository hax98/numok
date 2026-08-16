<?php

// Database configuration
$config['database'] = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_NAME') ?: 'numok_app',
    'username' => getenv('DB_USER') ?: 'numok_user',
    'password' => getenv('DB_PASS') ?: 'change_me_app_2025',
];

// Email configuration
$config['email'] = [
    'resend_api_key' => getenv('RESEND_API_KEY') ?: 'RESEND_API_KEY',
    'from_address' => getenv('MAIL_FROM_ADDRESS') ?: 'onboarding@resend.dev',
];

// Initialize database connection
\Numok\Database\Database::setConfig($config['database']);

// Application configuration
$config['app'] = [
    'name' => getenv('APP_NAME') ?: 'Repostit Partners',
    'url' => getenv('APP_URL') ?: 'http://localhost',
    'debug' => filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOL)
];

// Time zone
date_default_timezone_set('UTC');

return $config;
