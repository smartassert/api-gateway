<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Exception\ServiceException;
use App\Security\AuthenticationToken;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\UsersClient\ClientInterface as UsersClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user/apikey', name: 'user_apikey_')]
readonly class ApiKeyController
{
    public function __construct(
        private UsersClient $client
    ) {
    }

    /**
     * @throws UnauthorizedException
     * @throws ServiceException
     */
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(AuthenticationToken $token): JsonResponse
    {
        try {
            $apiKeys = $this->client->listUserApiKeys($token->token);
        } catch (
            ClientExceptionInterface |
            InvalidResponseDataException |
            InvalidResponseTypeException |
            NonSuccessResponseException $e
        ) {
            throw new ServiceException('users', $e);
        }

        return new JsonResponse([
            'api_keys' => $apiKeys->toArray(),
        ]);
    }

    /**
     * @throws UnauthorizedException
     * @throws ServiceException
     */
    #[Route('/', name: 'get_default', methods: ['GET'])]
    public function getDefault(AuthenticationToken $token): JsonResponse
    {
        try {
            $apiKey = $this->client->getUserDefaultApiKey($token->token);
        } catch (
            ClientExceptionInterface |
            InvalidResponseDataException |
            InvalidResponseTypeException |
            NonSuccessResponseException $e
        ) {
            throw new ServiceException('users', $e);
        }

        if (null === $apiKey) {
            return new JsonResponse(null, 404);
        }

        return new JsonResponse([
            'api_key' => $apiKey,
        ]);
    }
}
