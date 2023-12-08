<?php

declare(strict_types=1);

namespace App\Controller\Source;

use App\Exception\ServiceException;
use App\Security\ApiToken;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\CurlExceptionInterface;
use SmartAssert\ServiceClient\Exception\HttpResponseExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\ServiceClient\SerializableInterface;
use SmartAssert\SourcesClient\FileSourceClientInterface;
use SmartAssert\SourcesClient\GitSourceClientInterface;
use SmartAssert\SourcesClient\SourceClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

readonly class SourceController
{
    public function __construct(
        private SourceClientInterface $client,
        private FileSourceClientInterface $fileSourceClient,
        private GitSourceClientInterface $gitSourceClient,
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
     * @param non-empty-string $sourceId
     *
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route(path: '/source/{sourceId<[A-Z90-9]{26}>}', name: 'source_read', methods: ['GET'])]
    public function read(ApiToken $token, string $sourceId): Response
    {
        var_dump('foo 01');

        try {
            var_dump($this->fileSourceClient->get($token->token, $sourceId));
        } catch (\Exception $e) {
            var_dump('for file');
            var_dump($e::class);
        }

        try {
            var_dump($this->gitSourceClient->get($token->token, $sourceId));
        } catch (\Exception $e) {
            var_dump('for git');
            var_dump($e::class);
        }

//        try {
//            try {
//                $source = $this->fileSourceClient->get($token->token, $sourceId);
//            } catch (InvalidModelDataException) {
//                $source = $this->gitSourceClient->get($token->token, $sourceId);
//            }
//
//            return new JsonResponse($source->toArray());
//        } catch (
//            ClientExceptionInterface |
//            HttpResponseExceptionInterface |
//            InvalidModelDataException |
//            InvalidResponseDataException |
//            InvalidResponseTypeException $e
//        ) {
//            throw new ServiceException('sources', $e);
//        }
    }
}
