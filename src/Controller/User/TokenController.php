<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Exception\ServiceException;
use App\Response\UnauthorizedResponse;
use App\Security\AuthenticationToken;
use App\ServiceProxy\ServiceProxy;
use App\ServiceRequest\RequestBuilderFactory;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\UsersClient\ClientInterface as UsersClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(name: 'user_token_')]
readonly class TokenController
{
    public function __construct(
        private UsersClient $client,
        private RequestBuilderFactory $requestBuilderFactory,
        private ServiceProxy $usersProxy,
    ) {
    }

    /**
     * @throws ServiceException
     */
    #[Route('/user/frontend-token/create', name: 'create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $uri = (string) preg_replace('#^/user#', '', $request->getRequestUri());

        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $uri);
        $httpRequest = $requestBuilder
            ->withBody($request->getContent(), (string) $request->headers->get('content-type'))
            ->get()
        ;

        try {
            return $this->usersProxy->sendRequest(request: $httpRequest);
        } catch (ClientExceptionInterface $exception) {
            throw new ServiceException('users', $exception);
        }
    }

    /**
     * @throws ServiceException
     */
    #[Route('/user/token/verify', name: 'verify', methods: ['GET'])]
    public function verify(AuthenticationToken $token): Response
    {
        try {
            $user = $this->client->verifyFrontendToken($token->token);
            if (null === $user) {
                return new UnauthorizedResponse();
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

        return new JsonResponse([
            'user' => $user->toArray(),
        ]);
    }

    /**
     * @throws ServiceException
     */
    #[Route('/user/token/refresh', name: 'refresh', methods: ['POST'])]
    public function refresh(AuthenticationToken $token): Response
    {
        try {
            $refreshableToken = $this->client->refreshFrontendToken($token->token);

            if (null === $refreshableToken) {
                return new UnauthorizedResponse();
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

        return new JsonResponse([
            'refreshable_token' => $refreshableToken->toArray(),
        ]);
    }
}
