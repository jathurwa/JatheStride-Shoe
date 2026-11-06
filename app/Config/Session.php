<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Session\Handlers\BaseHandler;
use CodeIgniter\Session\Handlers\FileHandler;

class Session extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Session Driver
     * --------------------------------------------------------------------------
     *
     * The session storage driver to use:
     * - `CodeIgniter\Session\Handlers\ArrayHandler` (for testing)
     * - `CodeIgniter\Session\Handlers\FileHandler`
     * - `CodeIgniter\Session\Handlers\DatabaseHandler`
     * - `CodeIgniter\Session\Handlers\MemcachedHandler`
     * - `CodeIgniter\Session\Handlers\RedisHandler`
     *
     * @var class-string<BaseHandler>
     */
    public string $driver = FileHandler::class;

    /**
     * --------------------------------------------------------------------------
     * Session Cookie Name
     * --------------------------------------------------------------------------
     *
     * The session cookie name, must contain only [0-9a-z_-] characters
     */
    public string $cookieName = 'ci_session';

    /**
     * --------------------------------------------------------------------------
     * Session Expiration (30 Minutes = 1800 Seconds)
     * --------------------------------------------------------------------------
     *
     * The number of SECONDS you want the session to last.
     * Set to 1800 (30 minutes) as requested for admin sessions.
     */
    public int $expiration = 1800;

    /**
     * --------------------------------------------------------------------------
     * Session Save Path
     * --------------------------------------------------------------------------
     *
     * The location to save sessions to and is driver dependent.
     */
    public string $savePath = WRITEPATH . 'session';

    /**
     * --------------------------------------------------------------------------
     * Session Match IP
     * --------------------------------------------------------------------------
     *
     * Kept false so cellular or dynamic IP shifts do not log the admin out.
     */
    public bool $matchIP = false;

    /**
     * --------------------------------------------------------------------------
     * Session Time to Update (30 Minutes = 1800 Seconds)
     * --------------------------------------------------------------------------
     *
     * Set to 1800 to avoid aggressive mid-session regeneration (previously 300s)
     * which caused premature logouts while editing or adding products.
     */
    public int $timeToUpdate = 1800;

    /**
     * --------------------------------------------------------------------------
     * Session Regenerate Destroy
     * --------------------------------------------------------------------------
     */
    public bool $regenerateDestroy = false;

    /**
     * --------------------------------------------------------------------------
     * Session Database Group
     * --------------------------------------------------------------------------
     */
    public ?string $DBGroup = null;

    /**
     * --------------------------------------------------------------------------
     * Lock Retry Interval (microseconds)
     * --------------------------------------------------------------------------
     */
    public int $lockRetryInterval = 100_000;

    /**
     * --------------------------------------------------------------------------
     * Lock Max Retries
     * --------------------------------------------------------------------------
     */
    public int $lockMaxRetries = 300;
}
