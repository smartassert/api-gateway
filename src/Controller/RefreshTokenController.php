<?php

declare(strict_types=1);

namespace App\Controller;

use App\Response\EmptyBody;
use App\Response\ErrorResponse;
use App\Response\ErrorResponseBody;
use App\Response\Response;
use App\Security\AuthenticationToken;
use App\Security\RefreshToken;
use App\Security\UserId;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\UsersClient\Client;
use SmartAssert\UsersClient\Exception\UnauthorizedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

readonly class RefreshTokenController
{
    public function __construct(
        private Client $client
    ) {
    }

    #[Route('/user/refresh_token/revoke-all', name: 'user_revoke_all_refresh_token', methods: ['POST'])]
    public function revokeAllForUser(AuthenticationToken $token, UserId $userId): JsonResponse
    {
        try {
            $this->client->revokeFrontendRefreshTokensForUser($token->token, $userId->id);
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
        } catch (UnauthorizedException) {
            return new ErrorResponse(new ErrorResponseBody('unauthorized'), 401);
        } catch (NonSuccessResponseException $e) {
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
            new EmptyBody()
        );
    }

    #[Route('/user/refresh_token/revoke', name: 'user_revoke_refresh_token', methods: ['POST'])]
    public function revoke(AuthenticationToken $token, RefreshToken $refreshToken): JsonResponse
    {
        try {
            $this->client->revokeFrontendRefreshToken($token->token, $refreshToken->refreshToken);
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
        } catch (UnauthorizedException) {
            return new ErrorResponse(new ErrorResponseBody('unauthorized'), 401);
        } catch (NonSuccessResponseException $e) {
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
            new EmptyBody()
        );
    }
}
