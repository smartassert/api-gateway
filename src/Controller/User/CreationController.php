<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Exception\ServiceException;
use App\Response\ErrorResponse;
use App\Response\ErrorResponseBody;
use App\Security\AuthenticationToken;
use App\Security\UserCredentials;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\UsersClient\ClientInterface as UsersClient;
use SmartAssert\UsersClient\Exception\UserAlreadyExistsException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

readonly class CreationController
{
    public function __construct(
        private UsersClient $client
    ) {
    }

    /**
     * @throws UnauthorizedException
     * @throws ServiceException
     */
    #[Route('/user/create', name: 'user_create', methods: ['POST'])]
    public function create(AuthenticationToken $token, UserCredentials $userCredentials): JsonResponse
    {
        try {
            $user = $this->client->createUser(
                $token->token,
                $userCredentials->userIdentifier,
                $userCredentials->password
            );
        } catch (
            ClientExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException |
            InvalidResponseTypeException |
            NonSuccessResponseException $e
        ) {
            throw new ServiceException('users', $e);
        } catch (UserAlreadyExistsException) {
            return new ErrorResponse(
                new ErrorResponseBody('user-already-exists'),
                409
            );
        }

        return new JsonResponse([
            'user' => $user->toArray(),
        ]);
    }
}
