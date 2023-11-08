<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Exception\ServiceException;
use App\Response\ErrorResponse;
use App\Response\ErrorResponseBody;
use App\Response\LabelledBody;
use App\Response\Response;
use App\Response\User\RefreshableToken;
use App\Response\User\User;
use App\Security\AuthenticationToken;
use App\Security\UserCredentials;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\UsersClient\ClientInterface as UsersClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user/token', name: 'user_token_')]
readonly class TokenController
{
    public function __construct(
        private UsersClient $client
    ) {
    }

    /**
     * @throws UnauthorizedException
     * @throws ServiceException
     */
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(UserCredentials $userCredentials): JsonResponse
    {
        try {
            $token = $this->client->createFrontendToken($userCredentials->userIdentifier, $userCredentials->password);
        } catch (
            ClientExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException |
            InvalidResponseTypeException |
            NonSuccessResponseException $e
        ) {
            throw new ServiceException('users', $e);
        }

        return new Response(
            new LabelledBody(
                'refreshable_token',
                new RefreshableToken($token->token, $token->refreshToken)
            )
        );
    }

    /**
     * @throws ServiceException
     */
    #[Route('/verify', name: 'verify', methods: ['GET'])]
    public function verify(AuthenticationToken $token): JsonResponse
    {
        try {
            $user = $this->client->verifyFrontendToken($token->token);
            if (null === $user) {
                return new ErrorResponse(new ErrorResponseBody('unauthorized'), 401);
            }
        } catch (
            ClientExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException |
            InvalidResponseTypeException |
            NonSuccessResponseException $e
        ) {
            throw new ServiceException('users', $e);
        }

        return new Response(
            new LabelledBody(
                'user',
                new User($user->id, $user->userIdentifier)
            )
        );
    }

    /**
     * @throws ServiceException
     */
    #[Route('/refresh', name: 'refresh', methods: ['POST'])]
    public function refresh(AuthenticationToken $token): JsonResponse
    {
        try {
            $refreshableToken = $this->client->refreshFrontendToken($token->token);

            if (null === $refreshableToken) {
                return new ErrorResponse(new ErrorResponseBody('unauthorized'), 401);
            }
        } catch (
            ClientExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException |
            InvalidResponseTypeException |
            NonSuccessResponseException $e
        ) {
            throw new ServiceException('users', $e);
        }

        return new Response(
            new LabelledBody(
                'refreshable_token',
                new RefreshableToken($refreshableToken->token, $refreshableToken->refreshToken)
            )
        );
    }
}
