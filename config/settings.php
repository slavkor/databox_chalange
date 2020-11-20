<?php

// Error reporting for production
//error_reporting(0);
//ini_set('display_errors', '0');

ini_set('curl.cainfo', dirname(__DIR__).'/config/cacert.pem');


// Timezone
date_default_timezone_set('Europe/Berlin');

// Settings
$settings = [];

// Path settings
$settings['root'] = dirname(__DIR__);
$settings['temp'] = $settings['root'] . '/tmp';
$settings['public'] = $settings['root'] . '/public';
$settings['settings'] = $settings['root'] . '/settings';
$settings['config'] = $settings['root'] . '/config';

// Error Handling Middleware settings
$settings['error'] = [

    // Should be set to false in production
    'display_error_details' => true,

    // Parameter is passed to the default ErrorHandler
    // View in rendered output by enabling the "displayErrorDetails" setting.
    // For the console and unit tests we also disable it
    'log_errors' => true,

    // Display error details in error log
    'log_error_details' => true,
];

$settings['logger'] = [ 
                'name' => 'app',
                'path' => $settings['temp'].'/app.log',
                'level' => Monolog\Logger::DEBUG,
            ];

$settings['googleauthconfig'] = $settings['settings'].'/google_client_secrets.jsno';

$settings['session'] = [
    'name' => 'databopxapp',
    'cache_expire' => 0,
];

// Twig settings
$settings['twig'] = [
    // Template paths
    'paths' => [
        $settings['root'] . '/templates',
    ],
    // Twig environment options
    'options' => [
        // Should be set to true in production
        'cache_enabled' => false,
        'cache_path' => __DIR__ . '/../tmp/twig',
    ],
];

// google client settings
$settings['google'] = [
    'client_id' => '853566220918-6rf0l78i8lbvlfjkfbn4vl35fodsvmha.apps.googleusercontent.com',
    'client_secret' => 'tSytrr0xukjyTmHvfnVLE8wc'
];
// facebook client settings
$settings['facebook'] = [
    'client_id' => '1049210122167042',
    'client_secret' => '07e0756579f4f842bf8aad3fefaea723'
];

// databox  settings
$settings['databox'] = [
    'databoxtoken' => '1049210122167042'
];


return $settings;
