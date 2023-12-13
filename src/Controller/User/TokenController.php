<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Exception\ServiceException;
use App\Security\AuthenticationToken;
use App\ServiceProxy\Service;
use App\ServiceProxy\ServiceProxy;
use App\ServiceRequest\RequestBuilderFactory;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(name: 'user_token_')]
readonly class TokenController
{
    public function __construct(
        private RequestBuilderFactory $requestBuilderFactory,
        private ServiceProxy $serviceProxy,
        private Service $userService,
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
            return $this->serviceProxy->sendRequest($this->userService, $httpRequest);
        } catch (ClientExceptionInterface $exception) {
            throw new ServiceException($this->userService->getName(), $exception);
        }
    }

    /**
     * @throws ServiceException
     */
    #[Route('/user/frontend-token/verify', name: 'verify', methods: ['GET'])]
    public function verify(AuthenticationToken $token, Request $request): Response
    {
        $uri = (string) preg_replace('#^/user#', '', $request->getRequestUri());

        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $uri);
        $httpRequest = $requestBuilder
            ->withBearerAuthorization($token->token)
            ->get()
        ;

        try {
            return $this->serviceProxy->sendRequest($this->userService, $httpRequest);
        } catch (ClientExceptionInterface $exception) {
            throw new ServiceException($this->userService->getName(), $exception);
        }
    }

    /**
     * @throws ServiceException
     */
    #[Route('/user/frontend-token/refresh ', name: 'refresh', methods: ['POST'])]
    public function refresh(Request $request): Response
    {
        $uri = (string) preg_replace('#^/user#', '', $request->getRequestUri());

        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $uri);
        $httpRequest = $requestBuilder
            ->withBody($request->getContent(), (string) $request->headers->get('content-type'))
            ->get()
        ;

        try {
            return $this->serviceProxy->sendRequest($this->userService, $httpRequest);
        } catch (ClientExceptionInterface $exception) {
            throw new ServiceException($this->userService->getName(), $exception);
        }
    }
}
