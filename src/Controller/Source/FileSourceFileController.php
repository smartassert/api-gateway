<?php

declare(strict_types=1);

namespace App\Controller\Source;

use App\Exception\ServiceException;
use App\Security\ApiToken;
use App\ServiceProxy\ServiceProxy;
use App\ServiceRequest\RequestBuilderFactory;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/file-source/{sourceId<[A-Z90-9]{26}>}/{filename<.*\.yaml>}', name: 'file_source_file_')]
readonly class FileSourceFileController
{
    public function __construct(
        private RequestBuilderFactory $requestBuilderFactory,
        private ServiceProxy $sourcesProxy,
    ) {
    }

    /**
     * @throws ServiceException
     */
    #[Route(name: 'create', methods: ['POST'])]
    public function create(ApiToken $token, Request $request): Response
    {
        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $request->getRequestUri());
        $httpRequest = $requestBuilder
            ->withAuthorization($token->token)
            ->withBody((string) $request->getContent(), (string) $request->headers->get('content-type'))
            ->get()
        ;

        try {
            return $this->sourcesProxy->sendRequest(request: $httpRequest, bareResponseStatusCodes: [200, 404]);
        } catch (ClientExceptionInterface $exception) {
            throw new ServiceException('sources', $exception);
        }
    }

    /**
     * @throws ServiceException
     */
    #[Route(name: 'read', methods: ['GET'])]
    public function read(ApiToken $token, Request $request): Response
    {
        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $request->getRequestUri());
        $httpRequest = $requestBuilder
            ->withAuthorization($token->token)
            ->get()
        ;

        try {
            return $this->sourcesProxy->sendRequest(request: $httpRequest, successContentType: 'text/x-yaml');
        } catch (ClientExceptionInterface $exception) {
            throw new ServiceException('sources', $exception);
        }
    }

    /**
     * @throws ServiceException
     */
    #[Route(name: 'update', methods: ['PUT'])]
    public function update(ApiToken $token, Request $request): Response
    {
        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $request->getRequestUri());
        $httpRequest = $requestBuilder
            ->withAuthorization($token->token)
            ->withBody((string) $request->getContent(), (string) $request->headers->get('content-type'))
            ->get()
        ;

        try {
            return $this->sourcesProxy->sendRequest(request: $httpRequest, bareResponseStatusCodes: [200, 404]);
        } catch (ClientExceptionInterface $exception) {
            throw new ServiceException('sources', $exception);
        }
    }

    /**
     * @throws ServiceException
     */
    #[Route(name: 'delete', methods: ['DELETE'])]
    public function delete(ApiToken $token, Request $request): Response
    {
        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $request->getRequestUri());
        $httpRequest = $requestBuilder
            ->withAuthorization($token->token)
            ->get()
        ;

        try {
            return $this->sourcesProxy->sendRequest(request: $httpRequest, bareResponseStatusCodes: [200, 404]);
        } catch (ClientExceptionInterface $exception) {
            throw new ServiceException('sources', $exception);
        }
    }
}
