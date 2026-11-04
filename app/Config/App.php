<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Base Site URL
     * --------------------------------------------------------------------------
     *
     * URL to your CodeIgniter root. Dynamically detected on Vercel serverless
     * environments while preserving standard localhost support for local dev.
     */
    public string $baseURL = 'http://localhost:8080/';

    /**
     * Allowed Hostnames in the Site URL other than the hostname in the baseURL.
     *
     * @var list<string>
     */
    public array $allowedHostnames = [];

    /**
     * --------------------------------------------------------------------------
     * Index File
     * --------------------------------------------------------------------------
     *
     * Set to an empty string '' so Vercel does NOT redirect your site
     * to /index.php/. This provides clean modern URLs (e.g. /shoes instead of /index.php/shoes).
     */
    public string $indexPage = '';

    /**
     * --------------------------------------------------------------------------
     * URI PROTOCOL
     * --------------------------------------------------------------------------
     */
    public string $uriProtocol = 'REQUEST_URI';

    /*
    |--------------------------------------------------------------------------
    | Allowed URL Characters
    |--------------------------------------------------------------------------
    */
    public string $permittedURIChars = 'a-z 0-9~%.:_\-';

    /**
     * --------------------------------------------------------------------------
     * Default Locale
     * --------------------------------------------------------------------------
     */
    public string $defaultLocale = 'en';

    /**
     * --------------------------------------------------------------------------
     * Negotiate Locale
     * --------------------------------------------------------------------------
     */
    public bool $negotiateLocale = false;

    /**
     * --------------------------------------------------------------------------
     * Supported Locales
     * --------------------------------------------------------------------------
     *
     * @var list<string>
     */
    public array $supportedLocales = ['en'];

    /**
     * --------------------------------------------------------------------------
     * Application Timezone
     * --------------------------------------------------------------------------
     */
    public string $appTimezone = 'UTC';

    /**
     * --------------------------------------------------------------------------
     * Default Character Set
     * --------------------------------------------------------------------------
     */
    public string $charset = 'UTF-8';

    /**
     * --------------------------------------------------------------------------
     * Force Global Secure Requests
     * --------------------------------------------------------------------------
     */
    public bool $forceGlobalSecureRequests = false;

    /**
     * --------------------------------------------------------------------------
     * Reverse Proxy IPs
     * --------------------------------------------------------------------------
     *
     * On Vercel serverless, requests pass through Vercel's edge proxy network.
     * Setting proxyIPs enables CI4 to detect HTTPS and client IPs correctly.
     *
     * @var array<string, string>|list<string>
     */
    public array $proxyIPs = [
    '0.0.0.0/0' => 'X-Forwarded-For',
];

    /**
     * --------------------------------------------------------------------------
     * Content Security Policy
     * --------------------------------------------------------------------------
     */
    public bool $CSPEnabled = false;

    /**
     * Constructor: Automatically adapts baseURL for Vercel Serverless deployments
     */
    public function __construct()
    {
        parent::__construct();

        // 1. Check if environment variable app.baseURL or BASE_URL is provided
        $envBase = getenv('app.baseURL') ?: getenv('BASE_URL');
        if (!empty($envBase)) {
            $this->baseURL = rtrim($envBase, '/') . '/';
            return;
        }

        // 2. Auto-detect Vercel preview or production domain
        $vercelUrl = getenv('VERCEL_URL');
        if (!empty($vercelUrl)) {
            $this->baseURL = 'https://' . rtrim($vercelUrl, '/') . '/';
            return;
        }

        // 3. Auto-detect from HTTP Host header (works on custom domains & Vercel edge)
        if (!empty($_SERVER['HTTP_HOST'])) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                        ? 'https' : 'http';

            $this->baseURL = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/';
        }
    }
}
