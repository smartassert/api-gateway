<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Exception\ServiceException;
use App\Exception\UndefinedServiceException;
use App\ServiceProxy\ServiceCollection;
use App\ServiceProxy\ServiceProxy;
use App\ServiceRequest\RequestBuilderFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

readonly class RefreshTokenController
{
    public function __construct(
        private RequestBuilderFactory $requestBuilderFactory,
        private ServiceProxy $serviceProxy,
        private ServiceCollection $serviceCollection,
    ) {
    }

    /**
     * @throws ServiceException
     * @throws UndefinedServiceException
     */
    #[Route('/user/refresh-token/revoke-all-for-user', name: 'user_revoke_all_refresh_token', methods: ['POST'])]
    public function revokeAllForUser(Request $request): Response
    {
        $uri = (string) preg_replace('#^/user#', '', $request->getRequestUri());

        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $uri);
        $httpRequest = $requestBuilder
            ->withAuthorization((string) $request->headers->get('authorization'))
            ->withBody(http_build_query($request->request->all()), (string) $request->headers->get('content-type'))
            ->get()
        ;

        return $this->serviceProxy->sendRequest($this->serviceCollection->get('user'), $httpRequest);
    }

    /**
     * @throws ServiceException
     * @throws UndefinedServiceException
     */
    #[Route('/user/refresh-token/revoke ', name: 'user_revoke_refresh_token', methods: ['POST'])]
    public function revoke(Request $request): Response
    {
        $uri = (string) preg_replace('#^/user#', '', $request->getRequestUri());

        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $uri);
        $httpRequest = $requestBuilder
            ->withAuthorization((string) $request->headers->get('authorization'))
            ->withBody(http_build_query($request->request->all()), (string) $request->headers->get('content-type'))
            ->get()
        ;

        return $this->serviceProxy->sendRequest($this->serviceCollection->get('user'), $httpRequest);
    }
}
