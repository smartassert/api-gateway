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
use Symfony\Component\HttpFoundation\Response;
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
    #[Route(name: 'handle', methods: ['POST', 'GET', 'DELETE'])]
    public function handle(ApiToken $token, string $sourceId, string $filename, Request $request): Response
    {
        try {
            if ('POST' === $request->getMethod()) {
                $this->client->add($token->token, $sourceId, $filename, (string) $request->getContent());

                return new EmptyResponse();
            }

            if ('DELETE' === $request->getMethod()) {
                $this->client->remove($token->token, $sourceId, $filename);

                return new EmptyResponse();
            }

            if ('GET' === $request->getMethod()) {
                return new YamlResponse($this->client->read($token->token, $sourceId, $filename));
            }
        } catch (
            ClientExceptionInterface |
            HttpResponseExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException $e
        ) {
            throw new ServiceException('sources', $e);
        }

        return new EmptyResponse(405);
    }
}
