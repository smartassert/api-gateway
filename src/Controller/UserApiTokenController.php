<?php

declare(strict_types=1);

namespace App\Controller;

use App\Response\ErrorResponse;
use App\Response\ErrorResponseBody;
use App\Response\LabelledBody;
use App\Response\Response;
use App\Response\User\Token;
use App\Security\AuthenticationToken;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\UsersClient\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user/api/token', name: 'user_api_token_')]
readonly class UserApiTokenController
{
    public function __construct(
        private Client $client
    ) {
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(AuthenticationToken $apiKey): JsonResponse
    {
        try {
            $token = $this->client->createApiToken($apiKey->token);
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
        } catch (InvalidModelDataException $e) {
            return new ErrorResponse(
                new ErrorResponseBody(
                    'invalid-model-data',
                    [
                        'service' => 'users',
                        'data' => $e->getResponse()->getBody(),
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

        return new Response(
            new LabelledBody(
                'token',
                new Token($token->token)
            )
        );
    }
}
