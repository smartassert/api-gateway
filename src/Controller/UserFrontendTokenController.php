<?php

declare(strict_types=1);

namespace App\Controller;

use App\Response\ErrorResponse;
use App\Response\ErrorResponseBody;
use App\Response\LabelledBody;
use App\Response\Response;
use App\Response\User\RefreshableToken;
use App\Response\User\User;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\SecurityTokenExtractor\TokenExtractor;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\UsersClient\Client;
use SmartAssert\UsersClient\Model\Token;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user/frontend/token', name: 'user_frontend_token_')]
readonly class UserFrontendTokenController
{
    public function __construct(
        private Client $client
    ) {
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $email = $request->request->get('email');
        if (!is_string($email)) {
            $email = '';
        }

        $password = $request->request->get('password');
        if (!is_string($password)) {
            $password = '';
        }

        try {
            $token = $this->client->createFrontendToken($email, $password);
        } catch (ClientExceptionInterface $e) {
            $code = $e->getCode();
            $message = $e->getMessage();

            return new ErrorResponse(
                new ErrorResponseBody(
                    'service-communication-failure',
                    [
                        'service' => 'users',
                        'error' => [
                            'code' => $code,
                            'message' => $message,
                        ],
                    ]
                )
            );
        } catch (InvalidModelDataException $e) {
            return new ErrorResponse(
                new ErrorResponseBody(
                    'invalid-model-data',
                    [
                        'service' => 'users',
                        'data' => $e->getResponse()->getBody(),
                    ]
                )
            );
        } catch (InvalidResponseDataException $e) {
            return new ErrorResponse(
                new ErrorResponseBody(
                    'invalid-response-data',
                    [
                        'service' => 'users',
                        'data' => $e->getResponse()->getBody(),
                        'data-type' => [
                            'expected' => $e->expected,
                            'actual' => $e->actual,
                        ],
                    ]
                )
            );
        } catch (InvalidResponseTypeException $e) {
            return new ErrorResponse(
                new ErrorResponseBody(
                    'invalid-response-type',
                    [
                        'service' => 'users',
                        'content-type' => [
                            'expected' => $e->expected,
                            'actual' => $e->actual,
                        ],
                    ]
                )
            );
        } catch (NonSuccessResponseException $e) {
            if (401 === $e->getStatusCode()) {
                return new ErrorResponse(
                    new ErrorResponseBody('unauthorized'),
                    $e->getStatusCode()
                );
            }

            if (404 === $e->getStatusCode()) {
                return new ErrorResponse(
                    new ErrorResponseBody('not-found'),
                    $e->getStatusCode()
                );
            }

            return new ErrorResponse(
                new ErrorResponseBody(
                    'non-successful-service-response',
                    [
                        'service' => 'users',
                        'status' => $e->getStatusCode(),
                        'message' => $e->getMessage(),
                    ]
                )
            );
        }

        return new Response(
            new LabelledBody(
                'refreshable_token',
                new RefreshableToken($token->token, $token->refreshToken)
            )
        );
    }

    #[Route('/verify', name: 'verify', methods: ['GET'])]
    public function verify(
        Request $request,
        TokenExtractor $tokenExtractor,
        HttpMessageFactoryInterface $httpMessageFactory
    ): JsonResponse {
        $token = $tokenExtractor->extract($httpMessageFactory->createRequest($request));
        if (null === $token || '' === $token) {
            return new ErrorResponse(new ErrorResponseBody('unauthorized'), 401);
        }

        try {
            $user = $this->client->verifyFrontendToken(new Token($token));
            if (null === $user) {
                return new ErrorResponse(new ErrorResponseBody('unauthorized'), 401);
            }
        } catch (ClientExceptionInterface $e) {
            $code = $e->getCode();
            $message = $e->getMessage();

            return new ErrorResponse(
                new ErrorResponseBody(
                    'service-communication-failure',
                    [
                        'service' => 'users',
                        'error' => [
                            'code' => $code,
                            'message' => $message,
                        ],
                    ]
                )
            );
        } catch (InvalidModelDataException $e) {
            return new ErrorResponse(
                new ErrorResponseBody(
                    'invalid-model-data',
                    [
                        'service' => 'users',
                        'data' => $e->getResponse()->getBody(),
                    ]
                )
            );
        } catch (InvalidResponseDataException $e) {
            return new ErrorResponse(
                new ErrorResponseBody(
                    'invalid-response-data',
                    [
                        'service' => 'users',
                        'data' => $e->getResponse()->getBody(),
                        'data-type' => [
                            'expected' => $e->expected,
                            'actual' => $e->actual,
                        ],
                    ]
                )
            );
        } catch (InvalidResponseTypeException $e) {
            return new ErrorResponse(
                new ErrorResponseBody(
                    'invalid-response-type',
                    [
                        'service' => 'users',
                        'content-type' => [
                            'expected' => $e->expected,
                            'actual' => $e->actual,
                        ],
                    ]
                )
            );
        } catch (NonSuccessResponseException $e) {
            if (404 === $e->getStatusCode()) {
                return new ErrorResponse(
                    new ErrorResponseBody('not-found'),
                    $e->getStatusCode()
                );
            }

            return new ErrorResponse(
                new ErrorResponseBody(
                    'non-successful-service-response',
                    [
                        'service' => 'users',
                        'status' => $e->getStatusCode(),
                        'message' => $e->getMessage(),
                    ]
                )
            );
        }

        return new Response(
            new LabelledBody(
                'user',
                new User($user->id, $user->userIdentifier)
            )
        );
    }
}
