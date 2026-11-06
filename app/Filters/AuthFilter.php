<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public const TIMEOUT_SECONDS = 1800; // 30 minutes
    public const COOKIE_NAME     = 'admin_auth_token';

    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $now = time();

        // 1. Check if session has active admin
        $isLoggedIn = $session->get('is_logged_in') && $session->get('user_role') === 'admin';
        $lastActive = (int) $session->get('last_active');

        // Check for 30 minutes of inactivity timeout
        if ($isLoggedIn && $lastActive > 0 && ($now - $lastActive > self::TIMEOUT_SECONDS)) {
            $session->destroy();
            $this->clearAuthCookie();
            return redirect()->to('/login')->with('error', 'Your session timed out after 30 minutes of inactivity. Please log in again.');
        }

        // If active in session, update last_active timestamp and refresh cookie
        if ($isLoggedIn) {
            $session->set('last_active', $now);
            $this->setAuthCookie([
                'id'    => $session->get('user_id'),
                'name'  => $session->get('user_name'),
                'email' => $session->get('user_email'),
                'role'  => 'admin',
            ]);
            return;
        }

        // 2. Serverless fallback: If PHP session in /tmp was recycled across Vercel lambdas,
        // recover seamlessly from the signed HTTP-only cookie if within the 30-minute window
        $cookieToken = $_COOKIE[self::COOKIE_NAME] ?? null;
        $userData = $this->verifyToken($cookieToken);

        if ($userData && ($userData['role'] ?? '') === 'admin') {
            $session->set([
                'is_logged_in' => true,
                'user_id'      => $userData['id'],
                'user_name'    => $userData['name'] ?? 'Store Admin',
                'user_email'   => $userData['email'],
                'user_role'    => 'admin',
                'last_active'  => $now,
            ]);

            $this->setAuthCookie($userData);
            return;
        }

        return redirect()->to('/login')->with('error', 'Please log in with admin privileges to access this area.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    private static function getSecretKey(): string
    {
        $key = getenv('encryption.key') ?: getenv('APP_KEY') ?: getenv('DB_PASSWORD') ?: 'JatheStride2025SecretAdminSaltKey!#';
        return hash('sha256', (string)$key);
    }

    public function setAuthCookie(array $user): void
    {
        $payload = base64_encode(json_encode([
            'id'      => $user['id'] ?? 1,
            'name'    => $user['name'] ?? 'Store Admin',
            'email'   => $user['email'] ?? 'admin@solestore.com',
            'role'    => 'admin',
            'issued'  => time(),
            'expires' => time() + self::TIMEOUT_SECONDS,
        ]));

        $signature = hash_hmac('sha256', $payload, self::getSecretKey());
        $token = $payload . '.' . $signature;

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                   (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        setcookie(self::COOKIE_NAME, $token, [
            'expires'  => time() + self::TIMEOUT_SECONDS,
            'path'     => '/',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public function clearAuthCookie(): void
    {
        setcookie(self::COOKIE_NAME, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public function verifyToken(?string $token): ?array
    {
        if (empty($token) || !str_contains($token, '.')) {
            return null;
        }

        [$payload, $signature] = explode('.', $token, 2);
        $expectedSignature = hash_hmac('sha256', $payload, self::getSecretKey());

        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }

        $data = json_decode(base64_decode($payload), true);
        if (!is_array($data) || empty($data['expires']) || time() > $data['expires']) {
            return null;
        }

        return $data;
    }
}
