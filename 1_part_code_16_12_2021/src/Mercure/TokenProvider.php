<?php

namespace App\Mercure;

use Symfony\Component\Mercure\Jwt\TokenProviderInterface;

/**
 *
 */
class TokenProvider implements TokenProviderInterface
{
    public function getJwt(): string
    {
        // TODO: Программно можем установить jwt
        return $_ENV["MERCURE_JWT"] ?? "";
    }
}