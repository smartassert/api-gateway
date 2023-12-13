<?php

declare(strict_types=1);

namespace App\Controller\Source;

use App\Exception\ServiceException;
use App\Exception\UndefinedServiceException;
use App\ServiceProxy\ServiceCollection;
use App\ServiceProxy\ServiceProxy;
use App\ServiceRequest\RequestBuilderFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/source/file-source', name: 'file_source_')]
readonly class FileSourceController
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
    public function act(Request $request): Response
    {
        $uri = (string) preg_replace('#^/source#', '', $request->getRequestUri());
        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $uri);
        $httpRequest = $requestBuilder
            ->withAuthorization((string) $request->headers->get('authorization'))
            ->withBody(http_build_query($request->request->all()), (string) $request->headers->get('content-type'))
            ->get()
        ;

        return $this->serviceProxy->sendRequest($this->serviceCollection->get('source'), $httpRequest);
    }

    /**
     * @throws ServiceException
     * @throws UndefinedServiceException
     */
    #[Route(path: '/{sourceId<[A-Z90-9]{26}>}/list/', name: 'list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $uri = (string) preg_replace('#^/source#', '', $request->getRequestUri());
        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $uri);
        $httpRequest = $requestBuilder
            ->withAuthorization((string) $request->headers->get('authorization'))
            ->get()
        ;

        return $this->serviceProxy->sendRequest($this->serviceCollection->get('source'), $httpRequest);
    }
}
