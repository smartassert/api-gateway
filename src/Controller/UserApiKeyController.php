<?php

declare(strict_types=1);

namespace App\Controller;

use App\Response\ErrorResponse;
use App\Response\ErrorResponseBody;
use App\Response\LabelledCollectionBody;
use App\Response\Response;
use App\Response\User\ApiKey;
use App\Security\AuthenticationToken;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\UsersClient\Client;
use SmartAssert\UsersClient\Model\Token as UsersClientToken;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user/apikey', name: 'user_apikey_')]
readonly class UserApiKeyController
{
    public function __construct(
        private Client $client
    ) {
    }

    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(AuthenticationToken $token): JsonResponse
    {
        try {
            $apiKeyCollection = $this->client->listUserApiKeys(
                new UsersClientToken($token->token)
            );
        } catch (ClientExceptionInterface $e) {
            $code = $e->getCode();
            $message = $e->getMessage();

            return new ErrorResponse(
                new ErrorResponseBody(
                    'service-communication-failure',
                    [
                        'service' => 'users',
                        'error' => [
                            'code' => $code,
                            'message' => $message,
                        ],
                    ]
                )
            );
        } catch (InvalidResponseDataException $e) {
            return new ErrorResponse(
                new ErrorResponseBody(
                    'invalid-response-data',
                    [
                        'service' => 'users',
                        'data' => $e->getResponse()->getBody(),
                        'data-type' => [
                            'expected' => $e->expected,
                            'actual' => $e->actual,
                        ],
                    ]
                )
            );
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
            if (401 === $e->getStatusCode()) {
                return new ErrorResponse(
                    new ErrorResponseBody('unauthorized'),
                    $e->getStatusCode()
                );
            }

            if (404 === $e->getStatusCode()) {
                return new ErrorResponse(
                    new ErrorResponseBody('not-found'),
                    $e->getStatusCode()
                );
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
}
