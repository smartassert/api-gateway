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

#[Route(path: '/source/file-source/{sourceId<[A-Z90-9]{26}>}/{filename<.*\.yaml>}', name: 'file_source_file_')]
readonly class FileSourceFileController
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
    #[Route(name: 'handle', methods: ['GET', 'POST', 'PUT', 'DELETE'])]
    public function handle(Request $request): Response
    {
        $uri = (string) preg_replace('#^/source#', '', $request->getRequestUri());
        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $uri);
        $requestBuilder = $requestBuilder->withAuthorization((string) $request->headers->get('authorization'));

        if ('POST' === $request->getMethod() || 'PUT' === $request->getMethod()) {
            $requestBuilder = $requestBuilder->withBody(
                (string) $request->getContent(),
                (string) $request->headers->get('content-type')
            );
        }

        $httpRequest = $requestBuilder->get();

        return $this->serviceProxy->sendRequest(
            service: $this->serviceCollection->get('source'),
            request: $httpRequest,
            successContentType: 'text/x-yaml'
        );
    }
}
