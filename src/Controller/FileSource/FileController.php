<?php

declare(strict_types=1);

namespace App\Controller\FileSource;

use App\Exception\ServiceException;
use App\Response\EmptyBody;
use App\Response\Response;
use App\Response\YamlResponse;
use App\Security\AuthenticationToken;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\HttpResponseExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\SourcesClient\FileClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/file-source/{sourceId<[A-Z90-9]{26}>}/{filename<.*\.yaml>}', name: 'file_source_file_')]
readonly class FileController
{
    public function __construct(
        private FileClientInterface $client,
    ) {
    }

    /**
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route(name: 'add', methods: ['POST'])]
    public function add(
        AuthenticationToken $token,
        string $sourceId,
        string $filename,
        Request $request
    ): JsonResponse {
        try {
            $this->client->add($token->token, $sourceId, $filename, (string) $request->getContent());
        } catch (
            ClientExceptionInterface |
            HttpResponseExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException $e
        ) {
            throw new ServiceException('sources', $e);
        }

        return new Response(new EmptyBody());
    }

    /**
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route(name: 'read', methods: ['GET'])]
    public function read(
        AuthenticationToken $token,
        string $sourceId,
        string $filename
    ): YamlResponse {
        try {
            $content = $this->client->read($token->token, $sourceId, $filename);
        } catch (
            ClientExceptionInterface |
            HttpResponseExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException $e
        ) {
            throw new ServiceException('sources', $e);
        }

        return new YamlResponse($content);
    }
}
