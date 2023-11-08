<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Exception\ServiceException;
use App\Response\EmptyBody;
use App\Response\ErrorResponse;
use App\Response\ErrorResponseBody;
use App\Response\Response;
use App\Security\AuthenticationToken;
use App\Security\RefreshToken;
use App\Security\UserId;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\UsersClient\ClientInterface as UsersClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

readonly class RefreshTokenController
{
    public function __construct(
        private UsersClient $client
    ) {
    }

    /**
     * @throws UnauthorizedException
     * @throws ServiceException
     * @throws NonSuccessResponseException
     */
    #[Route('/user/refresh_token/revoke-all', name: 'user_revoke_all_refresh_token', methods: ['POST'])]
    public function revokeAllForUser(AuthenticationToken $token, UserId $userId): JsonResponse
    {
        try {
            $this->client->revokeFrontendRefreshTokensForUser($token->token, $userId->id);
        } catch (ClientExceptionInterface $e) {
            throw new ServiceException('users', $e);
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

        return new Response(
            new EmptyBody()
        );
    }

    /**
     * @throws UnauthorizedException
     * @throws NonSuccessResponseException
     * @throws ServiceException
     */
    #[Route('/user/refresh_token/revoke', name: 'user_revoke_refresh_token', methods: ['POST'])]
    public function revoke(AuthenticationToken $token, RefreshToken $refreshToken): JsonResponse
    {
        try {
            $this->client->revokeFrontendRefreshToken($token->token, $refreshToken->refreshToken);
        } catch (ClientExceptionInterface $e) {
            throw new ServiceException('users', $e);
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

        return new Response(
            new EmptyBody()
        );
    }
}
