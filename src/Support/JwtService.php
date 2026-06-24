<?php

declare(strict_types=1);

namespace RedSky\Framework\Support;

class JwtService
{
    protected string $secret;

    public function __construct()
    {
        $this->secret = env('JWT_SECRET', 'change_me');
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public function generate(array $user, int $ttl = 3600): string
    {
        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT',
        ]));

        $payload = $this->base64UrlEncode(json_encode([
            'sub' => $user['id'] ?? null,
            'email' => $user['email'] ?? null,
            'iat' => time(),
            'exp' => time() + $ttl,
        ]));

        $signature = hash_hmac(
            'sha256',
            "$header.$payload",
            $this->secret,
            true
        );

        $signature = $this->base64UrlEncode($signature);

        return "$header.$payload.$signature";
    }

    public function verify(string $token): bool
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        [$header, $payload, $signature] = $parts;

        $validSignature = $this->base64UrlEncode(
            hash_hmac(
                'sha256',
                "$header.$payload",
                $this->secret,
                true
            )
        );

        if (!hash_equals($signature, $validSignature)) {
            return false;
        }

        // validate exp
        $payloadData = json_decode($this->base64UrlDecode($payload), true);

        if (!is_array($payloadData)) {
            return false;
        }

        if (isset($payloadData['exp']) && time() > $payloadData['exp']) {
            return false;
        }

        return true;
    }

    public function decode(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [, $payload] = $parts;

        $data = json_decode($this->base64UrlDecode($payload), true);

        return is_array($data) ? $data : null;
    }
    
}