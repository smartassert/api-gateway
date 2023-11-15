<?php

declare(strict_types=1);

namespace App\Controller\Source;

use App\Exception\ServiceException;
use App\Response\EmptyResponse;
use App\Security\ApiToken;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\HttpResponseExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\SourcesClient\Exception\ModifyReadOnlyEntityException;
use SmartAssert\SourcesClient\FileSourceClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/file-source', name: 'file_source_')]
readonly class FileSourceController
{
    public function __construct(
        private FileSourceClientInterface $client,
    ) {
    }

    /**
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route(name: 'create', methods: ['POST'])]
    public function create(ApiToken $token, Request $request): Response
    {
        try {
            $source = $this->client->create($token->token, $request->request->getString('label'));
        } catch (
            ClientExceptionInterface |
            HttpResponseExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException |
            InvalidResponseTypeException $e
        ) {
            throw new ServiceException('sources', $e);
        }

        return new JsonResponse([
            'file_source' => $source->toArray(),
        ]);
    }

    /**
     * @param non-empty-string $sourceId
     *
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route(path: '/{sourceId<[A-Z90-9]{26}>}', name: 'handle', methods: ['GET', 'PUT', 'DELETE'])]
    public function handle(ApiToken $token, string $sourceId, Request $request): Response
    {
        try {
            $source = match ($request->getMethod()) {
                'GET' => $this->client->get($token->token, $sourceId),
                'PUT' => $this->client->update($token->token, $sourceId, $request->request->getString('label')),
                'DELETE' => $this->client->delete($token->token, $sourceId),
                default => null,
            };
        } catch (
            ClientExceptionInterface |
            HttpResponseExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException |
            InvalidResponseTypeException |
            ModifyReadOnlyEntityException $e
        ) {
            throw new ServiceException('sources', $e);
        }

        if (null === $source) {
            return new EmptyResponse(405);
        }

        return new JsonResponse([
            'file_source' => $source->toArray(),
        ]);
    }

    /**
     * @param non-empty-string $sourceId
     *
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route(path: '/{sourceId<[A-Z90-9]{26}>}/list', name: 'list', methods: ['GET'])]
    public function list(ApiToken $token, string $sourceId): JsonResponse
    {
        try {
            $filenames = $this->client->list($token->token, $sourceId);
        } catch (
            ClientExceptionInterface |
            HttpResponseExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException |
            InvalidResponseTypeException $e
        ) {
            throw new ServiceException('sources', $e);
        }

        return new JsonResponse([
            'files' => $filenames,
        ]);
    }
}
