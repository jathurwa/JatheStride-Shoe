<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration strictly driven by environment variables for Vercel Serverless
 */
class Database extends Config
{
    /**
     * The default database group.
     */
    public string $defaultGroup = 'default';

    /**
     * Path to the app Database directory (used for Migrations/Seeds files).
     */
    public string $filesPath = APPPATH . 'Database/';

    /**
     * The default connection settings.
     * Configured to read strictly via getenv() for Vercel Cloud deployment.
     */
    public array $default = [];

    public function __construct()
    {
        parent::__construct();

        // Support standard CI environment variables, standard DB_* keys, or DATABASE_URL parsing
        $host     = getenv('DB_HOST') ?: getenv('database.default.hostname') ?: '127.0.0.1';
        $database = getenv('DB_NAME') ?: getenv('database.default.database') ?: 'shoestore_db';
        $username = getenv('DB_USER') ?: getenv('database.default.username') ?: 'root';
        $password = getenv('DB_PASS') ?: getenv('database.default.password') ?: '';
        $port     = (int)(getenv('DB_PORT') ?: getenv('database.default.port') ?: 3306);
        $driver   = getenv('DB_DRIVER') ?: getenv('database.default.DBDriver') ?: 'MySQLi';

        // Optional DATABASE_URL parser for PaaS providers (Neon, PlanetScale, Supabase)
        $databaseUrl = getenv('DATABASE_URL');
        if (!empty($databaseUrl)) {
            $parsed = parse_url($databaseUrl);
            if ($parsed !== false) {
                $host     = $parsed['host'] ?? $host;
                $port     = isset($parsed['port']) ? (int)$parsed['port'] : $port;
                $username = $parsed['user'] ?? $username;
                $password = $parsed['pass'] ?? $password;
                $database = isset($parsed['path']) ? ltrim($parsed['path'], '/') : $database;
                if (isset($parsed['scheme']) && ($parsed['scheme'] === 'postgres' || $parsed['scheme'] === 'postgresql')) {
                    $driver = 'Postgre';
                }
            }
        }

        $this->default = [
            'DSN'          => '',
            'hostname'     => $host,
            'username'     => $username,
            'password'     => $password,
            'database'     => $database,
            'DBDriver'     => $driver,
            'DBPrefix'     => '',
            'pConnect'     => false, // Disabled for serverless to prevent connection exhaustion
            'DBDebug'      => (getenv('CI_ENVIRONMENT') === 'development'),
            'charset'      => 'utf8mb4',
            'DBCollat'     => 'utf8mb4_unicode_ci',
            'swapPre'      => '',
            'encrypt'      => (bool)(getenv('DB_SSL') ?: false),
            'compress'     => false,
            'strictOn'     => false,
            'failover'     => [],
            'port'         => $port,
            'numberNative' => false,
            'dateFormat'   => [
                'date'     => 'Y-m-d',
                'datetime' => 'Y-m-d H:i:s',
                'time'     => 'H:i:s',
            ],
        ];
    }
}
