<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    public string $defaultGroup = 'default';
    public array $default = [];

    public function __construct()
    {
        parent::__construct();

        $host     = getenv('DB_HOST') ?: getenv('DB_HOSTNAME') ?: getenv('database.default.hostname') ?: '127.0.0.1';
        $database = getenv('DB_NAME') ?: getenv('DB_DATABASE') ?: getenv('database.default.database') ?: 'test';
        
        // Prevent using MySQL internal 'sys' schema
        if ($database === 'sys') {
            $database = 'test';
        }

        $username = getenv('DB_USER') ?: getenv('DB_USERNAME') ?: getenv('database.default.username') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: getenv('DB_PASS') ?: getenv('database.default.password') ?: '';
        $driver   = getenv('DB_DRIVER') ?: getenv('database.default.DBDriver') ?: 'MySQLi';

        // Auto-enable SSL for TiDB Cloud & modern cloud databases
        $isCloudHost = !empty($host) && (
            strpos($host, 'tidbcloud.com') !== false ||
            strpos($host, 'aivencloud.com') !== false ||
            strpos($host, 'planetscale.com') !== false
        );
        $defaultPort = strpos($host, 'tidbcloud.com') !== false ? 4000 : 3306;
        $port = (int)(getenv('DB_PORT') ?: getenv('database.default.port') ?: $defaultPort);
        $dbSsl = getenv('DB_SSL');

        if ($dbSsl === 'false' || $dbSsl === '0') {
            $sslConfig = false;
        } elseif ($dbSsl === 'true' || $dbSsl === '1' || $isCloudHost) {
            // Tells MySQLi to use TLS encryption (MYSQLI_CLIENT_SSL)
            $sslConfig = [
                'ssl_verify' => false,
            ];
        } else {
            $sslConfig = false;
        }

        $this->default = [
            'DSN'          => '',
            'hostname'     => $host,
            'username'     => $username,
            'password'     => $password,
            'database'     => $database,
            'DBDriver'     => $driver,
            'DBPrefix'     => '',
            'pConnect'     => false,
            'DBDebug'      => (getenv('CI_ENVIRONMENT') === 'development'),
            'charset'      => 'utf8mb4',
            'DBCollat'     => 'utf8mb4_unicode_ci',
            'swapPre'      => '',
            'encrypt'      => $sslConfig,
            'compress'     => false,
            'strictOn'     => false,
            'failover'     => [],
            'port'         => $port,
        ];
    }
}
