<?php

declare(strict_types=1);

namespace App\Controller\Source;

use App\Exception\ServiceException;
use App\Exception\UndefinedServiceException;
use App\Security\ApiToken;
use App\ServiceProxy\ServiceCollection;
use App\ServiceProxy\ServiceProxy;
use App\ServiceRequest\RequestBuilderFactory;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

readonly class SourceController
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
    #[Route(path: '/source/sources', name: 'sources_list', methods: ['GET'])]
    public function list(ApiToken $token, Request $request): Response
    {
        $uri = (string) preg_replace('#^/source#', '', $request->getRequestUri());
        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $uri);
        $httpRequest = $requestBuilder
            ->withBearerAuthorization($token->token)
            ->get()
        ;

        $service = $this->serviceCollection->get('source');

        try {
            return $this->serviceProxy->sendRequest($service, $httpRequest);
        } catch (ClientExceptionInterface $exception) {
            throw new ServiceException($service->getName(), $exception);
        }
    }

    /**
     * @throws ServiceException
     * @throws UndefinedServiceException
     */
    #[Route(path: '/source/{sourceId<[A-Z90-9]{26}>}', name: 'source_act', methods: ['GET', 'DELETE'])]
    public function act(ApiToken $token, Request $request): Response
    {
        $uri = (string) preg_replace('#^/source#', '', $request->getRequestUri());
        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $uri);
        $httpRequest = $requestBuilder
            ->withBearerAuthorization($token->token)
            ->get()
        ;

        $service = $this->serviceCollection->get('source');

        try {
            return $this->serviceProxy->sendRequest($service, $httpRequest);
        } catch (ClientExceptionInterface $exception) {
            throw new ServiceException($service->getName(), $exception);
        }
    }
}
