<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Exception\ServiceException;
use App\Response\EmptyResponse;
use App\Security\AuthenticationToken;
use App\Security\RefreshToken;
use App\Security\UserId;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\UsersClient\ClientInterface as UsersClient;
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
     */
    #[Route('/user/refresh_token/revoke-all', name: 'user_revoke_all_refresh_token', methods: ['POST'])]
    public function revokeAllForUser(AuthenticationToken $token, UserId $userId): EmptyResponse
    {
        try {
            $this->client->revokeFrontendRefreshTokensForUser($token->token, $userId->id);
        } catch (ClientExceptionInterface | NonSuccessResponseException $e) {
            throw new ServiceException('users', $e);
        }

        return new EmptyResponse();
    }

    /**
     * @throws UnauthorizedException
     * @throws ServiceException
     */
    #[Route('/user/refresh_token/revoke', name: 'user_revoke_refresh_token', methods: ['POST'])]
    public function revoke(AuthenticationToken $token, RefreshToken $refreshToken): EmptyResponse
    {
        try {
            $this->client->revokeFrontendRefreshToken($token->token, $refreshToken->refreshToken);
        } catch (ClientExceptionInterface | NonSuccessResponseException $e) {
            throw new ServiceException('users', $e);
        }

        return new EmptyResponse();
    }
}
