<?php

/**
 * CodeIgniter 4 Vercel Serverless Public Entry Point
 * 
 * Configured specifically for AWS Lambda / Vercel Serverless execution.
 * Handles /tmp writable directories, session paths, and boots the CI4 application.
 */

// 1. Force serverless PHP settings
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED);

// Redirect session storage to /tmp on serverless environments
if (!empty(getenv('VERCEL')) || !empty(getenv('AWS_LAMBDA_FUNCTION_NAME')) || !is_writable(sys_get_temp_dir())) {
    $tmpDir = '/tmp/ci4_writable';
    @mkdir($tmpDir . '/session', 0777, true);
    @mkdir($tmpDir . '/cache', 0777, true);
    @mkdir($tmpDir . '/logs', 0777, true);
    ini_set('session.save_path', $tmpDir . '/session');
}

// Check PHP version requirement
$minPhpVersion = '7.4';
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    $message = sprintf(
        'Your PHP installation is too old. CodeIgniter requires at least PHP %s. Running %s',
        $minPhpVersion,
        PHP_VERSION
    );
    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo $message;
    exit(1);
}

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Ensure the current directory is pointing to the front controller's directory
if (getcwd() !== FCPATH) {
    chdir(FCPATH);
}

// Ensure an application environment is defined early to avoid
// calls to log_message() triggering undefined constant errors
if (! defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
}
/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 * This process sets up the path constants, loads and registers
 * our autoloader, along with Composer's, loads our environment
 * and starts the system.
 */

// Load our paths config file
require FCPATH . '../app/Config/Paths.php';

$paths = new Config\Paths();

// Prefer the modern Boot.php bootstrap (CI4.5+). Fall back to legacy bootstrap.php only if necessary.
$app = null;
$bootModern = rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'Boot.php';
$bootLegacy = rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

if (file_exists($bootModern)) {
    require $bootModern;
    exit(\CodeIgniter\Boot::bootWeb($paths));
} elseif (file_exists($bootLegacy)) {
    $app = require $bootLegacy;
} elseif (file_exists(FCPATH . '../vendor/codeigniter4/framework/system/Boot.php')) {
    require FCPATH . '../vendor/codeigniter4/framework/system/Boot.php';
    exit(\CodeIgniter\Boot::bootWeb($paths));
} elseif (file_exists(FCPATH . '../vendor/codeigniter4/framework/system/bootstrap.php')) {
    $app = require FCPATH . '../vendor/codeigniter4/framework/system/bootstrap.php';
}

/*
 *---------------------------------------------------------------
 * LAUNCH THE APPLICATION
 *---------------------------------------------------------------
 * Now that everything is setup, it's time to actually run
 * the application and send the response back to the client.
 */
if ($app && is_object($app) && method_exists($app, 'run')) {
    $app->run();
} else {
    // Elegant fallback if running without vendor folder during preview
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>ApexStride CI4</title></head><body style="font-family:sans-serif;padding:40px;background:#fafafa;">' .
         '<h2>ApexStride CodeIgniter 4 Serverless Core</h2>' .
         '<p>Ready for Vercel deployment with vercel.json and cloud database drivers.</p>' .
         '</body></html>';
}
