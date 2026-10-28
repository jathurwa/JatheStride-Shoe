<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Log\Handlers\FileHandler;

/**
 * Logger Configuration for Vercel Serverless
 * 
 * Redirects log destination to /tmp or stderr to prevent read-only crashes
 * on Vercel's ephemeral Lambda execution environment.
 */
class Logger extends BaseConfig
{
    /**
     * Threshold: 0 = Disables logging, 4 = All Messages
     * In serverless production, errors and critical items are logged to stderr or /tmp
     */
    public $threshold = 4;

    /**
     * Array of date formats
     */
    public string $dateFormat = 'Y-m-d H:i:s';

    /**
     * Active Log Handlers
     */
    public array $handlers = [
        FileHandler::class => [
            'handles' => ['critical', 'alert', 'emergency', 'debug', 'error', 'info', 'notice', 'warning'],
            'fileExtension' => '',
            'filePermissions' => 0666,
            // Point path dynamically to /tmp/ci4_writable/logs/ to prevent read-only filesystem crash
            'path' => '/tmp/ci4_writable/logs/',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        // In Vercel serverless, verify the /tmp/ci4_writable/logs directory exists
        $logPath = '/tmp/ci4_writable/logs/';
        if (!is_dir($logPath)) {
            @mkdir($logPath, 0777, true);
        }
    }
}
