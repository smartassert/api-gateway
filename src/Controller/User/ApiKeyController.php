<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Exception\ServiceException;
use App\Response\ErrorResponse;
use App\Response\ErrorResponseBody;
use App\Response\LabelledBody;
use App\Response\LabelledCollectionBody;
use App\Response\Response;
use App\Response\User\ApiKey;
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
     * @throws NonSuccessResponseException
     * @throws ServiceException
     */
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(AuthenticationToken $token): JsonResponse
    {
        try {
            $apiKeyCollection = $this->client->listUserApiKeys($token->token);
        } catch (ClientExceptionInterface | InvalidResponseDataException $e) {
            throw new ServiceException('users', $e);
        } catch (InvalidResponseTypeException $e) {
            return new ErrorResponse(
                new ErrorResponseBody(
                    'invalid-response-type',
                    [
                        'service' => 'users',
                        'content-type' => [
                            'expected' => $e->expected,
                            'actual' => $e->actual,
                        ],
                    ]
                )
            );
        } catch (NonSuccessResponseException $e) {
            if (404 === $e->getStatusCode()) {
                throw $e;
            }

            return new ErrorResponse(
                new ErrorResponseBody(
                    'non-successful-service-response',
                    [
                        'service' => 'users',
                        'status' => $e->getStatusCode(),
                        'message' => $e->getMessage(),
                    ]
                )
            );
        }

        $apiKeys = [];
        foreach ($apiKeyCollection as $usersClientApiKey) {
            $apiKeys[] = new ApiKey($usersClientApiKey->label, $usersClientApiKey->key);
        }

        return new Response(
            new LabelledCollectionBody('api_keys', $apiKeys)
        );
    }

    /**
     * @throws UnauthorizedException
     * @throws ServiceException
     * @throws NonSuccessResponseException
     */
    #[Route('/', name: 'get_default', methods: ['GET'])]
    public function getDefault(AuthenticationToken $token): JsonResponse
    {
        try {
            $apiKey = $this->client->getUserDefaultApiKey($token->token);
        } catch (ClientExceptionInterface | InvalidResponseDataException $e) {
            throw new ServiceException('users', $e);
        } catch (InvalidResponseTypeException $e) {
            return new ErrorResponse(
                new ErrorResponseBody(
                    'invalid-response-type',
                    [
                        'service' => 'users',
                        'content-type' => [
                            'expected' => $e->expected,
                            'actual' => $e->actual,
                        ],
                    ]
                )
            );
        } catch (NonSuccessResponseException $e) {
            if (404 === $e->getStatusCode()) {
                throw $e;
            }

            return new ErrorResponse(
                new ErrorResponseBody(
                    'non-successful-service-response',
                    [
                        'service' => 'users',
                        'status' => $e->getStatusCode(),
                        'message' => $e->getMessage(),
                    ]
                )
            );
        }

        if (null === $apiKey) {
            return new JsonResponse(null, 404);
        }

        return new Response(
            new LabelledBody(
                'api_key',
                new ApiKey($apiKey->label, $apiKey->key)
            )
        );
    }
}
