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
        $tests = [];
        foreach ($request->request->all('tests') as $test) {
            if (is_string($test) && '' !== $test) {
                $tests[] = $test;
            }
        }

        try {
            $suite = $this->client->create(
                $token->token,
                $request->request->getString('sourceId'),
                $request->request->getString('label'),
                $tests
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
}
