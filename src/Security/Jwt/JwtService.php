<?php
declare(strict_types=1);

namespace RedSky\Framework\Security\Jwt;

use Exception;

class JwtService
{
    protected string $secret;
    protected string $algo = 'HS256';

    public function __construct(string $secret)
    {
        if ($secret === '') {
            throw new Exception('JWT secret is not configured.');
        }

        $this->secret = $secret;
    }

    /* ========================================
     * Encode
     * ====================================== */
    public function encode(array $claims, int $ttlSeconds = 3600): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => $this->algo,
        ];

        $now = time();

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
            hash_hmac('sha256', $data, $this->secret, true)
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