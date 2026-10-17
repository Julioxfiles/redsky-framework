<?php

declare(strict_types=1);

namespace RedSky\Framework\Session;

/**
 * Manages secure PHP sessions.
 *
 * Provides session initialization, data access, identifier
 * regeneration, and session destruction.
 */
class Session
{
    private string $name;

    private string $path;

    private ?string $domain;

    private bool $secure;

    private bool $httpOnly;

    private string $sameSite;

    private int $lifetime;

    /**
     * Create a session manager.
     *
     * @param string      $name     Session cookie name.
     * @param bool        $secure   Require HTTPS for the cookie.
     * @param bool        $httpOnly Prevent JavaScript access.
     * @param string      $sameSite SameSite cookie policy.
     * @param string      $path     Cookie path.
     * @param string|null $domain   Cookie domain.
     * @param int         $lifetime Cookie lifetime in seconds.
     */
    public function __construct(
        string $name = 'REDSKY_SESSION',
        bool $secure = true,
        bool $httpOnly = true,
        string $sameSite = 'Lax',
        string $path = '/',
        ?string $domain = null,
        int $lifetime = 0
    ) {
        $sameSite = ucfirst(strtolower($sameSite));

        if (!in_array($sameSite, ['Lax', 'Strict', 'None'], true)) {
            throw new \InvalidArgumentException(
                'SameSite must be Lax, Strict, or None.'
            );
        }

        if ($sameSite === 'None' && !$secure) {
            throw new \InvalidArgumentException(
                'SameSite=None requires Secure=true.'
            );
        }

        if ($name === '' || $path === '' || $lifetime < 0) {
            throw new \InvalidArgumentException(
                'Invalid session configuration.'
            );
        }

        $this->name = $name;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
        $this->sameSite = $sameSite;
        $this->path = $path;
        $this->domain = $domain;
        $this->lifetime = $lifetime;
    }

    /**
     * Start the PHP session using the configured cookie options.
     *
     * @return static
     */
    public function start(): static
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return $this;
        }

        if (headers_sent()) {
            throw new \RuntimeException(
                'Cannot start a session after headers have been sent.'
            );
        }

        session_name($this->name);

        session_set_cookie_params([
            'lifetime' => $this->lifetime,
            'path' => $this->path,
            'domain' => $this->domain ?? '',
            'secure' => $this->secure,
            'httponly' => $this->httpOnly,
            'samesite' => $this->sameSite,
        ]);

        if (!session_start()) {
            throw new \RuntimeException(
                'Failed to start the session.'
            );
        }

        return $this;
    }

    /**
     * Retrieve a session value.
     *
     * @param string $key     Session key.
     * @param mixed  $default Default value.
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureStarted();

        return $_SESSION[$key] ?? $default;
    }

    /**
     * Store a session value.
     *
     * @param string $key   Session key.
     * @param mixed  $value Value to store.
     *
     * @return static
     */
    public function put(string $key, mixed $value): static
    {
        $this->ensureStarted();

        $_SESSION[$key] = $value;

        return $this;
    }

    /**
     * Determine whether a session key exists.
     *
     * @param string $key Session key.
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        $this->ensureStarted();

        return array_key_exists($key, $_SESSION);
    }

    /**
     * Remove a session value.
     *
     * @param string $key Session key.
     *
     * @return static
     */
    public function forget(string $key): static
    {
        $this->ensureStarted();

        unset($_SESSION[$key]);

        return $this;
    }

    /**
     * Regenerate the session identifier.
     *
     * Call after successful authentication or privilege changes.
     *
     * @return static
     */
    public function regenerate(): static
    {
        $this->ensureStarted();

        if (!session_regenerate_id(true)) {
            throw new \RuntimeException(
                'Failed to regenerate the session identifier.'
            );
        }

        return $this;
    }

    /**
     * Destroy the current session and expire its cookie.
     *
     * @return void
     */
    public function destroy(): void
    {
        $this->ensureStarted();

        $_SESSION = [];

        if (!headers_sent()) {
            setcookie($this->name, '', [
                'expires' => time() - 42000,
                'path' => $this->path,
                'domain' => $this->domain ?? '',
                'secure' => $this->secure,
                'httponly' => $this->httpOnly,
                'samesite' => $this->sameSite,
            ]);
        }

        if (!session_destroy()) {
            throw new \RuntimeException(
                'Failed to destroy the session.'
            );
        }
    }

    /**
     * Ensure the session is active before accessing its data.
     *
     * @return void
     */
    private function ensureStarted(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $this->start();
        }
    }
}