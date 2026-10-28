<?php

namespace Config;

/**
 * Paths Configuration for CodeIgniter 4 on Vercel Serverless
 * 
 * In Vercel's serverless runtime, the root filesystem is read-only.
 * Any attempt to write to standard `writable/` crashes the function.
 * This configuration redirects `$writableDirectory` to `/tmp/ci4_writable`.
 */
class Paths
{
    /**
     * System Directory
     */
    public string $systemDirectory = __DIR__ . '/../../system';

    /**
     * Application Directory
     */
    public string $appDirectory = __DIR__ . '/..';

    /**
     * Writable Directory - dynamically routed to /tmp in serverless/Vercel
     */
    public string $writableDirectory;

    /**
     * Tests Directory
     */
    public string $testsDirectory = __DIR__ . '/../../tests';

    /**
     * Views Directory
     */
    public string $viewDirectory = __DIR__ . '/../Views';

    public function __construct()
    {
        // Detect Vercel serverless environment or read-only filesystem
        $isVercel = !empty(getenv('VERCEL')) || !empty(getenv('NOW_REGION')) || !empty(getenv('AWS_LAMBDA_FUNCTION_NAME'));
        $defaultWritable = realpath(__DIR__ . '/../../writable');

        if ($isVercel || !$defaultWritable || !is_writable($defaultWritable)) {
            $this->writableDirectory = '/tmp/ci4_writable';
            
            // Guarantee required subdirectories exist in /tmp
            $subDirs = ['cache', 'logs', 'session', 'debugbar', 'uploads'];
            foreach ($subDirs as $dir) {
                $path = $this->writableDirectory . '/' . $dir;
                if (!is_dir($path)) {
                    @mkdir($path, 0777, true);
                }
            }
        } else {
            $this->writableDirectory = $defaultWritable;
        }
    }
}
