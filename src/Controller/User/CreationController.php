<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Exception\ServiceException;
use App\Security\AuthenticationToken;
use App\ServiceProxy\ServiceProxy;
use App\ServiceRequest\RequestBuilderFactory;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

readonly class CreationController
{
    public function __construct(
        private RequestBuilderFactory $requestBuilderFactory,
        private ServiceProxy $usersProxy,
    ) {
    }

    /**
     * @throws ServiceException
     */
    #[Route('/user/create', name: 'user_create', methods: ['POST'])]
    public function create(AuthenticationToken $token, Request $request): Response
    {
        $requestBuilder = $this->requestBuilderFactory->create($request->getMethod(), $request->getRequestUri());
        $httpRequest = $requestBuilder
            ->withAuthorization($token->token)
            ->withBody(http_build_query($request->request->all()), (string) $request->headers->get('content-type'))
            ->get()
        ;

        try {
            return $this->usersProxy->sendRequest(request: $httpRequest, bareResponseStatusCodes: [401, 404]);
        } catch (ClientExceptionInterface $exception) {
            throw new ServiceException('users', $exception);
        }
    }
}
