<?php

declare(strict_types=1);

namespace RedSky\Security\Jwt;

use Exception;

class JwtService
{
    protected string $secret;
    protected string $algo;
    protected int $ttl;

    public function __construct(array $config)
    {
        if (empty($config['secret'])) {
            throw new Exception('JWT secret is not configured.');
        }

        $this->secret = $config['secret'];
        $this->algo   = $config['algorithm'] ?? 'HS256';
        $this->ttl    = $config['ttl'] ?? 3600;
    }

    /* ========================================
     * Encode
     * ====================================== */
    public function encode(array $claims, ?int $ttlSeconds = null): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => $this->algo,
        ];

        $now = time();
        $ttlSeconds ??= $this->ttl;

        $payload = array_merge($claims, [
            'iat' => $now,
            'exp' => $now + $ttlSeconds,
        ]);

        $headerEncoded  = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

        $signature = $this->sign($headerEncoded . '.' . $payloadEncoded);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signature;
    }

    /* ========================================
     * Decode (untrusted)
     * ====================================== */
    public function decode(string $token): array
    {
        [, $payload] = $this->splitToken($token);

        return json_decode(
            $this->base64UrlDecode($payload),
            true
        );
    }

    /* ========================================
     * Verify
     * ====================================== */
    public function verify(string $token): bool
    {
        try {
            [$header, $payload, $signature] = $this->splitToken($token);

            $expected = $this->sign($header . '.' . $payload);

            if (!hash_equals($expected, $signature)) {
                return false;
            }

            $claims = json_decode(
                $this->base64UrlDecode($payload),
                true
            );

            return isset($claims['exp']) && time() < $claims['exp'];

        } catch (Exception) {
            return false;
        }
    }

    /* ========================================
     * Helpers
     * ====================================== */
    protected function sign(string $data): string
    {
        return $this->base64UrlEncode(
            hash_hmac($this->algo === 'HS256' ? 'sha256' : 'sha256', $data, $this->secret, true)
        );
    }

    protected function splitToken(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new Exception('Invalid JWT structure.');
        }

        return $parts;
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}