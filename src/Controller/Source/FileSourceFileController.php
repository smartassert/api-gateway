<?php

declare(strict_types=1);

namespace App\Controller\Source;

use App\Exception\ServiceException;
use App\Response\EmptyResponse;
use App\Response\YamlResponse;
use App\Security\ApiToken;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\HttpResponseExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\SourcesClient\FileClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/file-source/{sourceId<[A-Z90-9]{26}>}/{filename<.*\.yaml>}', name: 'file_source_file_')]
readonly class FileSourceFileController
{
    public function __construct(
        private FileClientInterface $client,
    ) {
    }

    /**
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route(name: 'create', methods: ['POST'])]
    public function create(ApiToken $token, string $sourceId, string $filename, Request $request): EmptyResponse
    {
        try {
            $this->client->add($token->token, $sourceId, $filename, (string) $request->getContent());

            return new EmptyResponse();
        } catch (
            ClientExceptionInterface |
            HttpResponseExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException $e
        ) {
            throw new ServiceException('sources', $e);
        }
    }

    /**
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route(name: 'read', methods: ['GET'])]
    public function read(ApiToken $token, string $sourceId, string $filename): YamlResponse
    {
        try {
            return new YamlResponse($this->client->read($token->token, $sourceId, $filename));
        } catch (
            ClientExceptionInterface |
            HttpResponseExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException $e
        ) {
            throw new ServiceException('sources', $e);
        }
    }

    /**
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route(name: 'update', methods: ['PUT'])]
    public function update(ApiToken $token, string $sourceId, string $filename, Request $request): EmptyResponse
    {
        try {
            $this->client->update($token->token, $sourceId, $filename, (string) $request->getContent());

            return new EmptyResponse();
        } catch (
            ClientExceptionInterface |
            HttpResponseExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException $e
        ) {
            throw new ServiceException('sources', $e);
        }
    }

    /**
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route(name: 'delete', methods: ['DELETE'])]
    public function delete(ApiToken $token, string $sourceId, string $filename): EmptyResponse
    {
        try {
            $this->client->remove($token->token, $sourceId, $filename);

            return new EmptyResponse();
        } catch (
            ClientExceptionInterface |
            HttpResponseExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException $e
        ) {
            throw new ServiceException('sources', $e);
        }
    }
}
