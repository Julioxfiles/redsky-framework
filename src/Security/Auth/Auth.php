<?php

declare(strict_types=1);

namespace RedSky\Security\Auth;

use RedSky\Security\Jwt\JwtService;

class Auth
{
    public function __construct(
        protected JwtService $jwt
    ) {
    }

    /**
     * Obtiene el usuario a partir de un JWT.
     */
    public function user(string $token): ?array
    {
        if (!$this->check($token)) {
            return null;
        }

        return $this->jwt->decode($token);
    }

    /**
     * Verifica si el token es válido.
     */
    public function check(string $token): bool
    {
        return $this->jwt->verify($token);
    }

    /**
     * Genera un JWT para un usuario.
     */
    public function login(array $claims): string
    {
        return $this->jwt->encode($claims);
    }

    /**
     * JWT es stateless, por lo que logout no invalida el token.
     * La aplicación cliente debe eliminarlo.
     */
    public function logout(): void
    {
        // Stateless
    }
}