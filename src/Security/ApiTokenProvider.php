<?php

declare(strict_types=1);

namespace App\Security;

use App\Exception\ServiceException;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\HttpResponseExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\UsersClient\ClientInterface as UsersClient;

readonly class ApiTokenProvider
{
    public function __construct(
        private UsersClient $client,
    ) {
    }

    /**
     * @param non-empty-string $apiKey
     *
     * @return non-empty-string
     *
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    public function get(string $apiKey): string
    {
        try {
            $apiToken = $this->client->createApiToken($apiKey);
        } catch (
            ClientExceptionInterface |
            HttpResponseExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException |
            InvalidResponseTypeException $e
        ) {
            throw new ServiceException('user', $e);
        }

        return $apiToken->token;
    }
}
