<?php

declare(strict_types=1);

namespace App\ValueResolver;

use App\Exception\EmptyAuthenticationTokenException;
use App\Security\AuthenticationToken;
use SmartAssert\SecurityTokenExtractor\TokenExtractor;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

readonly class AuthenticationTokenResolver implements ValueResolverInterface
{
    public function __construct(
        private TokenExtractor $tokenExtractor,
        private HttpMessageFactoryInterface $httpMessageFactory,
    ) {
    }

    /**
     * @return AuthenticationToken[]
     *
     * @throws EmptyAuthenticationTokenException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        if (AuthenticationToken::class !== $argument->getType()) {
            return [];
        }

        $token = $this->tokenExtractor->extract($this->httpMessageFactory->createRequest($request));
        if (null === $token || '' === $token) {
            throw new EmptyAuthenticationTokenException();
        }

        return [new AuthenticationToken($token)];
    }
}
