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

readonly class RefreshTokenController
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
    #[Route('/user/refresh-token/revoke-all-for-user', name: 'user_revoke_all_refresh_token', methods: ['POST'])]
    public function revokeAllForUser(AuthenticationToken $token, Request $request): Response
    {
        $uri = (string) preg_replace('#^/user#', '', $request->getRequestUri());

        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $uri);
        $httpRequest = $requestBuilder
            ->withAuthorization($token->token)
            ->withBody(http_build_query($request->request->all()), (string) $request->headers->get('content-type'))
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
    #[Route('/user/refresh-token/revoke ', name: 'user_revoke_refresh_token', methods: ['POST'])]
    public function revoke(AuthenticationToken $token, Request $request): Response
    {
        $uri = (string) preg_replace('#^/user#', '', $request->getRequestUri());

        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $uri);
        $httpRequest = $requestBuilder
            ->withBearerAuthorization($token->token)
            ->withBody(http_build_query($request->request->all()), (string) $request->headers->get('content-type'))
            ->get()
        ;

        try {
            return $this->serviceProxy->sendRequest($this->userService, $httpRequest);
        } catch (ClientExceptionInterface $exception) {
            throw new ServiceException($this->userService->getName(), $exception);
        }
    }
}
