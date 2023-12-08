<?php

declare(strict_types=1);

namespace App\Controller\Source;

use App\Exception\ServiceException;
use App\Security\ApiToken;
use App\ServiceProxy\ServiceProxy;
use App\ServiceRequest\RequestBuilderFactory;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\HttpResponseExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\ServiceClient\SerializableInterface;
use SmartAssert\SourcesClient\SourceClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

readonly class SourceController
{
    public function __construct(
        private SourceClientInterface $client,
        private RequestBuilderFactory $requestBuilderFactory,
        private ServiceProxy $sourcesProxy,
    ) {
    }

    /**
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route(path: '/sources/list', name: 'sources_list', methods: ['GET'])]
    public function list(ApiToken $token): JsonResponse
    {
        try {
            $sources = $this->client->list($token->token);
        } catch (
            ClientExceptionInterface |
            HttpResponseExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException |
            InvalidResponseTypeException $e
        ) {
            throw new ServiceException('sources', $e);
        }

        $serializedSources = [];
        foreach ($sources as $source) {
            if ($source instanceof SerializableInterface) {
                $serializedSources[] = $source->toArray();
            }
        }

        return new JsonResponse($serializedSources);
    }

    /**
     * @throws ServiceException
     */
    #[Route(path: '/source/{sourceId<[A-Z90-9]{26}>}', name: 'source_read', methods: ['GET'])]
    public function read(ApiToken $token, Request $request): Response
    {
        $requestBuilder = $this->requestBuilderFactory->create('GET', $request->getRequestUri());
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
