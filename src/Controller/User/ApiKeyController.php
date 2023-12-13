<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Exception\ServiceException;
use App\Exception\UndefinedServiceException;
use App\Security\AuthenticationToken;
use App\ServiceProxy\ServiceCollection;
use App\ServiceProxy\ServiceProxy;
use App\ServiceRequest\RequestBuilderFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

readonly class ApiKeyController
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
    #[Route('/user/apikey{action}', name: 'user_apikey_act', requirements: ['action' => '.*'], methods: ['GET'])]
    public function list(AuthenticationToken $token, Request $request): Response
    {
        $uri = (string) preg_replace('#^/user#', '', $request->getRequestUri());
        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $uri);
        $httpRequest = $requestBuilder
            ->withBearerAuthorization($token->token)
            ->get()
        ;

        return $this->serviceProxy->sendRequest($this->serviceCollection->get('user'), $httpRequest);
    }
}
