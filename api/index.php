<?php
/**
 * Vercel Serverless Entry Point for CodeIgniter 4
 */

// Keep detailed diagnostics in logs, not in production responses.
$showErrors = getenv('CI_ENVIRONMENT') === 'development';
ini_set('display_errors', $showErrors ? '1' : '0');
ini_set('display_startup_errors', $showErrors ? '1' : '0');
error_reporting(E_ALL);

// Ensure serverless writable storage is initialized in /tmp
$tmpWritable = '/tmp/ci4_writable';
foreach (['cache', 'logs', 'session', 'debugbar', 'uploads'] as $folder) {
    $dir = $tmpWritable . '/' . $folder;
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Redirect PHP session storage to writable /tmp
ini_set('session.save_path', $tmpWritable . '/session');

// Instant health check: visit /?health=1 to verify PHP is running
if (isset($_GET['health']) || isset($_GET['test'])) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>ApexStride - Vercel PHP Health Check</title>';
    echo '<style>body{font-family:sans-serif;padding:30px;background:#0f172a;color:#f8fafc;} .card{background:#1e293b;border-radius:12px;padding:24px;max-width:640px;margin:auto;} .ok{color:#4ade80;} .warn{color:#fbbf24;} code{background:#334155;padding:2px 6px;border-radius:4px;font-size:13px;}</style></head><body>';
    echo '<div class="card">';
    echo '<h2>Vercel PHP Serverless: <span class="ok">ONLINE</span></h2>';
    echo '<p>PHP Version: <strong>' . PHP_VERSION . '</strong></p>';
    echo '<p>Writable /tmp: <span class="ok">OK</span></p>';
    echo '<p>MySQLi Driver: <span class="' . (extension_loaded('mysqli') ? 'ok">Loaded' : 'warn">Not loaded') . '</span></p>';
    
    $dbHost = getenv('DB_HOST') ?: getenv('database.default.hostname');
    echo '<hr style="border-color:#334155;margin:20px 0;">';
    echo '<h3>Database Configuration:</h3>';
    if ($dbHost) {
        echo '<p>DB Host: <code>' . htmlspecialchars($dbHost) . '</code></p>';
    } else {
        echo '<p class="warn">No DB_HOST set. You must add cloud database variables in Vercel.</p>';
    }
    echo '<p><a href="/" style="color:#38bdf8;">&larr; Go to Storefront</a></p>';
    echo '</div></body></html>';
    exit;
}

// Global Exception Handler so you never get a blank white page
set_exception_handler(function (\Throwable $e) use ($showErrors) {
    error_log('[APEXSTRIDE_ERROR] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: text/html; charset=utf-8');
    }
    if (!$showErrors) {
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>JatheStride - Error</title></head><body>';
        echo '<h1>Something went wrong</h1><p>Please try again shortly.</p></body></html>';
        exit;
    }

    echo '<!DOCTYPE html><html><head><title>ApexStride - Runtime Diagnostic</title>';
    echo '<style>body{font-family:sans-serif;padding:30px;background:#fff5f5;color:#991b1b;} .box{background:#fff;border:1px solid #fecaca;padding:24px;border-radius:8px;max-width:800px;margin:auto;} h2{margin-top:0;color:#dc2626;} pre{background:#f8fafc;padding:12px;border-radius:6px;overflow-x:auto;color:#334155;font-size:13px;}</style></head><body>';
    echo '<div class="box">';
    echo '<h2>ApexStride Startup Error Caught</h2>';
    echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ' on line ' . $e->getLine() . '</p>';
    echo '<h3>Stack Trace</h3>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</div></body></html>';
    exit;
});

// Require the front controller
require __DIR__ . '/../public/index.php';
