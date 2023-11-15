<?php

declare(strict_types=1);

namespace App\ValueResolver;

use App\Exception\EmptyAuthenticationTokenException;
use App\Exception\ServiceException;
use App\Security\ApiToken;
use App\Security\ApiTokenProvider;
use SmartAssert\SecurityTokenExtractor\TokenExtractor;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

readonly class ApiTokenResolver implements ValueResolverInterface
{
    public function __construct(
        private TokenExtractor $tokenExtractor,
        private HttpMessageFactoryInterface $httpMessageFactory,
        private ApiTokenProvider $apiTokenProvider,
    ) {
    }

    /**
     * @return ApiToken[]
     *
     * @throws EmptyAuthenticationTokenException
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        if (ApiToken::class !== $argument->getType()) {
            return [];
        }

        $apiKey = $this->tokenExtractor->extract($this->httpMessageFactory->createRequest($request));
        if (null === $apiKey || '' === $apiKey) {
            throw new EmptyAuthenticationTokenException();
        }

        return [new ApiToken($this->apiTokenProvider->get($apiKey))];
    }
}
