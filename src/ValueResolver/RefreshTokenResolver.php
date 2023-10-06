<?php

declare(strict_types=1);

namespace App\ValueResolver;

use App\Exception\EmptyRefreshTokenException;
use App\Security\RefreshToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class RefreshTokenResolver implements ValueResolverInterface
{
    /**
     * @return RefreshToken[]
     *
     * @throws EmptyRefreshTokenException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        if (RefreshToken::class !== $argument->getType()) {
            return [];
        }

        $refreshToken = $request->request->get('refresh_token');
        if (!is_string($refreshToken) || '' === $refreshToken) {
            throw new EmptyRefreshTokenException();
        }

        return [new RefreshToken($refreshToken)];
    }
}
