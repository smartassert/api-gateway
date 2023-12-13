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

readonly class CreationController
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
    #[Route('/user/create', name: 'user_create', methods: ['POST'])]
    public function create(AuthenticationToken $token, Request $request): Response
    {
        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $request->getRequestUri());
        $httpRequest = $requestBuilder
            ->withAuthorization($token->token)
            ->withBody(http_build_query($request->request->all()), (string) $request->headers->get('content-type'))
            ->get()
        ;

        return $this->serviceProxy->sendRequest($this->serviceCollection->get('user'), $httpRequest);
    }
}
