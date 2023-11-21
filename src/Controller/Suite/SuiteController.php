<?php

declare(strict_types=1);

namespace App\Controller\Suite;

use App\Exception\ServiceException;
use App\Security\ApiToken;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\HttpResponseExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\SourcesClient\Exception\ModifyReadOnlyEntityException;
use SmartAssert\SourcesClient\SuiteClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/suite', name: 'suite_')]
readonly class SuiteController
{
    public function __construct(
        private SuiteClientInterface $client,
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
            $suite = $this->client->create(
                $token->token,
                $request->request->getString('sourceId'),
                $request->request->getString('label'),
                $this->getTests($request)
            );

            return new JsonResponse([
                'suite' => $suite->toArray(),
            ]);
        } catch (
            ClientExceptionInterface |
            HttpResponseExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException |
            InvalidResponseTypeException $e
        ) {
            throw new ServiceException('sources', $e);
        }
    }

    /**
     * @param non-empty-string $suiteId
     *
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route(path: '/{suiteId<[A-Z90-9]{26}>}', name: 'read', methods: ['GET'])]
    public function get(ApiToken $token, string $suiteId): Response
    {
        try {
            $suite = $this->client->get($token->token, $suiteId);

            return new JsonResponse([
                'suite' => $suite->toArray(),
            ]);
        } catch (
            ClientExceptionInterface |
            HttpResponseExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException |
            InvalidResponseTypeException $e
        ) {
            throw new ServiceException('sources', $e);
        }
    }

    /**
     * @param non-empty-string $suiteId
     *
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route(path: '/{suiteId<[A-Z90-9]{26}>}', name: 'update', methods: ['PUT'])]
    public function update(ApiToken $token, string $suiteId, Request $request): Response
    {
        try {
            $suite = $this->client->update(
                $token->token,
                $suiteId,
                $request->request->getString('sourceId'),
                $request->request->getString('label'),
                $this->getTests($request)
            );

            return new JsonResponse([
                'suite' => $suite->toArray(),
            ]);
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
    }

    /**
     * @param non-empty-string $suiteId
     *
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    #[Route(path: '/{suiteId<[A-Z90-9]{26}>}', name: 'delete', methods: ['DELETE'])]
    public function delete(ApiToken $token, string $suiteId): Response
    {
        try {
            $suite = $this->client->delete($token->token, $suiteId);

            return new JsonResponse([
                'suite' => $suite->toArray(),
            ]);
        } catch (
            ClientExceptionInterface |
            HttpResponseExceptionInterface |
            InvalidModelDataException |
            InvalidResponseDataException |
            InvalidResponseTypeException $e
        ) {
            throw new ServiceException('sources', $e);
        }
    }

    /**
     * @return non-empty-string[]
     */
    private function getTests(Request $request): array
    {
        $tests = [];
        foreach ($request->request->all('tests') as $test) {
            if (is_string($test) && '' !== $test) {
                $tests[] = $test;
            }
        }

        return $tests;
    }
}
