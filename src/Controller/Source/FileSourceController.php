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

#[Route(path: '/file-source', name: 'file_source_')]
readonly class FileSourceController
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
        $requestBuilder = $this->requestBuilderFactory->create('POST', '/file-source');
        $httpRequest = $requestBuilder
            ->withAuthorization($token->token)
            ->withPayload(['label' => $request->request->get('label')])
            ->get()
        ;

        try {
            return $this->sourcesProxy->sendRequest(request: $httpRequest, bareResponseStatusCodes: []);
        } catch (ClientExceptionInterface $exception) {
            throw new ServiceException('sources', $exception);
        }
    }

    /**
     * @param non-empty-string $sourceId
     *
     * @throws ServiceException
     */
    #[Route(path: '/{sourceId<[A-Z90-9]{26}>}', name: 'update', methods: ['PUT'])]
    public function update(ApiToken $token, string $sourceId, Request $request): Response
    {
        $requestBuilder = $this->requestBuilderFactory->create('PUT', '/file-source/' . $sourceId);
        $httpRequest = $requestBuilder
            ->withAuthorization($token->token)
            ->withPayload(['label' => $request->request->get('label')])
            ->get()
        ;

        try {
            return $this->sourcesProxy->sendRequest($httpRequest);
        } catch (ClientExceptionInterface $exception) {
            throw new ServiceException('sources', $exception);
        }
    }

    /**
     * @param non-empty-string $sourceId
     *
     * @throws ServiceException
     */
    #[Route(path: '/{sourceId<[A-Z90-9]{26}>}/list', name: 'list', methods: ['GET'])]
    public function list(ApiToken $token, string $sourceId): Response
    {
        $requestBuilder = $this->requestBuilderFactory->create('GET', '/file-source/' . $sourceId . '/list/');
        $httpRequest = $requestBuilder
            ->withAuthorization($token->token)
            ->get()
        ;

        try {
            return $this->sourcesProxy->sendRequest($httpRequest);
        } catch (ClientExceptionInterface $exception) {
            throw new ServiceException('sources', $exception);
        }
    }
}
