<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Exception\ServiceException;
use App\ServiceProxy\Service;
use App\ServiceProxy\ServiceProxy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

readonly class RefreshTokenController
{
    public function __construct(
        private ServiceProxy $serviceProxy,
    ) {
    }

    /**
     * @throws ServiceException
     */
    #[Route(
        path: '/{serviceName<[a-z]+>}/refresh-token/revoke-all-for-user',
        name: 'user_revoke_all_refresh_token',
        methods: ['POST']
    )]
    public function revokeAllForUser(Service $service, Request $request): Response
    {
        return $this->serviceProxy->proxy($service, $request);
    }

    /**
     * @throws ServiceException
     */
    #[Route(
        path: '/{serviceName<[a-z]+>}/refresh-token/revoke ',
        name: 'user_revoke_refresh_token',
        methods: ['POST']
    )]
    public function revoke(Service $service, Request $request): Response
    {
        return $this->serviceProxy->proxy($service, $request);
    }
}
