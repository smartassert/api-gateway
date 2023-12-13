<?php

declare(strict_types=1);

namespace App\Controller\Source;

use App\Exception\ServiceException;
use App\Exception\UndefinedServiceException;
use App\Security\ApiToken;
use App\ServiceProxy\ServiceCollection;
use App\ServiceProxy\ServiceProxy;
use App\ServiceRequest\RequestBuilderFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/source/git-source', name: 'git_source_')]
readonly class GitSourceController
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
    #[Route(path: '/{sourceId<[A-Z90-9]{26}>?}', name: 'act', methods: ['POST', 'PUT'])]
    public function act(ApiToken $token, Request $request): Response
    {
        $uri = (string) preg_replace('#^/source#', '', $request->getRequestUri());
        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $uri);
        $httpRequest = $requestBuilder
            ->withBearerAuthorization($token->token)
            ->withBody(http_build_query($request->request->all()), (string) $request->headers->get('content-type'))
            ->get()
        ;

        return $this->serviceProxy->sendRequest($this->serviceCollection->get('source'), $httpRequest);
    }
}
